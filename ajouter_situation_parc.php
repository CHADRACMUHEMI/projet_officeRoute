<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO situation_parc_bacs (
        mois_rapport, province, section_etat, numero_ordre, code_point,
        point_passage, matricule, type_capacite, annee_fab,
        axe_routier, type_axe, riviere, territoire, observation
    ) VALUES (
        :mois_rapport, :province, :section_etat, :numero_ordre, :code_point,
        :point_passage, :matricule, :type_capacite, :annee_fab,
        :axe_routier, :type_axe, :riviere, :territoire, :observation
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'  => report_month_label($mois_rapport),
        ':province'      => $_POST['province'] ?? 'PROVINCE DE LA TSHOPO',
        ':section_etat'  => $_POST['section_etat'] ?? '1.1. BACS EN ORDRE DE MARCHE',
        ':numero_ordre'  => $_POST['numero_ordre'] ?? '',
        ':code_point'    => $_POST['code_point'] ?? '',
        ':point_passage' => $_POST['point_passage'] ?? '',
        ':matricule'     => $_POST['matricule'] ?? '',
        ':type_capacite' => $_POST['type_capacite'] ?? '',
        ':annee_fab'     => $_POST['annee_fab'] ?: '-',
        ':axe_routier'   => $_POST['axe_routier'] ?? '',
        ':type_axe'      => $_POST['type_axe'] ?? '',
        ':riviere'       => $_POST['riviere'] ?? '',
        ':territoire'    => $_POST['territoire'] ?? '',
        ':observation'   => $_POST['observation'] ?: '-'
    ]);

    header('Location: afficher_situation_parc.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Situation du Parc Matériel Bacs</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Situation du Parc Matériel Bacs et Unités Fluviales</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid grid-3">
            <div><label for="mois_rapport">Mois / Année :</label><input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required></div>
            <div><label>Province :</label><input type="text" name="province" value="PROVINCE DE LA TSHOPO" required></div>
            <div>
                <label>Catégorie / Section :</label>
                <select name="section_etat" required>
                    <option value="1.1. BACS EN ORDRE DE MARCHE">1.1. BACS EN ORDRE DE MARCHE</option>
                    <option value="1.2. BACS EN PANNE REPARABLE">1.2. BACS EN PANNE REPARABLE</option>
                    <option value="1.3. BACS EN PANNE IRREPARABLE">1.3. BACS EN PANNE IRREPARABLE</option>
                </select>
            </main>

            <div><label>N° (Item) :</label><input type="text" name="numero_ordre" placeholder="Ex: 01 ou 09"></div>
            <div><label>N° Code :</label><input type="text" name="code_point" placeholder="Ex: 4/1"></div>
            <div><label>Point de passage :</label><input type="text" name="point_passage" placeholder="Ex: KISANGANI III" required></div>

            <div><label>Matricule :</label><input type="text" name="matricule" placeholder="Ex: A23678"></div>
            <div><label>Type - Mode de propul. et capacité :</label><input type="text" name="type_capacite" placeholder="Ex: AMB-M-35T."></div>
            <div><label>Ans :</label><input type="text" name="annee_fab" placeholder="Ex: 2008 ou -"></div>

            <div><label>Axe routier :</label><input type="text" name="axe_routier" placeholder="Ex: KISANGANI - OPALA"></div>
            <div><label>Type d'axe :</label><input type="text" name="type_axe" placeholder="Ex: RN, RP1, RR1, RIL"></div>
            <div><label>Rivière :</label><input type="text" name="riviere" placeholder="Ex: FLEUVE CONGO"></div>

            <div><label>Territoire :</label><input type="text" name="territoire" placeholder="Ex: MAKISO"></div>
            <div class="full"><label>Observation :</label><textarea name="observation" placeholder="État technique, pannes, pièces nécessaires..."></textarea></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>