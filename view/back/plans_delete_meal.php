<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/Database.php';
require_once __DIR__ . '/../../model/Plan.php';

$pmId = (int) ($_POST['pm_id'] ?? 0);

if (!$pmId) {
    echo json_encode(['ok' => false, 'message' => 'Missing plan_detail ID.']);
    exit;
}

$plan = Plan::first();
if (!$plan) {
    echo json_encode(['ok' => false, 'message' => 'No active plan found.']);
    exit;
}

try {
    $pdo = Database::pdo();

    // Only delete if it belongs to the current plan (security check)
    $stmt = $pdo->prepare(
        'DELETE FROM plan_detail WHERE id = :id AND plan_id = :pid'
    );
    $stmt->execute([':id' => $pmId, ':pid' => $plan->id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => true, 'message' => 'Meal removed from plan.']);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Meal not found or not authorized.']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
