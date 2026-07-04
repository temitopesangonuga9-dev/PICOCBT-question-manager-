<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$statusCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'draft' => 0];
foreach ($pdo->query("SELECT status, COUNT(*) cnt FROM question_bank GROUP BY status") as $row) {
    $statusCounts[$row['status']] = (int) $row['cnt'];
}
$total = array_sum($statusCounts);

$pendingTeachers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='pending'")->fetchColumn();
$activeTeachers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'")->fetchColumn();

$pageTitle = 'Admin Dashboard';
$assetPath = '../assets/';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="stat-grid">
    <div class="stat-box"><div class="num"><?= $total ?></div><div class="lbl">Total Questions</div></div>
    <div class="stat-box"><div class="num"><?= $statusCounts['pending'] ?></div><div class="lbl">Pending Review</div></div>
    <div class="stat-box"><div class="num"><?= $statusCounts['approved'] ?></div><div class="lbl">Approved</div></div>
    <div class="stat-box"><div class="num"><?= $activeTeachers ?></div><div class="lbl">Active Teachers</div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">📝 Review Questions</div>
        <div class="card-body">
            <p><?= $statusCounts['pending'] ?> question(s) awaiting your review.</p>
            <a href="review_questions.php" class="btn">Review Questions</a>
        </div>
    </div>
    <div class="card">
        <div class="card-header">👩‍🏫 Manage Teachers</div>
        <div class="card-body">
            <p><?= $pendingTeachers ?> teacher account(s) awaiting approval.</p>
            <a href="manage_teachers.php" class="btn">Manage Teachers</a>
        </div>
    </div>
    <div class="card">
        <div class="card-header">⬇️ Export to CSV</div>
        <div class="card-body">
            <p>Download approved questions as CSV to import into PICOCBT.</p>
            <a href="export_csv.php" class="btn">Go to Export</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
