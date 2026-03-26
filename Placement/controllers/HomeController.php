<?php
require_once __DIR__ . '/Controller.php';

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil.
     */
    public function index(): void
    {
        $this->render('home/home.php', 'Accueil — Placement IUT');
    }
}
