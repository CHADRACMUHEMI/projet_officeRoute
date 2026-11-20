<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO materiel_appui_bacs (
        mois_rapport, item, categorie, marque_type, numero_or,
        etat_localisation, prop_declasser, prop_renouveller, observ
    ) VALUES (
        :mois_rapport, :item, :categorie, :marque_type, :numero_or,
        :etat_localisation, :prop_declasser, :prop_renouveller, :observ
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'      => report_month_label($mois_rapport),
        ':item'              => $_POST['item'] ?? '',
        ':categorie'         => $_POST['categorie'] ?? '',
        ':marque_type'       => $_POST['marque_type'] ?? '',
        ':numero_or'         => $_POST['numero_or'] ?: '-',
        ':etat_localisation' => $_POST['etat_localisation'] ?: '-',
        ':prop_declasser'    => $_POST['prop_declasser'] ?: '-',
        ':prop_renouveller'  => $_POST['prop_renouveller'] ?: '-',
        ':observ'            => $_POST['observ'] ?: '-'
    ]);

    header('Location: afficher_materiel_appui.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Matériel d'Appui à l'Intervention Bacs</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Matériel d'Appui à l'Intervention Bacs</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid grid-3">
            <div><label for="mois_rapport">Mois / Année :</label><input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required></div>
            <div><label>Item :</label><input type="text" name="item" placeholder="Ex: 01"></div>
            <div><label>N°OR :</label><input type="text" name="numero_or" placeholder="Ex: 131332 ou -"></div>

            <div><label>Catégorie :</label><input type="text" name="categorie" placeholder="Ex: Camion Benne fixe" required></div>
            <div class="full"><label>Marque & Type :</label><input type="text" name="marque_type" placeholder="Ex: Sinotruck"></div>

            <div><label>Proposition - À Déclasser :</label><input type="text" name="prop_declasser" placeholder="Ex: X, 1 ou -" value="-"></div>
            <div><label>Proposition - À Renouveller :</label><input type="text" name="prop_renouveller" placeholder="Ex: X, 1 ou -" value="-"></div>
            <div><label>Observation :</label><input type="text" name="observ" value="-"></div>

            <div class="full"><label>État et Localisation :</label><textarea name="etat_localisation" placeholder="Ex: OM, se trouve au CMR 470"></textarea></div>
        </main>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>