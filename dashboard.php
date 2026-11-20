<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$prenom = htmlspecialchars($_SESSION['prenom']);
$nom = htmlspecialchars($_SESSION['nom']);
$email = htmlspecialchars($_SESSION['email']);
$fiche_courante = 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Office de Route, matériel flottant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page">
<?php require __DIR__ . '/app_header.php'; ?>
    <main class="dashboard-container">
        <section class="dashboard-overview" aria-label="Résumé de votre compte">
            <div class="dashboard-welcome">
                <p class="dashboard-eyebrow">TABLEAU DE BORD</p>
                <h2 class="welcome">Bienvenue, <span><?= $prenom ?> !</span></h2>
                <p class="dashboard-note">Accédez rapidement aux fiches et aux outils de gestion.</p>
            </div>
            <div class="user-info">
                <p><strong>👤 Nom complet :</strong> <?= $prenom ?> <?= $nom ?></p>
                <p><strong>📧 Email :</strong> <?= $email ?></p>
                <p><strong>📅 Date de connexion :</strong> <?= date('d/m/Y H:i') ?></p>
            </div>
        </section>

        <section class="dashboard-access">
            <div class="dashboard-section-heading">
                <h2>Accès aux fiches et outils</h2>
                <p>Choisissez une fiche pour consulter les enregistrements.</p>
            </div>
            <nav class="dashboard-links" aria-label="Accès aux fiches">
                <a class="dashboard-card" href="afficher_fiche.php">
                    📋 Fiche des travaux
                    <span>Consulter les travaux des bacs</span>
                </a>
                <a class="dashboard-card" href="afficher_trafic.php">
                    🚢 Fiche des trafics
                    <span>Consulter les mouvements mensuels</span>
                </a>
                <a class="dashboard-card" href="afficher_consommation.php">
                    ⛽ Consommation carburant
                    <span>Consulter carburants et lubrifiants</span>
                </a>
                <a class="dashboard-card" href="afficher_actualites_bacs.php">
                    📋 Actualités des bacs
                    <span>Consulter l’état et les travaux des bacs</span>
                </a>
                <a class="dashboard-card" href="afficher_commentaires_travaux.php">
                    📋 Commentaires et travaux exécutés
                    <span>Consulter les introductions et commentaires</span>
                </a>
                <a class="dashboard-card" href="afficher_inventaire_atelier.php">
                    📋 Inventaire de l’atelier
                    <span>Consulter les équipements et leur état</span>
                </a>
                <a class="dashboard-card" href="afficher_materiel_appui.php">
                    📋 Matériel d’appui
                    <span>Consulter le matériel d’intervention</span>
                </a>
                <a class="dashboard-card" href="afficher_mouvement.php">
                    📋 Mouvements carburant et lubrifiant
                    <span>Consulter les entrées et sorties</span>
                </a>
                <a class="dashboard-card" href="afficher_mouvement_magasin.php">
                    📋 Mouvements du magasin
                    <span>Consulter les entrées et sorties magasin</span>
                </a>
                <a class="dashboard-card" href="afficher_parc_flottant.php">
                    📋 Parc matériel flottant
                    <span>Consulter la situation du parc flottant</span>
                </a>
                <a class="dashboard-card" href="afficher_situation_parc.php">
                    📋 Situation du parc bacs
                    <span>Consulter les bacs par province</span>
                </a>
                <a class="dashboard-card" href="afficher_synthese_flottants.php">
                    📋 Synthèse des matériels flottants
                    <span>Consulter les statistiques du parc</span>
                </a>
                <a class="dashboard-card" href="afficher_cout.php">
                    💰 Coûts d'exploitation
                    <span>Consulter les coûts mensuels</span>
                </a>
                <a class="dashboard-card dashboard-tool-card" href="dupliquer_mois.php">
                    📅 Préparer un nouveau mois
                    <span>Reprendre les lignes du mois précédent</span>
                </a>
            </nav>
        </section>

        <a href="logout.php" class="btn btn-logout">Se déconnecter</a>
    </main>
<?php require 'footer.php'; ?>
</body>
</html>