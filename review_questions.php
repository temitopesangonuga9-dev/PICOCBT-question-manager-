<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$statusFilter  = $_GET['status'] ?? 'pending';
$subjectFilter = $_GET['subject'] ?? '';

$where  = [];
$params = [];

if (in_array($statusFilter, ['pending', 'approved', 'rejected', 'draft'], true)) {
    $where[] = "qb.status = ?";
    $params[] = $statusFilter;
}
if ($subjectFilter !== '') {
    $where[] = "qb.subject = ?";
    $params[] = $subjectFilter;
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare(
    "SELECT qb.*, u.full_name AS teacher_name
     FROM question_bank qb
     LEFT JOIN users u ON u.id = qb.created_by
     $whereSql
     ORDER BY qb.created_at DESC LIMIT 200"
);
$stmt->execute($params);
$questions = $stmt->fetchAll();

// Preload options for MCQ questions in this page
$optionsByQuestion = [];
$mcqIds = array_column(array_filter($questions, fn($q) => $q['question_type'] === 'mcq'), 'id');
if (!empty($mcqIds)) {
    $in = implode(',', array_fill(0, count($mcqIds), '?'));
    $optStmt = $pdo->prepare("SELECT * FROM question_bank_options WHERE question_id IN ($in) ORDER BY id");
    $optStmt->execute($mcqIds);
    foreach ($optStmt->fetchAll() as $opt) {
        $optionsByQuestion[$opt['question_id']][] = $opt;
    }
}

$pageTitle = 'Review Questions';
$assetPath = '../assets/';
require_once __DIR__ . '/../includes/header.php';
$qs = http_build_query(['status' => $statusFilter, 'subject' => $subjectFilter]);
?>

<div class="card">
    <div class="card-header">Review Questions</div>
    <div class="card-body">
        <form method="get" class="toolbar">
            <select name="status" onchange="this.form.submit()">
                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $statusFilter === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>All</option>
            </select>
            <select name="subject" onchange="this.form.submit()">
                <option value="">All Subjects</option>
                <?php foreach (subjectList() as $s): ?>
                    <option value="<?= e($s) ?>" <?= $subjectFilter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (empty($questions)): ?>
            <p>No questions found for this filter.</p>
        <?php endif; ?>

        <?php foreach ($questions as $q): ?>
        <div class="ai-question-preview">
            <div class="toolbar" style="justify-content: space-between;">
                <div>
                    <span class="badge badge-<?= e($q['status']) ?>"><?= e(ucfirst($q['status'])) ?></span>
                    &nbsp; <strong><?= e($q['subject']) ?></strong> · <?= e($q['class_level'] ?? '-') ?> · <?= e(ucfirst($q['question_type'])) ?>
                    · by <?= e($q['teacher_name'] ?? 'Unknown') ?>
                </div>
                <div style="font-size:0.85rem;color:#888;"><?= e(date('d M Y, g:i a', strtotime($q['created_at']))) ?></div>
            </div>

            <p><strong>Q:</strong> <?= nl2br(e($q['question_text'])) ?></p>

            <?php if ($q['question_type'] === 'mcq' && !empty($optionsByQuestion[$q['id']])): ?>
                <ul>
                    <?php foreach ($optionsByQuestion[$q['id']] as $opt): ?>
                        <li style="<?= $opt['is_correct'] ? 'font-weight:700;color:#1f7a30;' : '' ?>">
                            <?= e($opt['option_text']) ?> <?= $opt['is_correct'] ? '✓' : '' ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif ($q['question_type'] === 'theory'): ?>
                <p><strong>Model Answer:</strong> <?= nl2br(e($q['theory_answer'])) ?></p>
            <?php endif; ?>

            <div class="toolbar">
                <form method="post" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                    <input type="hidden" name="redirect_qs" value="<?= e($qs) ?>">
                    <input type="hidden" name="new_status" value="approved">
                    <button type="submit" class="btn btn-small">Approve</button>
                </form>
                <form method="post" action="update_status.php" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                    <input type="hidden" name="redirect_qs" value="<?= e($qs) ?>">
                    <input type="hidden" name="new_status" value="rejected">
                    <button type="submit" class="btn btn-small btn-danger">Reject</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
