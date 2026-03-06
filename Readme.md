# SAE 4.01 - Application de Gestion des Placements d'Examens

Bienvenue sur le dépôt principal de notre projet de SAE. 
Ce projet a pour objectif la refonte, l'optimisation et le développement de l'application "InfoPlacement", permettant de gérer de manière automatisée et optimisée le placement des étudiants lors de leurs évaluations.

---

## Gestion de Projet (Trello)

Le suivi des tâches, l'organisation de nos sprints et la répartition du travail en équipe sont pilotés de manière agile sur notre tableau Trello. 

👉 **[Rejoindre notre tableau Trello (Lien d'invitation direct)](https://trello.com/invite/b/69a8448cae2785b425143dcc/ATTI6fdaa55dadbfecc017b321e547531296D307B0CF/sae-401)**

---

## Architecture du Dépôt

Ce dépôt est organisé en plusieurs répertoires distincts correspondant aux différentes phases et composantes de notre projet :

* **`Placement/`** * Contient l'intégralité du code source de l'application finale. 
  * C'est ici que se trouve la logique métier, les interfaces utilisateur et les accès aux données.

* **`BDD/`** * Contient tout le travail d'architecture de la base de données.
  * Vous y trouverez le schéma de la base normalisée en **Quatrième Forme Normale (4FN)**, ainsi que les trois scripts SQL (Création, Migration des données, et Bascule) permettant de déployer la nouvelle architecture sans perte de données.

* **`Rapport analyse/`** * Contient notre rapport d'analyse initial.
  * Il détaille l'étude de l'existant, l'identification des anomalies (violation des formes normales) et nos choix d'architecture technique et logicielle.

* **`Rapport semaine/`** * Regroupe les comptes-rendus de nos sprints hebdomadaires.
  * Permet de suivre l'évolution du projet, la répartition des tâches et la gestion des obstacles rencontrés au fil des semaines.

---
*Projet réalisé dans le cadre du BUT Informatique.*