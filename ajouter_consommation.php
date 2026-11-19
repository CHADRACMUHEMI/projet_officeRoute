<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = trim($_POST['mois_rapport'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO consommation_bacs (
        mois_rapport, item, matricule, nom_bac, type_bac,
        carb_or, carb_tiers, carb_total, carb_essence,
        lub_or_1, lub_sae40, lub_or_2, lub_tiers, lub_sae50, lub_sae10,
        liquide_frein, graisse, atf, eau_vive, antigel,
        heures_bb, heures_tb, jour_bb, jour_tb
    ) VALUES (
        :mois_rapport, :item, :matricule, :nom_bac, :type_bac,
        :carb_or, :carb_tiers, :carb_total, :carb_essence,
        :lub_or_1, :lub_sae40, :lub_or_2, :lub_tiers, :lub_sae50, :lub_sae10,
        :liquide_frein, :graisse, :atf, :eau_vive, :antigel,
        :heures_bb, :heures_tb, :jour_bb, :jour_tb
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'  => $mois_rapport,
        ':item'          => $_POST['item'] ?? '',
        ':matricule'     => $_POST['matricule'] ?? '',
        ':nom_bac'       => $_POST['nom_bac'] ?? '',
        ':type_bac'      => $_POST['type_bac'] ?? '',
        ':carb_or'       => $_POST['carb_or'] ?: '-',
        ':carb_tiers'    => $_POST['carb_tiers'] ?: '-',
        ':carb_total'    => $_POST['carb_total'] ?: '-',
        ':carb_essence'  => $_POST['carb_essence'] ?: '-',
        ':lub_or_1'      => $_POST['lub_or_1'] ?: '-',
        ':lub_sae40'     => $_POST['lub_sae40'] ?: '-',
        ':lub_or_2'      => $_POST['lub_or_2'] ?: '-',
        ':lub_tiers'     => $_POST['lub_tiers'] ?: '-',
        ':lub_sae50'     => $_POST['lub_sae50'] ?: '-',
        ':lub_sae10'     => $_POST['lub_sae10'] ?: '-',
        ':liquide_frein' => $_POST['liquide_frein'] ?: '-',
        ':graisse'       => $_POST['graisse'] ?: '-',
        ':atf'           => $_POST['atf'] ?: '-',
        ':eau_vive'      => $_POST['eau_vive'] ?: '-',
        ':antigel'       => $_POST['antigel'] ?: '-',
        ':heures_bb'     => $_POST['heures_bb'] ?: '-',
        ':heures_tb'     => $_POST['heures_tb'] ?: '-',
        ':jour_bb'       => $_POST['jour_bb'] ?: '-',
        ':jour_tb'       => $_POST['jour_tb'] ?: '-'
    ]);

    $message = "Consommation enregistrée avec succès !";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Consommation Carburant et Lubrifiants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="entry-page">
<main class="entry-container consumption-container">
    <nav class="site-nav" aria-label="Navigation des fiches">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="afficher_consommation.php">Fiche de consommation</a>
        <a href="afficher_fiche.php">Fiche des travaux</a>
        <a href="afficher_trafic.php">Fiche des trafics</a>
        <a href="ajouter_bac.php">+ Ajouter des travaux</a>
        <a href="ajouter_trafic.php">+ Ajouter un trafic</a>
    </nav>
    <h2>Saisie - Consommation Carburant et Lubrifiants</h2>
    <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label for="mois_rapport">Mois et année du rapport :</label><input id="mois_rapport" type="text" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" placeholder="Ex: SEPTEMBRE 2026" required></div>
            <div><label>Item :</label><input type="text" name="item" placeholder="Ex: 01"></div>
            <div><label>Matricule :</label><input type="text" name="matricule" placeholder="Ex: A23597"></div>
            <div><label>Nom du Bac :</label><input type="text" name="nom_bac" placeholder="Ex: BANALIA"></div>
            <div><label>Type du Bac :</label><input type="text" name="type_bac" placeholder="Ex: RFA/M"></div>
        </div>

        <h3>Origine et Quantité Carburant</h3>
        <div class="grid">
            <div><label>OR :</label><input type="text" name="carb_or" value="-"></div>
            <div><label>TIERS :</label><input type="text" name="carb_tiers" value="-"></div>
            <div><label>TOTAL :</label><input type="text" name="carb_total" value="-"></div>
            <div><label>ESSENCE :</label><input type="text" name="carb_essence" value="-"></div>
        </div>

        <h3>Quantité de Lubrifiants Moteur & Autres</h3>
        <div class="grid">
            <div><label>OR (1) :</label><input type="text" name="lub_or_1" value="-"></div>
            <div><label>SAE 40 :</label><input type="text" name="lub_sae40" value="-"></div>
            <div><label>OR (2) :</label><input type="text" name="lub_or_2" value="-"></div>
            <div><label>TIERS :</label><input type="text" name="lub_tiers" value="-"></div>
            <div><label>SAE 50 :</label><input type="text" name="lub_sae50" value="-"></div>
            <div><label>SAE 10 :</label><input type="text" name="lub_sae10" value="-"></div>
            <div><label>Liquide de Frein :</label><input type="text" name="liquide_frein" value="-"></div>
            <div><label>Graisse :</label><input type="text" name="graisse" value="-"></div>
            <div><label>ATF :</label><input type="text" name="atf" value="-"></div>
            <div><label>Eau Vive :</label><input type="text" name="eau_vive" value="-"></div>
            <div><label>Antigel :</label><input type="text" name="antigel" value="-"></div>
        </div>

        <h3>Heures de Marche & Jours Fonction Moteur</h3>
        <div class="grid">
            <div><label>Heures Marche Bb :</label><input type="text" name="heures_bb" value="-"></div>
            <div><label>Heures Marche Tb :</label><input type="text" name="heures_tb" value="-"></div>
            <div><label>Jour Fonction Bb :</label><input type="text" name="jour_bb" value="-"></div>
            <div><label>Jour Fonction Tb :</label><input type="text" name="jour_tb" value="-"></div>
        </div>

        <button type="submit">Enregistrer la consommation</button>
        <p class="link entry-links"><a href="afficher_consommation.php?mois=<?= rawurlencode($mois_rapport) ?>">Voir le rapport complet<?= $mois_rapport !== '' ? ' - ' . htmlspecialchars($mois_rapport) : '' ?></a></p>
    </form>
</main>
</body>
</html>