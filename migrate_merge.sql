-- ============================================================
-- migrate_merge.sql — Fusion des deux bases de données
-- smart_meal_planner
--
-- Ce script :
--   1. Adapte les tables EXISTANTES (recette_repas, repas, ingredient)
--      pour correspondre au schéma du dump externe
--   2. Crée les tables MANQUANTES avec IF NOT EXISTS
--   3. N'efface aucune donnée existante
--   4. Utilise ALTER TABLE ... ADD COLUMN IF NOT EXISTS (MariaDB 10.4+)
--
-- À exécuter via phpMyAdmin → onglet SQL
-- ============================================================

USE smart_meal_planner;

-- Désactiver temporairement les FK pour éviter les erreurs d'ordre
SET FOREIGN_KEY_CHECKS = 0;

-- ════════════════════════════════════════════════════════════
-- PARTIE 1 : Adapter les tables existantes
-- ════════════════════════════════════════════════════════════

-- ── recette_repas : ajouter les colonnes manquantes ──────────
-- Le dump externe a : description, ingredients
-- Notre table a déjà : etapes, video_youtube, etc.

ALTER TABLE `recette_repas`
    ADD COLUMN IF NOT EXISTS `description` TEXT NULL
        COMMENT 'Description courte de la recette'
        AFTER `nom_recette`,
    ADD COLUMN IF NOT EXISTS `ingredients` TEXT NULL
        COMMENT 'Liste des ingrédients en texte libre'
        AFTER `image_recette`;

-- ── repas : adapter pour correspondre au dump externe ────────
-- Le dump externe a id_recette nullable + id_categorie
-- Notre table a id_recette NOT NULL avec FK stricte

-- Rendre id_recette nullable (le dump externe l'autorise)
ALTER TABLE `repas`
    MODIFY COLUMN `id_recette` INT NULL DEFAULT NULL;

-- Supprimer l'ancienne contrainte FK si elle existe
ALTER TABLE `repas`
    DROP FOREIGN KEY IF EXISTS `fk_repas_recette`;

-- Recréer la FK avec ON DELETE SET NULL (plus souple)
ALTER TABLE `repas`
    ADD CONSTRAINT `fk_repas_recette`
    FOREIGN KEY (`id_recette`)
    REFERENCES `recette_repas`(`id_recette`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- Ajouter id_categorie si absent
ALTER TABLE `repas`
    ADD COLUMN IF NOT EXISTS `id_categorie` INT(11) NULL DEFAULT NULL
        AFTER `type_repas`;

-- ── ingredient : ajouter id_recette si absent ────────────────
-- Le dump externe a id_recette en plus de id_repas

-- Rendre id_repas nullable (le dump externe l'autorise)
ALTER TABLE `ingredient`
    MODIFY COLUMN `id_repas` INT NULL DEFAULT NULL;

-- Supprimer l'ancienne FK si elle existe
ALTER TABLE `ingredient`
    DROP FOREIGN KEY IF EXISTS `fk_ingredient_repas`;

-- Recréer avec ON DELETE SET NULL
ALTER TABLE `ingredient`
    ADD CONSTRAINT `ingredient_ibfk_1`
    FOREIGN KEY (`id_repas`)
    REFERENCES `repas`(`id_repas`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Ajouter id_recette si absent
ALTER TABLE `ingredient`
    ADD COLUMN IF NOT EXISTS `id_recette` INT(11) NULL DEFAULT NULL
        AFTER `id_repas`;

-- Ajouter la FK sur id_recette
ALTER TABLE `ingredient`
    ADD CONSTRAINT IF NOT EXISTS `ingredient_ibfk_2`
    FOREIGN KEY (`id_recette`)
    REFERENCES `recette_repas`(`id_recette`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- ════════════════════════════════════════════════════════════
-- PARTIE 2 : Créer les tables manquantes
-- ════════════════════════════════════════════════════════════

-- ── Utilisateurs ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user` (
    `id`               INT(11)       NOT NULL AUTO_INCREMENT,
    `nom`              VARCHAR(100)  NOT NULL,
    `prenom`           VARCHAR(100)  NOT NULL,
    `date_naissance`   DATE          DEFAULT NULL,
    `email`            VARCHAR(150)  NOT NULL,
    `mot_de_passe`     VARCHAR(255)  NOT NULL,
    `role`             VARCHAR(50)   DEFAULT 'user',
    `statut`           VARCHAR(50)   NOT NULL DEFAULT 'active',
    `sexe`             ENUM('Female','Male') NOT NULL,
    `experience`       VARCHAR(100)  DEFAULT NULL,
    `speciality`       VARCHAR(150)  DEFAULT NULL,
    `motivation`       TEXT          DEFAULT NULL,
    `profile_picture`  VARCHAR(255)  DEFAULT 'default.png',
    `remember_token`   VARCHAR(255)  DEFAULT NULL,
    `remember_expires` DATETIME      DEFAULT NULL,
    `email_verified`   TINYINT(1)    DEFAULT 0,
    `email_token`      VARCHAR(255)  DEFAULT NULL,
    `reset_token`      VARCHAR(255)  DEFAULT NULL,
    `reset_expires`    DATETIME      DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Catégories de produits ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categorieproduit` (
    `id_categorie` INT(11)       NOT NULL AUTO_INCREMENT,
    `nom`          VARCHAR(100)  NOT NULL,
    `description`  TEXT          DEFAULT NULL,
    `image`        VARCHAR(255)  DEFAULT NULL,
    PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Catégories de repas (ancienne) ───────────────────────────
CREATE TABLE IF NOT EXISTS `categorierepas` (
    `id_categorie`  INT(11)      NOT NULL AUTO_INCREMENT,
    `nom_categorie` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Catégories de repas (nouvelle) ───────────────────────────
CREATE TABLE IF NOT EXISTS `categorie_repas` (
    `id_categorie`  INT(11)      NOT NULL AUTO_INCREMENT,
    `nom_categorie` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Produits ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `produit` (
    `id`             INT(11)        NOT NULL AUTO_INCREMENT,
    `nom`            VARCHAR(255)   NOT NULL,
    `description`    TEXT           DEFAULT NULL,
    `prix`           DECIMAL(10,2)  NOT NULL,
    `quantiteStock`  INT(11)        DEFAULT 0,
    `estDurable`     TINYINT(1)     DEFAULT 0,
    `dateExpiration` DATE           DEFAULT NULL,
    `image`          VARCHAR(255)   DEFAULT NULL,
    `statut`         VARCHAR(50)    DEFAULT NULL,
    `categorie`      VARCHAR(100)   DEFAULT 'Autre',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Avis sur les produits ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `avis` (
    `id_avis`     INT(11)     NOT NULL AUTO_INCREMENT,
    `note`        INT(11)     DEFAULT NULL CHECK (`note` >= 0 AND `note` <= 5),
    `commentaire` TEXT        DEFAULT NULL,
    `date_avis`   DATE        DEFAULT (CURDATE()),
    `id_produit`  INT(11)     NOT NULL,
    `sentiment`   VARCHAR(10) DEFAULT NULL,
    PRIMARY KEY (`id_avis`),
    KEY `fk_avis_produit` (`id_produit`),
    CONSTRAINT `fk_avis_produit`
        FOREIGN KEY (`id_produit`) REFERENCES `produit`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Événements ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `evenement` (
    `id_event`    INT(11)        NOT NULL AUTO_INCREMENT,
    `titre`       VARCHAR(150)   NOT NULL,
    `description` TEXT           DEFAULT NULL,
    `date_debut`  DATETIME       NOT NULL,
    `date_fin`    DATETIME       NOT NULL,
    `lieu`        VARCHAR(150)   DEFAULT NULL,
    `capacite_max` INT(11)       DEFAULT NULL,
    `prix`        DECIMAL(10,2)  DEFAULT NULL,
    `statut`      VARCHAR(50)    DEFAULT NULL,
    `type`        VARCHAR(50)    DEFAULT NULL,
    `image`       VARCHAR(255)   DEFAULT NULL,
    `likes`       INT(11)        DEFAULT 0,
    PRIMARY KEY (`id_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Commentaires sur les événements ──────────────────────────
CREATE TABLE IF NOT EXISTS `commentaire_event` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `id_event`   INT(11)      NOT NULL,
    `auteur`     VARCHAR(100) NOT NULL,
    `contenu`    TEXT         NOT NULL,
    `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `id_event` (`id_event`),
    CONSTRAINT `commentaire_event_ibfk_1`
        FOREIGN KEY (`id_event`) REFERENCES `evenement`(`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Participations aux événements ─────────────────────────────
CREATE TABLE IF NOT EXISTS `participation` (
    `id_participation`        INT(11)      NOT NULL AUTO_INCREMENT,
    `id_event`                INT(11)      DEFAULT NULL,
    `nom`                     VARCHAR(100) NOT NULL,
    `prenom`                  VARCHAR(100) NOT NULL,
    `email`                   VARCHAR(150) NOT NULL,
    `statut`                  ENUM('en_attente','confirme','refuse') DEFAULT 'en_attente',
    `date_participation`      DATE         DEFAULT NULL,
    `nombre_places_reservees` INT(11)      NOT NULL DEFAULT 1,
    `mode_paiement`           ENUM('gratuit','espèces','carte','virement') NOT NULL DEFAULT 'gratuit',
    PRIMARY KEY (`id_participation`),
    KEY `fk_participation_event` (`id_event`),
    CONSTRAINT `fk_participation_event`
        FOREIGN KEY (`id_event`) REFERENCES `evenement`(`id_event`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Réactions aux événements ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `reaction` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `id_event`   INT(11)      NOT NULL,
    `type`       VARCHAR(20)  NOT NULL,
    `session_id` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_reaction` (`id_event`, `session_id`, `type`),
    CONSTRAINT `reaction_ibfk_1`
        FOREIGN KEY (`id_event`) REFERENCES `evenement`(`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Notes des événements ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rating` (
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `id_event`   INT(11)      NOT NULL,
    `stars`      TINYINT(4)   NOT NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_rating` (`id_event`, `session_id`),
    CONSTRAINT `rating_ibfk_1`
        FOREIGN KEY (`id_event`) REFERENCES `evenement`(`id_event`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Codes promo ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `promo_code` (
    `id`           INT(11)        NOT NULL AUTO_INCREMENT,
    `code`         VARCHAR(50)    NOT NULL,
    `discount`     DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `type`         ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `id_event`     INT(11)        DEFAULT NULL,
    `milestone_id` INT(11)        DEFAULT NULL,
    `max_uses`     INT(11)        DEFAULT NULL,
    `used_count`   INT(11)        NOT NULL DEFAULT 0,
    `expires_at`   DATETIME       DEFAULT NULL,
    `active`       TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`   DATETIME       DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Repas du planificateur (meal) ────────────────────────────
CREATE TABLE IF NOT EXISTS `meal` (
    `id_meal`    INT(11)      NOT NULL AUTO_INCREMENT,
    `nom_meal`   VARCHAR(150) NOT NULL,
    `type`       VARCHAR(50)  DEFAULT NULL,
    `calories`   FLOAT        DEFAULT NULL,
    `notes`      TEXT         DEFAULT NULL,
    `image`      VARCHAR(255) DEFAULT NULL,
    `recipe_url` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id_meal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Plans de repas ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mealplan` (
    `id_plan`     INT(11)      NOT NULL AUTO_INCREMENT,
    `nom`         VARCHAR(150) NOT NULL,
    `duree`       INT(11)      DEFAULT NULL,
    `date_debut`  DATE         DEFAULT NULL,
    `date_fin`    DATE         DEFAULT NULL,
    `objectif`    VARCHAR(150) DEFAULT NULL,
    `description` TEXT         DEFAULT NULL,
    `user_id`     INT(11)      DEFAULT NULL,
    PRIMARY KEY (`id_plan`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `mealplan_ibfk_1`
        FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Détails des plans de repas ────────────────────────────────
CREATE TABLE IF NOT EXISTS `plan_detail` (
    `id`        INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `plan_id`   INT(11) UNSIGNED NOT NULL,
    `meal_date` DATE             NOT NULL,
    `meal_type` VARCHAR(20)      NOT NULL,
    `meal_id`   INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_plan_date_type` (`plan_id`, `meal_date`, `meal_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Favoris ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `favourites` (
    `id`         INT(11) NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11) NOT NULL DEFAULT 1,
    `meal_id`    INT(11) NOT NULL,
    `created_at` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_meal` (`user_id`, `meal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ════════════════════════════════════════════════════════════
-- PARTIE 3 : Ajouter la FK manquante sur repas.id_categorie
-- (après création de categorie_repas)
-- ════════════════════════════════════════════════════════════

ALTER TABLE `repas`
    ADD CONSTRAINT IF NOT EXISTS `repas_ibfk_1`
    FOREIGN KEY (`id_categorie`)
    REFERENCES `categorie_repas`(`id_categorie`)
    ON DELETE SET NULL;

-- ════════════════════════════════════════════════════════════
-- PARTIE 4 : Mettre à jour la vue vue_repas_recette
-- pour inclure les nouvelles colonnes
-- ════════════════════════════════════════════════════════════

CREATE OR REPLACE VIEW `vue_repas_recette` AS
SELECT
    r.id_repas,
    r.nom              AS nom_repas,
    r.calories,
    r.proteines,
    r.glucides,
    r.lipides,
    r.description,
    r.type_repas,
    r.id_categorie,
    r.image_repas,
    r.created_at,
    rr.id_recette,
    rr.nom_recette,
    rr.description     AS description_recette,
    rr.etapes,
    rr.ingredients     AS ingredients_recette,
    rr.difficulte,
    rr.temps_prep,
    rr.temps_cuisson,
    rr.nb_personnes,
    rr.image_recette,
    rr.video_youtube
FROM repas r
LEFT JOIN recette_repas rr ON r.id_recette = rr.id_recette;

-- ════════════════════════════════════════════════════════════
-- PARTIE 5 : Données initiales (INSERT IGNORE = sans écrasement)
-- ════════════════════════════════════════════════════════════

-- Utilisateur par défaut
INSERT IGNORE INTO `user`
    (`id`, `nom`, `prenom`, `date_naissance`, `email`, `mot_de_passe`,
     `role`, `statut`, `sexe`, `profile_picture`, `email_token`)
VALUES
    (1, 'fff', 'dff', '2005-02-02', 'ranaabid1@gmail.com',
     '$2y$10$v9Mm4oJ/M5jtgvDVwxU/DeKU2VH.2w3hu/w4wODZZUxuepD7Nn1iu',
     'client', 'active', 'Female', 'default.png',
     'c71de535b6bf0d4568357a83269afe3ac0b428287ea1527b2778e037e5cdd0a0');

-- Produits par défaut
INSERT IGNORE INTO `produit` (`id`, `nom`, `description`, `prix`, `quantiteStock`, `estDurable`, `dateExpiration`, `statut`, `categorie`)
VALUES
    (1, 'tomate', '.............', 4.00, 20, 1, '2026-04-23', 'Disponible', 'fruit'),
    (2, 'fff', 'r', 10.00, 1, 0, '2026-05-12', 'Disponible', NULL);

-- Réactiver les FK
SET FOREIGN_KEY_CHECKS = 1;
