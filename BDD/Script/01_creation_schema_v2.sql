-- =======================================================
-- SCRIPT 01 : CRÉATION DU NOUVEAU SCHÉMA (4FN)
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `v2_departement` (
  `id_dpt` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_dpt` VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_batiment` (
  `id_bat` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_bat` VARCHAR(50) NOT NULL,
  `ad_bat` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_plan` (
  `id_plan` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `donnee` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_salle` (
  `id_salle` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_salle` VARCHAR(50) NOT NULL,
  `etage` TINYINT DEFAULT 0,
  `id_bat` INT UNSIGNED NOT NULL,
  `id_dpt` INT UNSIGNED NOT NULL,
  `id_plan` INT UNSIGNED NOT NULL,
  `capacite` INT UNSIGNED NOT NULL,
  `intercal` TINYINT(1) DEFAULT 1,
  CONSTRAINT `fk_v2_salle_bat` FOREIGN KEY (`id_bat`) REFERENCES `v2_batiment`(`id_bat`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_salle_dpt` FOREIGN KEY (`id_dpt`) REFERENCES `v2_departement`(`id_dpt`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_salle_plan` FOREIGN KEY (`id_plan`) REFERENCES `v2_plan`(`id_plan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_promotion` (
  `id_promo` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_promo` VARCHAR(50) NOT NULL,
  `annee` INT DEFAULT NULL,
  `id_dpt` INT UNSIGNED NOT NULL,
  CONSTRAINT `fk_v2_promo_dpt` FOREIGN KEY (`id_dpt`) REFERENCES `v2_departement`(`id_dpt`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_groupe` (
  `id_groupe` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_groupe` VARCHAR(50) NOT NULL,
  `id_promo` INT UNSIGNED NOT NULL,
  `nb_etud` INT UNSIGNED NOT NULL,
  CONSTRAINT `fk_v2_grp_promo` FOREIGN KEY (`id_promo`) REFERENCES `v2_promotion`(`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_etudiant` (
  `id_etudiant` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_etudiant` VARCHAR(50) NOT NULL,
  `prenom_etudiant` VARCHAR(50) NOT NULL,
  `id_groupe` INT UNSIGNED NOT NULL,
  `demigr` TINYINT(1) DEFAULT 0,
  `tiers_temps` TINYINT(1) DEFAULT 0,
  `mob_reduite` TINYINT(1) DEFAULT 0,
  CONSTRAINT `fk_v2_etu_grp` FOREIGN KEY (`id_groupe`) REFERENCES `v2_groupe`(`id_groupe`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_matiere` (
  `id_mat` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_mat` VARCHAR(100) NOT NULL,
  `id_promo` INT UNSIGNED NOT NULL,
  CONSTRAINT `fk_v2_mat_promo` FOREIGN KEY (`id_promo`) REFERENCES `v2_promotion`(`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_enseignant` (
  `id_ens` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_ens` VARCHAR(50) NOT NULL,
  `prenom_ens` VARCHAR(50) NOT NULL,
  `sexe` CHAR(1) DEFAULT NULL,
  `login` VARCHAR(50) UNIQUE DEFAULT NULL,
  `pass` VARCHAR(255) DEFAULT NULL,
  `admin` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_enseigne` (
  `id_mat` INT UNSIGNED NOT NULL,
  `id_ens` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_mat`, `id_ens`),
  CONSTRAINT `fk_v2_ensg_mat` FOREIGN KEY (`id_mat`) REFERENCES `v2_matiere`(`id_mat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_ensg_ens` FOREIGN KEY (`id_ens`) REFERENCES `v2_enseignant`(`id_ens`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_devoir` (
  `id_devoir` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nom_devoir` VARCHAR(100) NOT NULL,
  `date_devoir` DATE NOT NULL,
  `heure_devoir` TIME NOT NULL,
  `duree_devoir` TIME NOT NULL,
  `id_mat` INT UNSIGNED DEFAULT NULL,
  CONSTRAINT `fk_v2_dev_mat` FOREIGN KEY (`id_mat`) REFERENCES `v2_matiere`(`id_mat`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_devoir_salle` (
  `id_devoir` INT UNSIGNED NOT NULL,
  `id_salle` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_devoir`, `id_salle`),
  CONSTRAINT `fk_v2_ds_dev` FOREIGN KEY (`id_devoir`) REFERENCES `v2_devoir`(`id_devoir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_ds_salle` FOREIGN KEY (`id_salle`) REFERENCES `v2_salle`(`id_salle`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_devoir_groupe` (
  `id_devoir` INT UNSIGNED NOT NULL,
  `id_groupe` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_devoir`, `id_groupe`),
  CONSTRAINT `fk_v2_dg_dev` FOREIGN KEY (`id_devoir`) REFERENCES `v2_devoir`(`id_devoir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_dg_grp` FOREIGN KEY (`id_groupe`) REFERENCES `v2_groupe`(`id_groupe`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_devoir_promo` (
  `id_devoir` INT UNSIGNED NOT NULL,
  `id_promo` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_devoir`, `id_promo`),
  CONSTRAINT `fk_v2_dp_dev` FOREIGN KEY (`id_devoir`) REFERENCES `v2_devoir`(`id_devoir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_dp_promo` FOREIGN KEY (`id_promo`) REFERENCES `v2_promotion`(`id_promo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_placement` (
  `id_etudiant` INT UNSIGNED NOT NULL,
  `id_devoir` INT UNSIGNED NOT NULL,
  `id_salle` INT UNSIGNED NOT NULL,
  `place_x` INT UNSIGNED NOT NULL,
  `place_y` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_etudiant`, `id_devoir`),
  CONSTRAINT `fk_v2_plac_etu` FOREIGN KEY (`id_etudiant`) REFERENCES `v2_etudiant`(`id_etudiant`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_plac_dev` FOREIGN KEY (`id_devoir`) REFERENCES `v2_devoir`(`id_devoir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_plac_salle` FOREIGN KEY (`id_salle`) REFERENCES `v2_salle`(`id_salle`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `v2_surveille` (
  `id_ens` INT UNSIGNED NOT NULL,
  `id_devoir` INT UNSIGNED NOT NULL,
  `id_salle` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id_ens`, `id_devoir`),
  CONSTRAINT `fk_v2_surv_ens` FOREIGN KEY (`id_ens`) REFERENCES `v2_enseignant`(`id_ens`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_surv_dev` FOREIGN KEY (`id_devoir`) REFERENCES `v2_devoir`(`id_devoir`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_v2_surv_salle` FOREIGN KEY (`id_salle`) REFERENCES `v2_salle`(`id_salle`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optimisation des performances
CREATE INDEX idx_etudiant_nom ON v2_etudiant(nom_etudiant, prenom_etudiant);
CREATE INDEX idx_devoir_date ON v2_devoir(date_devoir);
CREATE INDEX idx_placement_salle_devoir ON v2_placement(id_salle, id_devoir);

SET FOREIGN_KEY_CHECKS = 1;