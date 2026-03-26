<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/EnseignantModel.php';

class EnseignantController extends Controller
{
    /**
     * Génère un login à partir du nom et du prénom
     * (même logique que l'ancien code : 4 premières lettres de chaque).
     */
    private static function genererLogin(string $nom, string $prenom): string
    {
        $search  = ['á','à','â','ä','ã','å','ç','é','è','ê','ë','í','ì','î','ï',
                    'ñ','ó','ò','ô','ö','õ','ú','ù','û','ü','ý','ÿ'];
        $replace = ['a','a','a','a','a','a','c','e','e','e','e','i','i','i','i',
                    'n','o','o','o','o','o','u','u','u','u','y','y'];

        $n = substr(str_replace($search, $replace, strtolower($nom)),   0, 4);
        $p = substr(str_replace($search, $replace, strtolower($prenom)), 0, 4);
        return $n . $p;
    }

    /**
     * Point d'entrée unique : liste + création + modification + suppression.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $erreur             = null;
        $succes             = null;
        $reopenCreateForm   = false;

        // --- SUPPRESSION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idEns = (int) ($_POST['id_ens'] ?? 0);
            // Sécurité : l'admin ne peut pas se supprimer lui-même
            if ($idEns > 0 && $idEns !== ($_SESSION['id_ens'] ?? 0)) {
                EnseignantModel::delete($pdo, $idEns);
                $succes = 'Enseignant supprimé.';
            } else {
                $erreur = 'Impossible de supprimer votre propre compte.';
            }
        }

        // --- CRÉATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $nomEns    = trim($_POST['nom_ens']    ?? '');
            $prenomEns = trim($_POST['prenom_ens'] ?? '');
            $sexe      = $_POST['sexe'] ?? null;

            if ($nomEns === '' || $prenomEns === '') {
                $erreur            = 'Le nom et le prénom sont obligatoires.';
                $reopenCreateForm  = true;
            } else {
                // Vérification doublon (nom + prénom)
                $exist = $pdo->prepare(
                    'SELECT COUNT(*) FROM enseignant WHERE nom_ens = :n AND prenom_ens = :p'
                );
                $exist->execute(['n' => strtoupper($nomEns), 'p' => ucfirst($prenomEns)]);
                if ((int) $exist->fetchColumn() > 0) {
                    $erreur            = 'Cet enseignant existe déjà.';
                    $reopenCreateForm  = true;
                } else {
                    $login = self::genererLogin($nomEns, $prenomEns);
                    // Le mot de passe par défaut est 'declic' (hashé en bcrypt).
                    // L'enseignant sera forcé de le changer à sa première connexion.
                    EnseignantModel::create($pdo, $nomEns, $prenomEns, $sexe, $login, 'declic');
                    $succes = "Enseignant créé. Login : <strong>{$login}</strong>. Mot de passe par défaut : <strong>declic</strong>.";
                }
            }
        }

        // --- MODIFICATION (nom, prénom, sexe) ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update') {
            $idEns     = (int) ($_POST['id_ens']     ?? 0);
            $nomEns    = trim($_POST['nom_ens']    ?? '');
            $prenomEns = trim($_POST['prenom_ens'] ?? '');
            $sexe      = $_POST['sexe'] ?? null;
            $login     = trim($_POST['login']     ?? '');
            $admin     = (int) ($_POST['admin'] ?? 0);

            if ($idEns > 0 && $nomEns !== '' && $prenomEns !== '') {
                EnseignantModel::update($pdo, $idEns, $nomEns, $prenomEns, $sexe, $login, $admin);
                $succes = 'Enseignant modifié.';
            }
        }

        $enseignants = EnseignantModel::findAll($pdo);

        $this->render('enseignant/gest_ens.php', 'Gestion des enseignants', [
            'enseignants'        => $enseignants,
            'erreur'             => $erreur,
            'succes'             => $succes,
            'reopenCreateForm'   => $reopenCreateForm,
        ]);
    }
}
