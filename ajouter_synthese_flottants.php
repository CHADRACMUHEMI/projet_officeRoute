<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

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
        ':mois_rapport'   => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
        ':date_situation' => $_POST['date_situation'] ?? 'Situation au 30 septembre 2026',
        ':centre_gestion' => $_POST['centre_gestion'] ?? 'CENTRE 480',
        ':province'       => $_POST['province'] ?? 'PROVINCE DE LA TSHOPO',
        ':item'           => $_POST['item'] ?? '',
        ':designation'    => $_POST['designation'] ?? '',
        ':a_declasser_pd' => intval($_POST['a_declasser_pd'] ?? 0),
        ':panne_rep_pr'   => intval($_POST['panne_rep_pr'] ?? 0),
        ':dispo_om'       => intval($_POST['dispo_om'] ?? 0)
    ]);

    $message = "Ligne de synthèse enregistrée avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Tableau Synthétique Matériels Flottants</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 820px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 15px; }
        .full { grid-column: span 2; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input { width: 100%; padding: 6px; box-sizing: border-box; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Tableau Synthétique des Matériels Flottants</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label>Mois / Année :</label><input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required></div>
            <div><label>Situation / Date :</label><input type="text" name="date_situation" value="Situation au 30 septembre 2026" required></div>
            <div><label>Centre de gestion :</label><input type="text" name="centre_gestion" value="CENTRE 480" required></div>
            <div><label>Province :</label><input type="text" name="province" value="PROVINCE DE LA TSHOPO" required></div>

            <div><label>Item (It.) :</label><input type="text" name="item" placeholder="Ex: 01"></div>
            <div><label>Désignation :</label><input type="text" name="designation" placeholder="Ex: Bacs à Moteur" required></div>

            <div><label>Dispo (OM) :</label><input type="number" name="dispo_om" value="0" min="0"></div>
            <div><label>Panne Rép (PR) :</label><input type="number" name="panne_rep_pr" value="0" min="0"></div>
            <div class="full"><label>À Déclasser (PD) :</label><input type="number" name="a_declasser_pd" value="0" min="0"></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="afficher_synthese_flottants.php" style="margin-left: 15px;">Voir le tableau synthétique →</a>
    </form>
</div>
</body>
</html>