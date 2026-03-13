<?php
class SalleController {
    
    public function afficherSalles() {
        // 1. Vérifier si l'utilisateur est connecté (logique qui était dans votre index.php)
        if(!isset($_SESSION['login'])) {
            header('Location: index.php?action=login');
            exit();
        }

        // 2. Appeler le Modèle pour récupérer les données (ex: liste des salles)
        // require_once 'models/SalleModel.php';
        // $salles = SalleModel::getAllSalles($pdo);

        // 3. Charger la vue (l'affichage)
        $titre_page = "Gestion des Salles";
        
        // On met en mémoire tampon l'affichage de la page spécifique
        ob_start();
        require_once 'views/salle/gest_salle.php'; 
        $contenu_page = ob_get_clean(); // On stocke la page dans une variable

        // 4. On inclut le layout global (votre menu, header, footer) qui va afficher $contenu_page
        require_once 'views/layout.php';
    }
}