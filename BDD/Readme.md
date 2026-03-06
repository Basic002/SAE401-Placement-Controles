# Refonte et Migration de la Base de Données "InfoPlacement"

### `01_creation_schema_v2.sql` (Structure & Optimisation)
Ce script DDL met en place la nouvelle architecture 4FN :
- Création des nouvelles tables en zone "staging" avec le préfixe `v2_` pour éviter les conflits de nommage.
- Optimisation des types de données (ex: `INT UNSIGNED` pour les clés, `TINYINT(1)` pour les booléens).
- Mise en place stricte des contraintes d'intégrité (`FOREIGN KEY` avec `ON DELETE CASCADE` ou `RESTRICT`).
- Création d'**index de performances** sur les colonnes stratégiques (recherches nominales, dates des devoirs, croisements salle/devoir).

### `02_migration_donnees.sql` (Transfert & Assainissement)
Ce script DML est le cœur de la migration. Il transfère les données de l'ancien schéma vers le nouveau :
- **Extraction dynamique :** Il récupère l'ancien `id_mat` caché dans les tables de liaison via un `COALESCE` pour l'injecter proprement dans la table `v2_devoir`.
- **Filtre anti-orphelins :** Il intègre une sous-requête de sécurité pour remplacer par `NULL` les identifiants de matières obsolètes
- **Purge des doublons 4FN :** Il utilise la clause `DISTINCT` pour ventiler proprement les données de l'ancien produit cartésien vers les nouvelles tables indépendantes.

### `03_bascule_finale.sql` (Déploiement)
Ce script finalise l'opération de manière atomique :
- Suppression (`DROP`) de l'ancien schéma défectueux en respectant l'ordre des dépendances.
- Renommage (`RENAME TABLE`) simultané des tables `v2_` pour leur donner leur nom définitif. Cette opération est instantanée, garantissant le **Zéro Downtime**.