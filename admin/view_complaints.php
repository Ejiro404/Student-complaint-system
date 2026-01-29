<?php
require_once '../config/session.php';
require_once '../config/db.php';

check_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

/* ----------------------------
   Inputs: filter + search + pagination
----------------------------- */
$filter_status = isset($_SESSION['complaints_filter']) ? $_SESSION['complaints_filter'] : '';
$allowed_status = ['Pending', 'In Progress', 'Resolved'];
if (!in_array($filter_status, $allowed_status, true)) {
    $filter_status = '';
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$q_like = '%' . $q . '%';

$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $per_page;

/* ----------------------------
   Build WHERE conditions + params (for both COUNT and LIST)
----------------------------- */
$where = " WHERE 1=1 ";
$params = [];
$types  = "";

if ($filter_status !== '') {
    $where .= " AND c.status = ? ";
    $types .= "s";
    $params[] = $filter_status;
}

if ($q !== '') {
    $where .= " AND (
                u.full_name LIKE ?
                OR c.subject LIKE ?
                OR c.complaint_text LIKE ?
              ) ";
    $types .= "sss";
    $params[] = $q_like;
    $params[] = $q_like;
    $params[] = $q_like;
}

/* ----------------------------
   COUNT total rows (pagination)
----------------------------- */
$countSql = "SELECT COUNT(*) AS total
             FROM complaints c
             LEFT JOIN users u ON c.student_id = u.id
             $where";

$countStmt = $conn->prepare($countSql);
if (!$countStmt) {
    die("Prepare failed (COUNT): " . $conn->error);
}

if (!empty($params)) {
    $bind = [];
    $bind[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind[] = &$params[$i];
    }
    call_user_func_array([$countStmt, 'bind_param'], $bind);
}

$countStmt->execute();
$countRes = $countStmt->get_result();
$total_rows = (int)($countRes->fetch_assoc()['total'] ?? 0);
$countStmt->close();

$total_pages = (int)ceil($total_rows / $per_page);
if ($total_pages < 1) { $total_pages = 1; }
if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

/* ----------------------------
   Fetch paginated rows
----------------------------- */
$listSql = "SELECT
                c.id AS complaint_id,
                c.subject,
                c.complaint_text,
                c.status,
                c.created_at,
                u.full_name
            FROM complaints c
            LEFT JOIN users u ON c.student_id = u.id
            $where
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?";

$listStmt = $conn->prepare($listSql);
if (!$listStmt) {
    die("Prepare failed (LIST): " . $conn->error);
}

$listParams = $params;
$listTypes  = $types . "ii";
$listParams[] = $per_page;
$listParams[] = $offset;

$bind2 = [];
$bind2[] = $listTypes;
for ($i = 0; $i < count($listParams); $i++) {
    $bind2[] = &$listParams[$i];
}
call_user_func_array([$listStmt, 'bind_param'], $bind2);

$listStmt->execute();
$result = $listStmt->get_result();

$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

$page_title = "View Complaints | LASUED Complaint System";
include '../includes/portal_header.php';

// helper for pagination links (preserve q + page)
function build_page_url($page, $q) {
    $qs = [];
    if ($q !== '') $qs[] = "q=" . urlencode($q);
    $qs[] = "page=" . (int)$page;
    return "view_complaints.php?" . implode("&", $qs);
}
?>

<div class="tile">
  <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
    <h2 style="margin:0;">All Student Complaints</h2>

    <!-- Search box (GET) -->
    <form method="GET" action="" class="search-bar">
      <input
        type="text"
        name="q"
        value="<?php echo htmlspecialchars($q); ?>"
        placeholder="Search by student, subject, or message..."
      >
      <?php if ($q !== ''): ?>
        <a class="search-clear" href="view_complaints.php">Clear</a>
      <?php endif; ?>
      <button class="btn" type="submit" style="padding:10px 14px;">Search</button>
    </form>
  </div>

  <!-- Filter pills (preserve q) -->
  <div class="filter-bar" style="margin-top:12px;">
    <a class="filter-pill <?php echo empty($filter_status) ? 'active' : ''; ?>"
       href="set_filter.php?status=ALL<?php echo ($q !== '' ? '&q='.urlencode($q) : ''); ?>">All</a>

    <a class="filter-pill <?php echo ($filter_status === 'Pending') ? 'active' : ''; ?>"
       href="set_filter.php?status=Pending<?php echo ($q !== '' ? '&q='.urlencode($q) : ''); ?>">Pending</a>

    <a class="filter-pill <?php echo ($filter_status === 'In Progress') ? 'active' : ''; ?>"
       href="set_filter.php?status=In%20Progress<?php echo ($q !== '' ? '&q='.urlencode($q) : ''); ?>">In Progress</a>

    <a class="filter-pill <?php echo ($filter_status === 'Resolved') ? 'active' : ''; ?>"
       href="set_filter.php?status=Resolved<?php echo ($q !== '' ? '&q='.urlencode($q) : ''); ?>">Resolved</a>
  </div>

  <?php if ($filter_status !== '' || $q !== ''): ?>
    <div style="margin-bottom:12px;">
      <?php if ($filter_status !== ''): ?>
        <span class="badge">Status: <?php echo htmlspecialchars($filter_status); ?></span>
      <?php endif; ?>
      <?php if ($q !== ''): ?>
        <span class="badge" style="margin-left:8px;">Search: <?php echo htmlspecialchars($q); ?></span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- =======================
       Desktop/Tablet Table
       ======================= -->
  <div class="table-wrap desktop-only">
    <table>
      <tr>
        <th>Student</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
      </tr>

      <?php if (!empty($rows)): ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></td>
            <td><?php echo htmlspecialchars($row['subject']); ?></td>

            <td>
              <div style="display:flex; gap:8px; align-items:flex-start; flex-wrap:wrap;">
                <div style="flex:1; min-width: 220px;">
                  <?php echo htmlspecialchars($row['complaint_text']); ?>
                </div>
                <button
                  type="button"
                  class="btn copy-btn"
                  data-copy="<?php echo htmlspecialchars($row['complaint_text'], ENT_QUOTES); ?>"
                  style="padding:8px 10px; font-size:13px;"
                >Copy</button>
              </div>
            </td>

            <td class="status-<?php echo str_replace(' ', '-', $row['status']); ?>">
              <?php echo htmlspecialchars($row['status']); ?>
            </td>

            <td><?php echo htmlspecialchars($row['created_at']); ?></td>

            <td>
              <a class="btn action-btn"
                 href="update_complaint.php?id=<?php echo (int)$row['complaint_id']; ?>">
                Update
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" style="text-align:center; padding:16px;">
            No complaints found.
          </td>
        </tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- =======================
       Mobile Card Layout
       ======================= -->
  <div class="mobile-only">
    <?php if (!empty($rows)): ?>
      <?php foreach ($rows as $row): ?>
        <div class="complaint-card">
          <div class="cc-head">
            <div class="cc-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
            <div class="cc-status status-<?php echo str_replace(' ', '-', $row['status']); ?>">
              <?php echo htmlspecialchars($row['status']); ?>
            </div>
          </div>

          <div class="cc-body">
            <div class="cc-label">Student</div>
            <div class="cc-text"><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></div>

            <div class="cc-label" style="margin-top:10px;">Message</div>
            <div class="cc-text"><?php echo htmlspecialchars($row['complaint_text']); ?></div>
          </div>

          <div class="cc-foot" style="align-items: stretch;">
            <div class="cc-meta"><strong>Date:</strong> <?php echo htmlspecialchars($row['created_at']); ?></div>
          </div>

          <div class="cc-actions">
            <button
              type="button"
              class="btn copy-btn btn-mobile-full"
              data-copy="<?php echo htmlspecialchars($row['complaint_text'], ENT_QUOTES); ?>"
            >Copy Message</button>

            <a
              class="btn btn-mobile-full"
              href="update_complaint.php?id=<?php echo (int)$row['complaint_id']; ?>"
            >Update Complaint</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="complaint-empty">No complaints found.</div>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php $prev = $page - 1; $next = $page + 1; ?>

      <a class="page-link <?php echo ($page <= 1) ? 'disabled' : ''; ?>"
         href="<?php echo ($page <= 1) ? '#' : build_page_url($prev, $q); ?>">Prev</a>

      <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for ($p = $start; $p <= $end; $p++):
      ?>
        <a class="page-link <?php echo ($p === $page) ? 'active' : ''; ?>"
           href="<?php echo build_page_url($p, $q); ?>"><?php echo $p; ?></a>
      <?php endfor; ?>

      <a class="page-link <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"
         href="<?php echo ($page >= $total_pages) ? '#' : build_page_url($next, $q); ?>">Next</a>
    </div>
  <?php endif; ?>

  <p class="footer-note" style="margin-top:12px;">
    Use the search box to quickly locate complaints. Filters still work normally.
  </p>
</div>

<script>
(function () {
  function fallbackCopy(text) {
    const ta = document.createElement("textarea");
    ta.value = text;
    ta.setAttribute("readonly", "");
    ta.style.position = "absolute";
    ta.style.left = "-9999px";
    document.body.appendChild(ta);
    ta.select();
    document.execCommand("copy");
    document.body.removeChild(ta);
  }

  document.addEventListener("click", async function (e) {
    const btn = e.target.closest(".copy-btn");
    if (!btn) return;

    const text = btn.getAttribute("data-copy") || "";
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(text);
      } else {
        fallbackCopy(text);
      }
      const old = btn.textContent;
      btn.textContent = "Copied!";
      setTimeout(() => (btn.textContent = old), 1200);
    } catch (err) {
      fallbackCopy(text);
    }
  });
})();
</script>

<?php
$listStmt->close();
include '../includes/portal_footer.php';
?>
