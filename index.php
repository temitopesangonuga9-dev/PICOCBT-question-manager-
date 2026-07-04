<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isTeacher()) redirect('teacher/dashboard.php');
if (isAdmin()) redirect('admin/dashboard.php');

$pageTitle = 'PICOCBT Question Manager';
$assetPath = 'assets/';
require_once __DIR__ . '/includes/header.php';
?>

<div class="grid-2">
    <div class="card">
        <div class="card-header">👩‍🏫 Teacher Portal</div>
        <div class="card-body">
            <p>Create questions manually, generate with AI, or convert lesson notes into MCQs.</p>
            <a href="teacher/login.php" class="btn">Teacher Login</a>
            <a href="teacher/register.php" class="btn btn-secondary" style="margin-top:8px;">Register as Teacher</a>
        </div>
    </div>
    <div class="card">
        <div class="card-header">🛠️ Admin Portal</div>
        <div class="card-body">
            <p>Review submitted questions, approve teacher accounts, and export CSV for PICOCBT.</p>
            <a href="admin/login.php" class="btn">Admin Login</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
