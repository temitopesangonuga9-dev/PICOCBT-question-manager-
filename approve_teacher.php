<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Invalid request.');
    redirect('manage_teachers.php');
}

$teacherId = (int) ($_POST['teacher_id'] ?? 0);
$newStatus = $_POST['new_status'] ?? '';

if (!in_array($newStatus, ['active', 'suspended', 'pending'], true) || $teacherId <= 0) {
    setFlash('error', 'Invalid update.');
    redirect('manage_teachers.php');
}

$stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'teacher'");
$stmt->execute([$newStatus, $teacherId]);

setFlash('success', 'Teacher account updated.');
redirect('manage_teachers.php');
