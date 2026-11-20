<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'consommation';
$periode_affichee = strtoupper($recherche_mois);

$stmt = $pdo->prepare("SELECT * FROM consommation_bacs WHERE mois_rapport LIKE :mois ORDER BY item ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Colonnes numériques à sommer dans le Total-général
$colonnes_totaux = [
    'carb_or',
    'carb_tiers',
    'carb_total',
    'carb_essence',
    'lub_or_1',
    'lub_sae40',
    'lub_or_2',
    'lub_tiers',
    'lub_sae50',
    'lub_sae10',
    'liquide_frein',
    'graisse',
    'atf',
    'eau_vive',
    'antigel',
    'heures_bb',
    'heures_tb',
    'jour_bb',
    'jour_tb'
];

$totaux = array_fill_keys($colonnes_totaux, 0.0);

/**
 * Extrait une valeur numérique depuis une chaîne (ex: "1 610 L" -> 1610, "7Kgs" -> 7)
 * Ignore les mentions textuelles comme "RAS", "PR", "-", etc.
 */
function extraire_nombre($valeur) {
    if ($valeur === null) {
        return 0.0;
    }
    $texte = trim((string)$valeur);
    if ($texte === '' || $texte === '-' || preg_match('/^(ras|pr|n\/a)$/i', $texte)) {
        return 0.0;
    }
    // Supprime les espaces insécables ou normaux entre milliers et remplace la virgule par un point
    $texte = preg_replace('/[\s\x{00A0}]+/u', '', $texte);
    if (preg_match('/([0-9]+(?:[.,][0-9]+)?)/', $texte, $matches)) {
        return floatval(str_replace(',', '.', $matches[1]));
    }
    return 0.0;
}

foreach ($lignes as $row) {
    foreach ($colonnes_totaux as $col) {
        $totaux[$col] += extraire_nombre($row[$col] ?? 0);
    }
}

function formater_total($nombre, $unite = '') {
    if ($nombre == 0) {
        return '-';
    }
    $propre = rtrim(rtrim(number_format($nombre, 2, ',', ' '), '0'), ',');
    return $unite !== '' ? $propre . ' ' . $unite : $propre;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consommation Carburant et Lubrifiants - <?= htmlspecialchars($periode_affichee) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="report-page">
<?php require __DIR__ . '/app_header.php'; ?>

<main class="report-container consumption-container">
    <div class="no-print">
        <form method="GET">
            <label for="mois"><strong>Filtrer (Mois et année) :</strong></label>
            <input id="mois" type="month" name="mois" value="<?= htmlspecialchars($mois_picker) ?>" required>
            <button type="submit">Rechercher</button>
        </form>
        <nav class="site-nav fiche-actions" aria-label="Actions sur la fiche">
            <a href="dupliquer_mois.php?type=consommation&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
            <a href="ajouter_consommation.php?mois=<?= rawurlencode($mois_picker) ?>">+ Ajouter une ligne</a>
            <button class="btn-print" type="button" onclick="window.print()">Imprimer la fiche</button>
        </nav>
    </div>

    <div class="header-doc">
        <h3>RAPPORT MODELE DMB</h3>
        <h3>MOIS DE <?= htmlspecialchars($periode_affichee) ?></h3>
        <h3>CONSOMMATION CARBURANT ET LUBRIFIANTS</h3>
    </div>

<div class="report-table-wrap">
<table class="report-table consumption-table">
    <thead>
        <tr>
            <th rowspan="3" style="width: 3%;">ITEM</th>
            <th rowspan="3" style="width: 6%;">MATRICULE</th>
            <th rowspan="3" style="width: 11%;">NOM DU BAC OU POINT DE PASSAGE</th>
            <th rowspan="3" style="width: 6%;">TYPE DU BAC</th>
            <th colspan="4">ORIGINE ET QUANTITE CARBURANT</th>
            <th colspan="6">QUANTITE DE LUBRIFIANTS MOTEUR</th>
            <th rowspan="3">LIQUIDE DE FREIN</th>
            <th rowspan="3">GRAISSE</th>
            <th rowspan="3">ATF</th>
            <th rowspan="3">EAU VIVE</th>
            <th rowspan="3">ANTIGEL</th>
            <th colspan="2" rowspan="2">HEURES DE MARCHE MOTEUR</th>
            <th colspan="2" rowspan="2">JOUR DE FONCTION MOTEUR</th>
        </tr>
        <tr>
            <th colspan="4">Stock à la fin du mois</th>
            <th colspan="6">0</th>
        </tr>
        <tr>
            <th>OR</th>
            <th>TIERS</th>
            <th>TOTAL</th>
            <th>ESSENCE</th>
            <th>OR</th>
            <th>SAE40</th>
            <th>OR</th>
            <th>TIERS</th>
            <th>SAE 50</th>
            <th>SAE 10</th>
            <th>Bb</th>
            <th>Tb</th>
            <th>Bb</th>
            <th>Tb</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($lignes) > 0): ?>
            <?php foreach ($lignes as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['matricule'] ?? '') ?></td>
                    <td class="text-left"><?= htmlspecialchars($row['nom_bac'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['type_bac'] ?? '') ?></td>

                    <td><?= htmlspecialchars($row['carb_or'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['carb_tiers'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['carb_total'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['carb_essence'] ?: '-') ?></td>

                    <td><?= htmlspecialchars($row['lub_or_1'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['lub_sae40'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['lub_or_2'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['lub_tiers'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['lub_sae50'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['lub_sae10'] ?: '-') ?></td>

                    <td><?= htmlspecialchars($row['liquide_frein'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['graisse'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['atf'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['eau_vive'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['antigel'] ?: '-') ?></td>

                    <td><?= htmlspecialchars($row['heures_bb'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['heures_tb'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['jour_bb'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($row['jour_tb'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="4" style="text-align: right; padding-right: 10px;">Total-général :</td>
                <td><?= formater_total($totaux['carb_or'], 'L') ?></td>
                <td><?= formater_total($totaux['carb_tiers'], 'L') ?></td>
                <td><?= formater_total($totaux['carb_total'], 'L') ?></td>
                <td><?= formater_total($totaux['carb_essence'], 'L') ?></td>

                <td><?= formater_total($totaux['lub_or_1'], 'L') ?></td>
                <td><?= formater_total($totaux['lub_sae40'], 'L') ?></td>
                <td><?= formater_total($totaux['lub_or_2'], 'L') ?></td>
                <td><?= formater_total($totaux['lub_tiers'], 'L') ?></td>
                <td><?= formater_total($totaux['lub_sae50'], 'L') ?></td>
                <td><?= formater_total($totaux['lub_sae10'], 'L') ?></td>

                <td><?= formater_total($totaux['liquide_frein'], 'L') ?></td>
                <td><?= formater_total($totaux['graisse'], 'Kgs') ?></td>
                <td><?= formater_total($totaux['atf'], 'L') ?></td>
                <td><?= formater_total($totaux['eau_vive'], 'L') ?></td>
                <td><?= formater_total($totaux['antigel'], 'L') ?></td>

                <td><?= formater_total($totaux['heures_bb']) ?></td>
                <td><?= formater_total($totaux['heures_tb']) ?></td>
                <td><?= formater_total($totaux['jour_bb']) ?></td>
                <td><?= formater_total($totaux['jour_tb']) ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="23" style="padding: 15px;">Aucune donnée trouvée pour cette recherche.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</main>
</body>
</html>