<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/MealDbStore.php';

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['ok' => false, 'errors' => ['Invalid meal ID.']]);
    exit;
}

try {
    MealDbStore::delete($id);
    echo json_encode(['ok' => true, 'message' => 'Meal deleted successfully.']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'errors' => [$e->getMessage()]]);
}
