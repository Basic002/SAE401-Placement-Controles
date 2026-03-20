-- =======================================================
-- SCRIPT 03 : BASCULE (DROP & RENAME)
-- =======================================================

SET FOREIGN_KEY_CHECKS = 0; 

-- Nettoyage de l'ancien schéma avec respect de l'ordre des dépendances
DROP TABLE IF EXISTS placement;
DROP TABLE IF EXISTS surveille;
DROP TABLE IF EXISTS devoir_groupe;
DROP TABLE IF EXISTS devoir_promo;
DROP TABLE IF EXISTS enseigne;
DROP TABLE IF EXISTS etudiant;
DROP TABLE IF EXISTS groupe;
DROP TABLE IF EXISTS matiere;
DROP TABLE IF EXISTS devoir;
DROP TABLE IF EXISTS promotion;
DROP TABLE IF EXISTS salle;
DROP TABLE IF EXISTS plan;
DROP TABLE IF EXISTS batiment;
DROP TABLE IF EXISTS departement;
DROP TABLE IF EXISTS enseignant;

SET FOREIGN_KEY_CHECKS = 1;

-- Renommage atomique pour officialiser le schéma V2
RENAME TABLE 
    v2_departement TO departement,
    v2_batiment TO batiment,
    v2_plan TO plan,
    v2_salle TO salle,
    v2_promotion TO promotion,
    v2_groupe TO groupe,
    v2_etudiant TO etudiant,
    v2_matiere TO matiere,
    v2_enseignant TO enseignant,
    v2_enseigne TO enseigne,
    v2_devoir TO devoir,
    v2_devoir_salle TO devoir_salle,
    v2_devoir_groupe TO devoir_groupe,
    v2_devoir_promo TO devoir_promo,
    v2_placement TO placement,
    v2_surveille TO surveille;