<?php

/**
 * Classe de base pour tous les contrôleurs.
 *
 * Fournit trois helpers réutilisables :
 *  - render()      : charge une vue dans le layout
 *  - exigerAdmin() : bloque l'accès si l'utilisateur n'est pas admin
 *  - jsonResponse(): envoie une réponse JSON et arrête l'exécution
 */
abstract class Controller
{
    /**
     * Rend une vue dans le layout global.
     *
     * @param string               $vue   Chemin relatif depuis /views/ (ex: 'home/home.php')
     * @param string               $titre Titre de la page HTML
     * @param array<string, mixed> $data  Variables injectées dans la vue via extract()
     */
    protected function render(string $vue, string $titre, array $data = []): void
    {
        global $pdo;
        // Injection des variables dans la portée de la vue
        // EXTR_SKIP empêche d'écraser $pdo, $titre_page, $contenu_page
        extract($data, EXTR_SKIP);

        $titre_page = $titre;

        ob_start();
        require __DIR__ . '/../views/' . $vue;
        $contenu_page = ob_get_clean();

        require __DIR__ . '/../views/layout.php';
    }

    /**
     * Vérifie que l'utilisateur connecté est administrateur.
     * Redirige vers l'accueil sinon.
     */
    protected function exigerAdmin(): void
    {
        if (empty($_SESSION['droit'])) {
            header('Location: index.php?action=home');
            exit();
        }
    }

    /**
     * Envoie une réponse JSON et arrête l'exécution.
     *
     * @param mixed $data
     * @param int   $status Code HTTP (200, 400, 403…)
     */
    protected function jsonResponse(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
