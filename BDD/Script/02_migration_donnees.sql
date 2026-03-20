-- =======================================================
-- SCRIPT 02 : MIGRATION ET ASSAINISSEMENT DES DONNÉES
-- =======================================================

-- 1. Tables simples
INSERT INTO v2_departement SELECT * FROM departement;
INSERT INTO v2_batiment SELECT * FROM batiment;
INSERT INTO v2_plan SELECT * FROM plan;
INSERT INTO v2_salle SELECT * FROM salle;
INSERT INTO v2_promotion SELECT * FROM promotion;
INSERT INTO v2_groupe SELECT * FROM groupe;
INSERT INTO v2_etudiant SELECT * FROM etudiant;
INSERT INTO v2_matiere SELECT * FROM matiere;
INSERT INTO v2_enseignant SELECT * FROM enseignant;
INSERT INTO v2_enseigne SELECT * FROM enseigne;

-- 2. Migration complexe (3FN) avec assainissement de l'id_mat orphelin
INSERT INTO v2_devoir (id_devoir, nom_devoir, date_devoir, heure_devoir, duree_devoir, id_mat)
SELECT 
    d.id_devoir, 
    d.nom_devoir, 
    d.date_devoir, 
    d.heure_devoir, 
    d.duree_devoir, 
    (
        SELECT m.id_mat 
        FROM v2_matiere m 
        WHERE m.id_mat = COALESCE(
            (SELECT id_mat FROM devoir_groupe dg WHERE dg.id_devoir = d.id_devoir LIMIT 1),
            (SELECT id_mat FROM devoir_promo dp WHERE dp.id_devoir = d.id_devoir LIMIT 1)
        )
    ) AS id_mat_valide
FROM devoir d;

-- 3. Décomposition 4FN (Résolution des produits cartésiens)
INSERT INTO v2_devoir_salle (id_devoir, id_salle)
SELECT DISTINCT id_devoir, id_salle FROM devoir_groupe
UNION
SELECT DISTINCT id_devoir, id_salle FROM devoir_promo;

INSERT INTO v2_devoir_groupe (id_devoir, id_groupe)
SELECT DISTINCT id_devoir, id_groupe FROM devoir_groupe;

INSERT INTO v2_devoir_promo (id_devoir, id_promo)
SELECT DISTINCT id_devoir, id_promo FROM devoir_promo;

-- 4. Reprise des tables de placement et surveillance
INSERT INTO v2_placement SELECT * FROM placement;
INSERT INTO v2_surveille SELECT * FROM surveille;