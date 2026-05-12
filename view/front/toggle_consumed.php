<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../model/Plan.php';

$mealType = trim($_POST['meal_type'] ?? '');
$date     = trim($_POST['date']     ?? '');

if (!$mealType || !$date) {
    echo json_encode(['ok' => false, 'error' => 'Missing parameters']);
    exit;
}

$plan = Plan::first();
if (!$plan) {
    echo json_encode(['ok' => false, 'error' => 'No active plan']);
    exit;
}

$sessionKey = 'consumed_' . $plan->id . '_' . $date;
if (!isset($_SESSION[$sessionKey])) {
    $_SESSION[$sessionKey] = [];
}

// Toggle
if (isset($_SESSION[$sessionKey][$mealType])) {
    unset($_SESSION[$sessionKey][$mealType]);
    $consumed = false;
} else {
    $_SESSION[$sessionKey][$mealType] = true;
    $consumed = true;
}

echo json_encode(['ok' => true, 'consumed' => $consumed]);
