<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/Meal.php';

// Build base URL for assets
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$projRoot = str_replace('\\', '/', realpath(__DIR__ . '/../../'));
$basePath = str_replace($docRoot, '', $projRoot);
$assetBase = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/view/assets/';

try {
    $meals = Meal::all();
    $result = [];
    $typeLabels = [
        'breakfast' => 'Breakfast',
        'lunch'     => 'Lunch',
        'dinner'    => 'Dinner',
        'snack'     => 'Snacks',
    ];
    foreach ($meals as $i => $m) {
        // Resolve image to absolute URL
        $img = $m->image;
        if ($img && !str_starts_with($img, 'http')) {
            // Strip leading "assets/" if present, then prepend asset base
            $img = $assetBase . ltrim(preg_replace('#^assets/#', '', $img), '/');
        }
        $result[] = [
            'id'           => $m->id,
            'displayId'    => $i + 1,
            'name'         => $m->name,
            'mealType'     => $m->mealType,
            'mealTypeLabel'=> $typeLabels[$m->mealType] ?? ucfirst($m->mealType),
            'calories'     => $m->calories,
            'description'  => $m->description,
            'image'        => $img,
            'recipeUrl'    => $m->recipeUrl,
        ];
    }
    echo json_encode($result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
