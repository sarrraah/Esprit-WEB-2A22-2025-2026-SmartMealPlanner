<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/Meal.php';
require_once __DIR__ . '/../../model/MealDbStore.php';

$errors = [];

$name       = trim($_POST['name']       ?? '');
$mealType   = trim($_POST['meal_type']  ?? '');
$calories   = trim($_POST['calories']   ?? '');
$desc       = trim($_POST['description']?? '');
$recipeUrl  = trim($_POST['recipe_url'] ?? '');
$editingId  = (int)($_POST['editing_id'] ?? 0);
$existingImg= trim($_POST['existing_image'] ?? '');

// Validation
if ($name === '')     $errors[] = 'Name is required.';
if ($mealType === '') $errors[] = 'Meal type is required.';
if ($calories === '') $errors[] = 'Calories are required.';
elseif (!ctype_digit($calories) || (int)$calories < 0 || (int)$calories > 3000)
    $errors[] = 'Calories must be a whole number between 0 and 3000.';
if ($desc === '')     $errors[] = 'Description is required.';
if ($recipeUrl !== '' && !preg_match('#^https?://.+#', $recipeUrl))
    $errors[] = 'Recipe URL must start with http:// or https://.';

// Handle image upload
$imagePath = $existingImg;
if (isset($_FILES['meal_image']) && $_FILES['meal_image']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    $mime    = mime_content_type($_FILES['meal_image']['tmp_name']);
    if (!in_array($mime, $allowed)) {
        $errors[] = 'Image must be JPEG, PNG, WebP, or GIF.';
    } elseif ($_FILES['meal_image']['size'] > 5 * 1024 * 1024) {
        $errors[] = 'Image must be under 5 MB.';
    } else {
        $uploadDir = __DIR__ . '/../../_project_files/uploads/meals/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $ext      = pathinfo($_FILES['meal_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('meal_', true) . '.' . strtolower($ext);
        if (move_uploaded_file($_FILES['meal_image']['tmp_name'], $uploadDir . $filename)) {
            $imagePath = '_project_files/uploads/meals/' . $filename;
        } else {
            $errors[] = 'Failed to save image.';
        }
    }
} elseif ($editingId === 0 && $existingImg === '') {
    $errors[] = 'Image is required for new meals.';
}

if ($errors) {
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

$meal = new Meal(
    $editingId,
    $name,
    (int)$calories,
    $desc,
    $imagePath,
    $recipeUrl ?: '#',
    $mealType
);

try {
    if ($editingId > 0) {
        MealDbStore::update($editingId, $meal);
        echo json_encode(['ok' => true, 'message' => 'Meal updated successfully.']);
    } else {
        $newId = MealDbStore::insert($meal);
        echo json_encode(['ok' => true, 'message' => 'Meal added successfully.', 'id' => $newId]);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'errors' => [$e->getMessage()]]);
}
