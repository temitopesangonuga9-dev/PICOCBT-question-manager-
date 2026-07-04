<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$teachers = $pdo->query(
    "SELECT u.*, (SELECT COUNT(*) FROM question_bank WHERE created_by = u.id) AS question_count
     FROM users u WHERE role = 'teacher' ORDER BY status = 'pending' DESC, created_at DESC"
)->fetchAll();

$pageTitle = 'Manage Teachers';
$assetPath = '../assets/';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">Manage Teacher Accounts</div>
    <div class="card-body">
        <?php if (empty($teachers)): ?>
            <p>No teacher accounts yet.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Subject</th><th>Questions</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($teachers as $t): ?>
                <tr>
                    <td><?= e($t['full_name']) ?></td>
                    <td><?= e($t['email']) ?></td>
                    <td><?= e($t['subject'] ?? '-') ?></td>
                    <td><?= (int) $t['question_count'] ?></td>
                    <td><span class="badge badge-<?= $t['status'] === 'active' ? 'approved' : ($t['status'] === 'pending' ? 'pending' : 'rejected') ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                    <td>
                        <?php if ($t['status'] !== 'active'): ?>
                        <form method="post" action="approve_teacher.php" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="new_status" value="active">
                            <button type="submit" class="btn btn-small">Approve</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($t['status'] !== 'suspended'): ?>
                        <form method="post" action="approve_teacher.php" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="new_status" value="suspended">
                            <button type="submit" class="btn btn-small btn-danger">Suspend</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
