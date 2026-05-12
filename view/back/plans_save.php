<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../model/Plan.php';
require_once __DIR__ . '/../../model/PlanDbStore.php';

$action    = trim($_POST['action']          ?? '');
$editingId = (int) ($_POST['editing_id']    ?? 0);
$name      = trim($_POST['name']            ?? '');
$objective = trim($_POST['objective']       ?? '');
$duration  = max(1, (int) ($_POST['duration'] ?? 7));
$calories  = trim($_POST['total_calories']  ?? '');
$desc      = trim($_POST['description']     ?? '');

if ($name === '') {
    echo json_encode(['ok' => false, 'message' => 'Plan name is required.']);
    exit;
}

// Build description with calorie target
if ($calories !== '' && !str_contains($desc, 'Daily target:')) {
    $desc = "Daily target: {$calories} kcal";
}

$dateDebut = date('Y-m-d');

try {
    if ($editingId > 0) {
        $existing = Plan::find($editingId);
        PlanDbStore::update($editingId, [
            'nom'         => $name,
            'duree'       => $duration,
            'date_debut'  => $existing ? ($existing->dateDebut ?: $dateDebut) : $dateDebut,
            'date_fin'    => date('Y-m-d', strtotime("+{$duration} days", strtotime($existing->dateDebut ?: $dateDebut))),
            'objectif'    => $objective,
            'description' => $desc,
        ]);
        echo json_encode(['ok' => true, 'message' => 'Plan updated successfully.']);
    } else {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        PlanDbStore::insert([
            'nom'         => $name,
            'duree'       => $duration,
            'date_debut'  => $dateDebut,
            'date_fin'    => date('Y-m-d', strtotime("+{$duration} days")),
            'objectif'    => $objective,
            'description' => $desc,
            'user_id'     => $userId,
        ]);
        echo json_encode(['ok' => true, 'message' => 'Plan created successfully.']);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
