<?php
/**
 * Authentication check for front-end pages
 * Ensures user is logged in before accessing protected pages
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
            header('Location: signin.php');
            exit();
        }
    } else {
        // No session and no remember token, redirect to login
        header('Location: signin.php');
        exit();
    }
}

// Check if account is deactivated or banned
if (isset($_SESSION['statut'])) {
    $statut = strtolower(trim($_SESSION['statut']));
    
    // Allow access to reactivate_account.php if deactivated
    $currentScript = basename($_SERVER['PHP_SELF']);
    
    if ($statut === 'deactivated' && $currentScript !== 'reactivate_account.php') {
        header('Location: reactivate_account.php');
        exit();
    }
    
    if ($statut === 'banned') {
        session_unset();
        session_destroy();
        header('Location: signin.php?error=banned');
        exit();
    }
}
