<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = trim($_POST['mois_rapport'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO cout_exploitation_bacs (
        mois_rapport, item, matricule, nom_bac, recette_bac, 
        etat_fin_mois, charge_personnel, charge_entretien, 
        fonctionnement, total_fc, observ
    ) VALUES (
        :mois_rapport, :item, :matricule, :nom_bac, :recette_bac, 
        :etat_fin_mois, :charge_personnel, :charge_entretien, 
        :fonctionnement, :total_fc, :observ
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'     => $mois_rapport,
        ':item'             => $_POST['item'] ?? '',
        ':matricule'        => $_POST['matricule'] ?? '',
        ':nom_bac'          => $_POST['nom_bac'] ?? '',
        ':recette_bac'      => $_POST['recette_bac'] ?: '-',
        ':etat_fin_mois'    => $_POST['etat_fin_mois'] ?: '-',
        ':charge_personnel' => $_POST['charge_personnel'] ?: '-',
        ':charge_entretien' => $_POST['charge_entretien'] ?: '-',
        ':fonctionnement'   => $_POST['fonctionnement'] ?: '-',
        ':total_fc'         => $_POST['total_fc'] ?: '-',
        ':observ'           => $_POST['observ'] ?: 'RAS'
    ]);

    $message = "Coût d'exploitation enregistré avec succès !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Coût d'Exploitation Bacs et Tiers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="entry-page">
<main class="entry-container">
    <nav class="site-nav" aria-label="Navigation des fiches">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="afficher_cout.php">Fiche des coûts</a>
        <a href="afficher_fiche.php">Fiche des travaux</a>
        <a href="afficher_trafic.php">Fiche des trafics</a>
        <a href="afficher_consommation.php">Fiche de consommation</a>
        <a href="ajouter_bac.php">+ Ajouter des travaux</a>
        <a href="ajouter_trafic.php">+ Ajouter un trafic</a>
        <a href="ajouter_consommation.php">+ Ajouter une consommation</a>
    </nav>
    <h2>Saisie - Coût d'Exploitation Bacs et Tiers</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label for="mois_rapport">Mois et année du rapport :</label><input id="mois_rapport" type="text" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" placeholder="Ex: SEPTEMBRE 2026" required></div>
            <div><label for="item">Item :</label><input id="item" type="text" name="item" placeholder="Ex: 01" required></div>
            <div><label for="matricule">Matricule :</label><input id="matricule" type="text" name="matricule" placeholder="Ex: A23597" required></div>
            <div><label for="nom_bac">Nom du Bac / Point de passage :</label><input id="nom_bac" type="text" name="nom_bac" placeholder="Ex: BANALIA" required></div>
            <div><label for="recette_bac">Recette du Bac :</label><input id="recette_bac" type="text" name="recette_bac" value="-"></div>
            <div><label for="etat_fin_mois">État en fin du mois :</label><input id="etat_fin_mois" type="text" name="etat_fin_mois" value="-"></div>
            <div><label for="charge_personnel">Charge du personnel :</label><input id="charge_personnel" type="text" name="charge_personnel" value="-"></div>
            <div><label for="charge_entretien">Charge d'entretien / réparation :</label><input id="charge_entretien" type="text" name="charge_entretien" value="-"></div>
            <div><label for="fonctionnement">Fonctionnement :</label><input id="fonctionnement" type="text" name="fonctionnement" value="-"></div>
            <div><label for="total_fc">Total FC :</label><input id="total_fc" type="text" name="total_fc" value="-"></div>
            <div class="full"><label for="observ">Observation :</label><input id="observ" type="text" name="observ" value="RAS"></div>
        </div>

        <button type="submit">Enregistrer le coût</button>
    </form>
    <p class="link entry-links"><a href="afficher_cout.php?mois=<?= rawurlencode($mois_rapport) ?>">Voir le rapport complet<?= $mois_rapport !== '' ? ' - ' . htmlspecialchars($mois_rapport) : '' ?></a></p>
</main>
</body>
</html>