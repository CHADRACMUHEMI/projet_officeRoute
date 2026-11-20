<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();
$date_situation = $_POST['date_situation'] ?? 'Situation au ' . date('d/m/Y', strtotime($mois_rapport . '-01 +1 month -1 day'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO synthese_materiels_flottants (
        mois_rapport, date_situation, centre_gestion, province,
        item, designation, a_declasser_pd, panne_rep_pr, dispo_om
    ) VALUES (
        :mois_rapport, :date_situation, :centre_gestion, :province,
        :item, :designation, :a_declasser_pd, :panne_rep_pr, :dispo_om
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'   => report_month_label($mois_rapport),
        ':date_situation' => $date_situation,
        ':centre_gestion' => $_POST['centre_gestion'] ?? 'CENTRE 480',
        ':province'       => $_POST['province'] ?? 'PROVINCE DE LA TSHOPO',
        ':item'           => $_POST['item'] ?? '',
        ':designation'    => $_POST['designation'] ?? '',
        ':a_declasser_pd' => intval($_POST['a_declasser_pd'] ?? 0),
        ':panne_rep_pr'   => intval($_POST['panne_rep_pr'] ?? 0),
        ':dispo_om'       => intval($_POST['dispo_om'] ?? 0)
    ]);

    header('Location: afficher_synthese_flottants.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Tableau Synthétique Matériels Flottants</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Tableau Synthétique des Matériels Flottants</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label for="mois_rapport">Mois / Année :</label><input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required></div>
            <div><label for="date_situation">Situation / Date :</label><input id="date_situation" type="text" name="date_situation" value="<?= htmlspecialchars($date_situation) ?>" required></div>
            <div><label>Centre de gestion :</label><input type="text" name="centre_gestion" value="CENTRE 480" required></div>
            <div><label>Province :</label><input type="text" name="province" value="PROVINCE DE LA TSHOPO" required></div>

            <div><label>Item (It.) :</label><input type="text" name="item" placeholder="Ex: 01"></div>
            <div><label>Désignation :</label><input type="text" name="designation" placeholder="Ex: Bacs à Moteur" required></div>

            <div><label>Dispo (OM) :</label><input type="number" name="dispo_om" value="0" min="0"></div>
            <div><label>Panne Rép (PR) :</label><input type="number" name="panne_rep_pr" value="0" min="0"></div>
            <div class="full"><label>À Déclasser (PD) :</label><input type="number" name="a_declasser_pd" value="0" min="0"></div>
        </main>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>