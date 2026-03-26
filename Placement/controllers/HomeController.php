<?php
class HomeController {
    
    //Affiche la page d'accueil de l'application
    public function index(): void {
        //Vérification de sécurité
        if (!isset($_SESSION['login'])) {
            header('Location: index.php?action=login');
            exit();
        }

        //Préparation des variables pour le layout
        $titre_page = "Accueil - InfoPlacement";

        //Mise en mémoire tampon de la vue spécifique à l'accueil
        ob_start();
        
        // On inclut la vue de l'accueil
        require_once 'views/home/home.php'; 
        
        //On récupère le contenu généré dans la variable attendue par layout.php
        $contenu_page = ob_get_clean();

        //Inclusion du layout global qui va afficher le tout
        require_once 'views/layout.php';
    }
}
?>