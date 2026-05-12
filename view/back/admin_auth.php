<?php
/**
 * Authentication check for back-office/admin pages
 * Ensures user is logged in and has admin role
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Check for remember token cookie
    if (isset($_COOKIE['remember_token'])) {
        require_once __DIR__ . '/../../controller/UserController.php';
        
        $controller = new UserController();
        $user = $controller->getUserByRememberToken($_COOKIE['remember_token']);
        
        if ($user) {
            // Restore session from remember token
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['statut'] = $user['statut'];
        } else {
            // Invalid token, clear cookie and redirect to login
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            header('Location: ../front/signin.php');
            exit();
        }
    } else {
        // No session and no remember token, redirect to login
        header('Location: ../front/signin.php');
        exit();
    }
}

// Check if user has back-office access (admin, coach, nutritionist)
$userRole = strtolower(trim($_SESSION['role'] ?? $_SESSION['user_role'] ?? ''));

if (!in_array($userRole, ['admin', 'coach', 'nutritionist'])) {
    // Not a back-office user, redirect to front-end home
    header('Location: ../front/signin.php');
    exit();
}

// Check if account is deactivated or banned
if (isset($_SESSION['statut'])) {
    $statut = strtolower(trim($_SESSION['statut']));
    
    if ($statut === 'deactivated') {
        header('Location: ../front/reactivate_account.php');
        exit();
    }
    
    if ($statut === 'banned') {
        session_unset();
        session_destroy();
        header('Location: ../front/signin.php?error=banned');
        exit();
    }
}
