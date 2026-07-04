<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid request.');
    redirect('review_questions.php');
}

$questionId = (int) ($_POST['question_id'] ?? 0);
$newStatus  = $_POST['new_status'] ?? '';

if (!in_array($newStatus, ['approved', 'rejected', 'pending'], true) || $questionId <= 0) {
    setFlash('error', 'Invalid status update.');
    redirect('review_questions.php');
}

$stmt = $pdo->prepare("UPDATE question_bank SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
$stmt->execute([$newStatus, $_SESSION['user_id'], $questionId]);

setFlash('success', 'Question marked as ' . $newStatus . '.');
redirect('review_questions.php' . (!empty($_POST['redirect_qs']) ? '?' . $_POST['redirect_qs'] : ''));
