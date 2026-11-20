<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO situation_parc_flottant (
        mois_rapport, centre_gestion, province, groupe_nom, designation,
        a_reformer, actif, panne, disponible, ordre_affichage
    ) VALUES (
        :mois_rapport, :centre_gestion, :province, :groupe_nom, :designation,
        :a_reformer, :actif, :panne, :disponible, :ordre_affichage
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'    => report_month_label($mois_rapport),
        ':centre_gestion'  => $_POST['centre_gestion'] ?? 'CENTRE MATERIELS FLOTTANTS 480',
        ':province'        => $_POST['province'] ?? 'PROVINCE DE LA TSHOPO',
        ':groupe_nom'      => $_POST['groupe_nom'] ?? 'I.-BACS RFA',
        ':designation'     => $_POST['designation'] ?? '',
        ':a_reformer'      => intval($_POST['a_reformer'] ?? 0),
        ':actif'           => intval($_POST['actif'] ?? 0),
        ':panne'           => intval($_POST['panne'] ?? 0),
        ':disponible'      => intval($_POST['disponible'] ?? 0),
        ':ordre_affichage' => intval($_POST['ordre_affichage'] ?? 0)
    ]);

    header('Location: afficher_parc_flottant.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Situation du Parc Matériel Flottant</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Situation du Parc Matériel Flottant</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid grid-3">
            <div><label for="mois_rapport">Mois / Année :</label><input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required></div>
            <div><label>Centre de gestion :</label><input type="text" name="centre_gestion" value="CENTRE MATERIELS FLOTTANTS 480" required></div>
            <div><label>Province :</label><input type="text" name="province" value="PROVINCE DE LA TSHOPO" required></div>

            <div class="full">
                <label>Groupe / Catégorie principale :</label>
                <select name="groupe_nom" required>
                    <option value="I.-BACS RFA">I.-BACS RFA</option>
                    <option value="II.-BACS TRADITIONNEL">II.-BACS TRADITIONNEL</option>
                    <option value="III.-BACS CATAMARAN">III.-BACS CATAMARAN</option>
                    <option value="IV.-BACS AMBIDROMES">IV.-BACS AMBIDROMES</option>
                    <option value="V. BACS UNIFLOTTES">V. BACS UNIFLOTTES</option>
                    <option value="VI. UNITES FLOTTANTES">VI. UNITES FLOTTANTES</option>
                </select>
            </main>

            <div class="full"><label>Désignation (ex. I.1.- BACS de 35T ou VI.2 Canot rapide) :</label><input type="text" name="designation" required></div>

            <div><label>Actif :</label><input type="number" name="actif" value="0" min="0"></div>
            <div><label>Panne :</label><input type="number" name="panne" value="0" min="0"></div>
            <div><label>Disponible :</label><input type="number" name="disponible" value="0" min="0"></div>

            <div><label>À Réformer :</label><input type="number" name="a_reformer" value="0" min="0"></div>
            <div><label>Ordre d'affichage :</label><input type="number" name="ordre_affichage" value="1"></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>