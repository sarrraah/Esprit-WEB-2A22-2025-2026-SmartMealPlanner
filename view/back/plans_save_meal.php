<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/Database.php';
require_once __DIR__ . '/../../model/Plan.php';

$mealId   = (int)   ($_POST['meal_id']   ?? 0);
$mealType = trim(   $_POST['meal_type']  ?? '');
$mealDate = trim(   $_POST['meal_date']  ?? '');

if (!$mealId || !$mealType || !$mealDate) {
    echo json_encode(['ok' => false, 'message' => 'Missing required fields.']);
    exit;
}

$plan = Plan::first();
if (!$plan) {
    echo json_encode(['ok' => false, 'message' => 'No active plan found.']);
    exit;
}

try {
    $pdo = Database::pdo();

    // Check if an entry already exists for this plan + date + type
    $check = $pdo->prepare(
        'SELECT id FROM plan_detail WHERE plan_id = :pid AND meal_date = :dt AND meal_type = :type'
    );
    $check->execute([':pid' => $plan->id, ':dt' => $mealDate, ':type' => $mealType]);
    $existing = $check->fetchColumn();

    if ($existing) {
        // UPDATE — replace the meal of the same type
        $stmt = $pdo->prepare(
            'UPDATE plan_detail SET meal_id = :mid WHERE id = :id'
        );
        $stmt->execute([':mid' => $mealId, ':id' => $existing]);
    } else {
        // INSERT — new entry for this type on this date
        $stmt = $pdo->prepare(
            'INSERT INTO plan_detail (plan_id, meal_date, meal_type, meal_id)
             VALUES (:pid, :dt, :type, :mid)'
        );
        $stmt->execute([
            ':pid'  => $plan->id,
            ':dt'   => $mealDate,
            ':type' => $mealType,
            ':mid'  => $mealId,
        ]);
    }

    echo json_encode(['ok' => true, 'message' => 'Meal saved successfully.']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
