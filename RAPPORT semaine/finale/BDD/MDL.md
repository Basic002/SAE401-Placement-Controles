BATIMENT ( <u>id_bat</u>, nom_bat, ad_bat )

DEPARTEMENT ( <u>id_dpt</u>, nom_dpt )

PLAN ( <u>id_plan</u>, donnee, capacite_max )

PROMOTION ( <u>id_promo</u>, nom_promo, annee, #<u>id_dpt</u> )

SALLE ( <u>id_salle</u>, nom_salle, etage, #<u>id_bat</u>, #<u>id_dpt</u>, #<u>id_plan</u>, capacite, intercal, type_salle )

GROUPE ( <u>id_groupe</u>, nom_groupe, #<u>id_promo</u> )

MATIERE ( <u>id_mat</u>, nom_mat, #<u>id_promo</u> )

ENSEIGNANT ( <u>id_ens</u>, nom_ens, prenom_ens, sexe, login, pass, admin )

ETUDIANT ( <u>id_etudiant</u>, nom_etudiant, prenom_etudiant, #<u>id_groupe</u>, demigr, tiers_temps, mob_reduite, premier_rang )

DEVOIR ( <u>id_devoir</u>, nom_devoir, date_devoir, heure_devoir, duree_devoir, #<u>id_mat</u> )

ENSEIGNE ( #<u>id_mat</u>, #<u>id_ens</u> )

DEVOIR_GROUPE ( #<u>id_devoir</u>, #<u>id_groupe</u> )

DEVOIR_PROMO ( #<u>id_devoir</u>, #<u>id_promo</u> )

DEVOIR_SALLE ( #<u>id_devoir</u>, #<u>id_salle</u> )

PLACEMENT ( #<u>id_etudiant</u>, #<u>id_devoir</u>, #<u>id_salle</u>, place_x, place_y )

SURVEILLE ( #<u>id_ens</u>, #<u>id_devoir</u>, #<u>id_salle</u> )