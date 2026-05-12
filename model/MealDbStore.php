<?php

declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Meal.php';

/**
 * Meals persistence in MySQL.
 *
 * Table `meal` columns:
 * - id_meal (PK, AUTO_INCREMENT)
 * - nom_meal  VARCHAR
 * - type      VARCHAR
 * - calories  FLOAT
 * - notes     TEXT
 * - image     VARCHAR(255)
 * - recipe_url VARCHAR(255)
 */
final class MealDbStore
{
    public static function tableExists(): bool
    {
        try {
            $pdo  = Database::pdo();
            $stmt = $pdo->query("SHOW TABLES LIKE 'meal'");
            return (bool) $stmt->fetchColumn();
        } catch (Throwable) {
            return false;
        }
    }

    /** @return Meal[] */
    public static function all(): array
    {
        $pdo  = Database::pdo();
        $rows = $pdo->query(
            "SELECT id_meal AS id, nom_meal AS name, `type` AS mealType,
                    calories, notes AS description,
                    COALESCE(image, '')      AS image,
                    COALESCE(recipe_url, '#') AS recipeUrl
             FROM meal ORDER BY id_meal ASC"
        )->fetchAll();

        $meals = [];
        foreach ($rows as $r) {
            $meals[] = new Meal(
                (int)    ($r['id']          ?? 0),
                (string) ($r['name']        ?? ''),
                (int)    ($r['calories']    ?? 0),
                (string) ($r['description'] ?? ''),
                (string) ($r['image']       ?? ''),
                (string) ($r['recipeUrl']   ?? '#'),
                (string) ($r['mealType']    ?? 'lunch')
            );
        }
        return $meals;
    }

    public static function countMeals(): int
    {
        return (int) Database::pdo()->query("SELECT COUNT(*) FROM meal")->fetchColumn();
    }

    /**
     * Sync meals from JSON rows into DB (insert missing; update image/recipe if empty).
     * @param array<int, array<string, mixed>> $rows
     */
    public static function syncFromJsonRows(array $rows): void
    {
        if ($rows === []) return;

        $pdo = Database::pdo();

        $selectExisting = $pdo->prepare(
            "SELECT id_meal, COALESCE(image,'') AS image, COALESCE(recipe_url,'#') AS recipeUrl
             FROM meal
             WHERE nom_meal = :name AND `type` = :type AND calories = :calories AND notes = :notes
             LIMIT 1"
        );

        $update = $pdo->prepare(
            "UPDATE meal SET image = :image, recipe_url = :recipeUrl WHERE id_meal = :id"
        );

        foreach ($rows as $r) {
            if (!is_array($r)) continue;

            $name   = (string) ($r['name']        ?? '');
            $type   = (string) ($r['mealType']    ?? 'lunch');
            $cal    = (int)    ($r['calories']     ?? 0);
            $notes  = (string) ($r['description'] ?? '');
            $image  = (string) ($r['image']        ?? '');
            $recipe = (string) ($r['recipeUrl']    ?? '#');

            if ($name === '') continue;

            $selectExisting->execute([':name' => $name, ':type' => $type, ':calories' => $cal, ':notes' => $notes]);
            $existing = $selectExisting->fetch();

            if ($existing) {
                $curImg    = (string) ($existing['image']     ?? '');
                $curRecipe = (string) ($existing['recipeUrl'] ?? '#');
                $needsUpdate = ($curImg === '' && $image !== '')
                            || (($curRecipe === '' || $curRecipe === '#') && $recipe !== '' && $recipe !== '#');
                if ($needsUpdate) {
                    $update->execute([
                        ':image'     => $curImg    !== '' ? $curImg    : $image,
                        ':recipeUrl' => ($curRecipe !== '' && $curRecipe !== '#') ? $curRecipe : $recipe,
                        ':id'        => (int) $existing['id_meal'],
                    ]);
                }
                continue;
            }

            self::insert(new Meal(0, $name, $cal, $notes, $image, $recipe, $type));
        }
    }

    public static function insert(Meal $m): int
    {
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO meal (nom_meal, `type`, calories, notes, image, recipe_url)
             VALUES (:name, :type, :calories, :notes, :image, :recipeUrl)"
        );
        $stmt->execute([
            ':name'      => $m->name,
            ':type'      => $m->mealType,
            ':calories'  => $m->calories,
            ':notes'     => $m->description,
            ':image'     => $m->image,
            ':recipeUrl' => $m->recipeUrl,
        ]);
        $id = (int) $pdo->lastInsertId();
        self::resequenceIds();
        return $id;
    }

    public static function update(int $id, Meal $m): void
    {
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare(
            "UPDATE meal
             SET nom_meal = :name, `type` = :type, calories = :calories,
                 notes = :notes, image = :image, recipe_url = :recipeUrl
             WHERE id_meal = :id"
        );
        $stmt->execute([
            ':id'        => $id,
            ':name'      => $m->name,
            ':type'      => $m->mealType,
            ':calories'  => $m->calories,
            ':notes'     => $m->description,
            ':image'     => $m->image,
            ':recipeUrl' => $m->recipeUrl,
        ]);
    }

    public static function delete(int $id): void
    {
        $pdo  = Database::pdo();
        $stmt = $pdo->prepare("DELETE FROM meal WHERE id_meal = :id");
        $stmt->execute([':id' => $id]);
        self::resequenceIds();
    }

    public static function resequenceIds(): void
    {
        $pdo = Database::pdo();
        $ids = $pdo->query("SELECT id_meal FROM meal ORDER BY id_meal ASC")->fetchAll(PDO::FETCH_COLUMN);

        if (!$ids) {
            try { $pdo->exec("ALTER TABLE meal AUTO_INCREMENT = 1"); } catch (Throwable) {}
            return;
        }

        $map = [];
        $n   = 0;
        foreach ($ids as $old) {
            $n++;
            if ((int)$old !== $n) $map[(int)$old] = $n;
        }

        if ($map === []) {
            try { $pdo->exec("ALTER TABLE meal AUTO_INCREMENT = " . ((int)count($ids) + 1)); } catch (Throwable) {}
            return;
        }

        $pdo->beginTransaction();
        try {
            $maxId  = (int) $pdo->query("SELECT MAX(id_meal) FROM meal")->fetchColumn();
            $offset = $maxId + 100000;

            $updTmp   = $pdo->prepare("UPDATE meal SET id_meal = :tmp WHERE id_meal = :old");
            $updFinal = $pdo->prepare("UPDATE meal SET id_meal = :new WHERE id_meal = :tmp");

            foreach ($map as $oldId => $newId) {
                $updTmp->execute([':tmp' => $offset + $oldId, ':old' => $oldId]);
            }
            foreach ($map as $oldId => $newId) {
                $updFinal->execute([':new' => $newId, ':tmp' => $offset + $oldId]);
            }

            $pdo->commit();
            try { $pdo->exec("ALTER TABLE meal AUTO_INCREMENT = " . ((int)count($ids) + 1)); } catch (Throwable) {}
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
