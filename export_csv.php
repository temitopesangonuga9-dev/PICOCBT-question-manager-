<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Handle the actual CSV download
if (isset($_GET['download'])) {
    $subject = $_GET['subject'] ?? '';
    $classLevel = $_GET['class_level'] ?? '';
    $questionType = $_GET['question_type'] ?? '';

    $where = ["qb.status = 'approved'"];
    $params = [];
    if ($subject !== '') { $where[] = 'qb.subject = ?'; $params[] = $subject; }
    if ($classLevel !== '') { $where[] = 'qb.class_level = ?'; $params[] = $classLevel; }
    if ($questionType !== '') { $where[] = 'qb.question_type = ?'; $params[] = $questionType; }
    $whereSql = implode(' AND ', $where);

    $stmt = $pdo->prepare("SELECT * FROM question_bank qb WHERE $whereSql ORDER BY qb.subject, qb.id");
    $stmt->execute($params);
    $questions = $stmt->fetchAll();

    if (!empty($questions)) {
        $ids = array_column($questions, 'id');
        $in = implode(',', array_fill(0, count($ids), '?'));
        $optStmt = $pdo->prepare("SELECT * FROM question_bank_options WHERE question_id IN ($in) ORDER BY id");
        $optStmt->execute($ids);
        $optionsByQ = [];
        foreach ($optStmt->fetchAll() as $opt) {
            $optionsByQ[$opt['question_id']][] = $opt;
        }
        foreach ($questions as &$q) {
            $q['options'] = $optionsByQ[$q['id']] ?? [];
        }
        unset($q);
    }

    // Log the export for audit purposes
    $logStmt = $pdo->prepare("INSERT INTO csv_exports (admin_id, filters, question_count) VALUES (?, ?, ?)");
    $logStmt->execute([$_SESSION['user_id'], json_encode(compact('subject', 'classLevel', 'questionType')), count($questions)]);

    $filename = 'picocbt_questions_' . date('Y-m-d_His') . '.csv';
    streamQuestionsAsCsv($questions, $filename);
}

// Otherwise show the filter form
$approvedCount = $pdo->query("SELECT COUNT(*) FROM question_bank WHERE status = 'approved'")->fetchColumn();

$recentExports = $pdo->query(
    "SELECT ce.*, u.full_name FROM csv_exports ce LEFT JOIN users u ON u.id = ce.admin_id ORDER BY ce.created_at DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Export CSV';
$assetPath = '../assets/';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">⬇️ Export Approved Questions to CSV</div>
    <div class="card-body">
        <p><?= (int) $approvedCount ?> question(s) are currently approved and ready to export.</p>
        <p style="font-size:0.9rem;color:#666;">
            The CSV columns are: <code>subject, class_level, question_type, question_text, option_a, option_b, option_c, option_d, correct_option, theory_answer, difficulty</code>
            &mdash; matching the format PICOCBT's question bank importer expects. If your importer expects different column names, just say the word and I'll adjust the export format.
        </p>

        <form method="get" action="export_csv.php">
            <input type="hidden" name="download" value="1">
            <div class="grid-2">
                <div>
                    <label>Subject</label>
                    <select name="subject">
                        <option value="">All Subjects</option>
                        <?php foreach (subjectList() as $s): ?>
                            <option value="<?= e($s) ?>"><?= e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Class Level</label>
                    <select name="class_level">
                        <option value="">All Classes</option>
                        <option value="JSS1">JSS1</option>
                        <option value="JSS2">JSS2</option>
                        <option value="JSS3">JSS3</option>
                        <option value="SS1">SS1</option>
                        <option value="SS2">SS2</option>
                        <option value="SS3">SS3</option>
                    </select>
                </div>
            </div>
            <label>Question Type</label>
            <select name="question_type">
                <option value="">Both MCQ &amp; Theory</option>
                <option value="mcq">MCQ Only</option>
                <option value="theory">Theory Only</option>
            </select>

            <button type="submit" class="btn btn-block">Download CSV</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent Exports</div>
    <div class="card-body">
        <?php if (empty($recentExports)): ?>
            <p>No exports yet.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Date</th><th>By</th><th>Questions</th></tr></thead>
            <tbody>
            <?php foreach ($recentExports as $ex): ?>
                <tr>
                    <td><?= e(date('d M Y, g:i a', strtotime($ex['created_at']))) ?></td>
                    <td><?= e($ex['full_name'] ?? 'Unknown') ?></td>
                    <td><?= (int) $ex['question_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
