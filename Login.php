<?php
$login_result = null;
$login_message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/INCLUDE/db.php';

    $icNum = trim($_POST['icNum'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = strtolower(trim($_POST['role'] ?? ''));

    if ($icNum === '' || $password === '' || $role === '') {
        $login_result = false;
        $login_message = 'All fields are required.';
    } else {
        $result = loginUser($icNum, $password, $role);
        $login_result = $result['success'];
        $login_message = $result['message'];

        if ($login_result && !empty($result['redirect'])) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['user']['role'] = strtolower((string) $_SESSION['user']['role']);
            header('Location: ' . $result['redirect']);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="CSS/style.css">
</head>

<body>

    <div class="login-container">
        <div class="login-card">
            <img src="IMG/logo.png" alt="FK Club Logo" class="login-logo">

            <h1>FK Student Club & Event Management System</h1>
            <p>Sign in using your registered account.</p>
        
            <?php if ($login_result === false): ?>
                <div class="alert alert-danger mb-2"><?= htmlspecialchars($login_message) ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" autocomplete="off">
                <input type="text"
                    class="login-input"
                    name="icNum"
                    placeholder="Username"
                    value="<?= htmlspecialchars($_POST['icNum'] ?? '') ?>"
                    required>

                <input type="password"
                    class="login-input"
                    name="password"
                    placeholder="Password"
                    required>

                <select class="login-input" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>
                        Administrator
                    </option>
                    <option value="student" <?= (($_POST['role'] ?? '') === 'student') ? 'selected' : '' ?>>
                        Student
                    </option>
                    <option value="committee" <?= (($_POST['role'] ?? '') === 'committee') ? 'selected' : '' ?>>
                        Club Committee Member
                    </option>
                </select>

                <button type="submit" class="login-btn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Sign In
                </button>
            </form>

            <div class="login-extra">
                <a href="Module1/forgot.php">Forgot Password?</a>
            </div>
        </div>
    </div>

</body>

</html>
