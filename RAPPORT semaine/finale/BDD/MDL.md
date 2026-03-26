**BATIMENT** ( **id_bat**, nom_bat, ad_bat )

**DEPARTEMENT** ( **id_dpt**, nom_dpt )

**PLAN** ( **id_plan**, donnee, capacite_max )

**PROMOTION** ( **id_promo**, nom_promo, annee, #*id_dpt* )

**SALLE** ( **id_salle**, nom_salle, etage, #*id_bat*, #*id_dpt*, #*id_plan*, capacite, intercal, type_salle )

**GROUPE** ( **id_groupe**, nom_groupe, #*id_promo* )

**MATIERE** ( **id_mat**, nom_mat, #*id_promo* )

**ENSEIGNANT** ( **id_ens**, nom_ens, prenom_ens, sexe, login, pass, admin )

**ETUDIANT** ( **id_etudiant**, nom_etudiant, prenom_etudiant, #*id_groupe*, demigr, tiers_temps, mob_reduite, premier_rang )

**DEVOIR** ( **id_devoir**, nom_devoir, date_devoir, heure_devoir, duree_devoir, #*id_mat* )

**ENSEIGNE** ( #**id_mat**, #**id_ens** )

**DEVOIR_GROUPE** ( #**id_devoir**, #**id_groupe** )

**DEVOIR_PROMO** ( #**id_devoir**, #**id_promo** )

**DEVOIR_SALLE** ( #**id_devoir**, #**id_salle** )

**PLACEMENT** ( #**id_etudiant**, #**id_devoir**, #*id_salle*, place_x, place_y )

**SURVEILLE** ( #**id_ens**, #**id_devoir**, #*id_salle* )

---

*Légende :*
* **Gras** : Clé Primaire (PK)
* #*Italique* : Clé Étrangère (FK)
* #**Gras** : Clé Primaire ET Clé Étrangère