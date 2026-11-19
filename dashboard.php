<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$prenom = htmlspecialchars($_SESSION['prenom']);
$nom = htmlspecialchars($_SESSION['nom']);
$email = htmlspecialchars($_SESSION['email']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Office de Route, matériel flottant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="logo">
            <div class="icon">🚢</div>
            <h1>Office de Route - Matériel flottant</h1>
            <p>Tableau de bord</p>
        </div>

        <h2 class="welcome">Bienvenue, <span><?= $prenom ?> !</span></h2>
        <p class="dashboard-note">Vous êtes connecté à votre espace personnel</p>

        <div class="user-info">
            <p><strong>👤 Nom complet :</strong> <?= $prenom ?> <?= $nom ?></p>
            <p><strong>📧 Email :</strong> <?= $email ?></p>
            <p><strong>📅 Date de connexion :</strong> <?= date('d/m/Y H:i') ?></p>
        </div>

        <nav class="dashboard-links" aria-label="Accès aux fiches">
            <a class="dashboard-card" href="afficher_fiche.php">
                📋 Fiche des travaux
                <span>Consulter les travaux des bacs</span>
            </a>
            <a class="dashboard-card" href="ajouter_bac.php">
                ➕ Ajouter des travaux
                <span>Enregistrer une ligne de travaux</span>
            </a>
            <a class="dashboard-card" href="afficher_trafic.php">
                🚢 Fiche des trafics
                <span>Consulter les mouvements mensuels</span>
            </a>
            <a class="dashboard-card" href="ajouter_trafic.php">
                ➕ Ajouter un trafic
                <span>Enregistrer un mouvement</span>
            </a>
            <a class="dashboard-card" href="afficher_consommation.php">
                ⛽ Consommation carburant
                <span>Consulter carburants et lubrifiants</span>
            </a>
            <a class="dashboard-card" href="ajouter_consommation.php">
                ➕ Ajouter une consommation
                <span>Enregistrer les consommations mensuelles</span>
            </a>
            <a class="dashboard-card" href="afficher_cout.php">
                💰 Coûts d'exploitation
                <span>Consulter les coûts mensuels</span>
            </a>
            <a class="dashboard-card" href="ajouter_cout.php">
                ➕ Ajouter un coût
                <span>Enregistrer les coûts d'exploitation</span>
            </a>
            <a class="dashboard-card" href="dupliquer_mois.php">
                📅 Préparer un nouveau mois
                <span>Reprendre les lignes du mois précédent</span>
            </a>
        </nav>

        <a href="logout.php" class="btn btn-logout">Se déconnecter</a>
    </div>
</body>
</html>