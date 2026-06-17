<?php

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = '../login.php' . ($query !== '' ? '?' . $query : '');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/../INCLUDE/db.php';

    $icNum = trim($_POST['icNum'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = strtolower(trim($_POST['role'] ?? ''));

    if ($icNum !== '' && $password !== '' && $role !== '') {
        $result = loginUser($icNum, $password, $role);
        if ($result['success'] && !empty($result['redirect'])) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['user']['role'] = strtolower((string) $_SESSION['user']['role']);
            // $dest = preg_replace('#^Module1/#', '', $result['redirect']);
            header('Location: ' . $result['redirect']);
            exit;
        }
    }
}

header('Location: ' . $target);
exit;
