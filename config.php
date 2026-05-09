<?php
/**
 * config.php — Configuration et connexion à la base de données
 *
 * Fusionne les deux versions :
 *   - Classe config (Singleton PDO) pour le module gestion-recette
 *   - Constantes et fonctions utilitaires pour les autres modules
 */

// ── Constantes de connexion ───────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_meal_planner');

// ── Connexion PDO globale (pour les modules qui utilisent $pdo directement) ──
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// ── Fonction utilitaire : statut automatique d'un produit ─────
function determinerStatut($quantiteStock, $dateExpiration) {
    $dateActuelle = new DateTime();
    $dateExp = DateTime::createFromFormat('Y-m-d', $dateExpiration);
    if ($dateExp && $dateExp < $dateActuelle) {
        return 'Expired';
    } elseif ($quantiteStock == 0) {
        return 'Out of Stock';
    } elseif ($quantiteStock > 0) {
        return 'Available';
    }
    return 'Unknown';
}

// ── Dossier d'uploads ─────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/smart_meal_planner/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/uploads/');

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Extensions autorisées pour les images
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// ── Classe config (Singleton PDO) pour le module gestion-recette ─
class config
{
    /** @var PDO|null Instance unique de la connexion PDO */
    private static $pdo = null;

    /**
     * Retourne la connexion PDO unique (lazy initialization).
     * Exécute aussi les migrations automatiques au premier appel.
     */
    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_TIMEOUT => 5]
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec("SET NAMES utf8mb4");

                // Migrations automatiques au démarrage
                self::runMigrations(self::$pdo);

            } catch (Exception $e) {
                die('Erreur : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /**
     * Migrations automatiques — ajoute les colonnes manquantes
     * sans toucher aux données existantes.
     */
    private static function runMigrations(PDO $pdo): void
    {
        try {
            // 1. video_youtube dans recette_repas
            $col = $pdo->query("SHOW COLUMNS FROM recette_repas LIKE 'video_youtube'");
            if ($col->rowCount() === 0) {
                $pdo->exec("ALTER TABLE recette_repas
                    ADD COLUMN video_youtube VARCHAR(20) NULL
                    COMMENT 'ID vidéo YouTube' AFTER image_recette");
            }

            // 2. description dans recette_repas
            $col2 = $pdo->query("SHOW COLUMNS FROM recette_repas LIKE 'description'");
            if ($col2->rowCount() === 0) {
                $pdo->exec("ALTER TABLE recette_repas
                    ADD COLUMN description TEXT NULL
                    COMMENT 'Description courte' AFTER nom_recette");
            }

            // 3. ingredients dans recette_repas
            $col3 = $pdo->query("SHOW COLUMNS FROM recette_repas LIKE 'ingredients'");
            if ($col3->rowCount() === 0) {
                $pdo->exec("ALTER TABLE recette_repas
                    ADD COLUMN ingredients TEXT NULL
                    COMMENT 'Ingrédients en texte libre' AFTER image_recette");
            }

            // 4. id_categorie dans repas
            $col4 = $pdo->query("SHOW COLUMNS FROM repas LIKE 'id_categorie'");
            if ($col4->rowCount() === 0) {
                $pdo->exec("ALTER TABLE repas
                    ADD COLUMN id_categorie INT NULL DEFAULT NULL AFTER type_repas");
            }

            // 5. id_recette dans ingredient
            $col5 = $pdo->query("SHOW COLUMNS FROM ingredient LIKE 'id_recette'");
            if ($col5->rowCount() === 0) {
                $pdo->exec("ALTER TABLE ingredient
                    ADD COLUMN id_recette INT NULL DEFAULT NULL AFTER id_repas");
            }

        } catch (\Exception $e) {
            // Silencieux — retentée à la prochaine requête
        }
    }
}
