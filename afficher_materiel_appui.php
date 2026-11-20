<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'materiel_appui';

$stmt = $pdo->prepare("SELECT * FROM materiel_appui_bacs WHERE mois_rapport LIKE :mois ORDER BY CAST(item AS UNSIGNED) ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function compter_marquage($val) {
    if (!$val) return 0;
    $v = trim((string)$val);
    if ($v === '' || $v === '-' || $v === '0') return 0;
    if (is_numeric(str_replace(',', '.', $v))) {
        return floatval(str_replace(',', '.', $v));
    }
    // Si une marque comme "X" ou "OK" est saisie, compte 1 unité
    return 1;
}

$total_materiels = count($lignes);
$total_declasser = 0;
$total_renouveller = 0;
$total_om = 0;
$total_pd = 0;

foreach ($lignes as $r) {
    $total_declasser   += compter_marquage($r['prop_declasser'] ?? '');
    $total_renouveller += compter_marquage($r['prop_renouveller'] ?? '');

    $etat = strtoupper(trim((string)($r['etat_localisation'] ?? '')));
    if (preg_match('/\bOM\b/', $etat)) {
        $total_om++;
    }
    if (preg_match('/\bPD\b/', $etat)) {
        $total_pd++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Matériel d'Appui à l'Intervention Bacs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 12px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; padding-left: 6px; }
        .total-row td { font-weight: bold; background-color: #eaeaea; }
        .no-print { margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: #eee; padding: 10px; border-radius: 5px; flex-wrap: wrap; gap: 10px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            @page { size: landscape; }
        }
    </style>
</head>
<body class="report-page">
<?php require __DIR__ . '/app_header.php'; ?>

<div class="no-print">
    <form method="GET" style="margin:0; display:flex; gap:10px; align-items:center;">
        <label><strong>Mois / Année :</strong></label>
        <input type="month" name="mois" value="<?= htmlspecialchars($mois_picker) ?>" required>
        <button type="submit" style="padding:5px 10px;">Filtrer</button>
    </form>
    <nav class="site-nav fiche-actions" aria-label="Actions sur la fiche">
        <a href="dupliquer_mois.php?type=materiel_appui&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_materiel_appui.php?mois=<?= rawurlencode($mois_picker) ?>" style="margin-right:15px;">+ Ajouter un matériel</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer</button>
    </nav>
</div>

<div class="header-doc">
    <h3>MATERIEL D'APPUI A L'INTERVENTION BACS</h3>
    <h4>MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:4%;">Item</th>
            <th colspan="3">IDENTIFICATION DU MATERIEL</th>
            <th rowspan="2" style="width:34%;">ETAT ET LOCALISATION</th>
            <th colspan="2">PROPOSITION</th>
            <th rowspan="2" style="width:12%;">OBSRV.</th>
        </tr>
        <tr>
            <th style="width:12%;">CATEGORIE</th>
            <th style="width:14%;">MARQUE & TYPE</th>
            <th style="width:8%;">N°OR</th>
            <th style="width:7%;">A DECLASSER</th>
            <th style="width:7%;">A RENOUVELLER</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($total_materiels > 0): ?>
            <?php foreach ($lignes as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['item'] ?? '') ?></td>
                    <td class="text-left"><?= htmlspecialchars($r['categorie'] ?? '') ?></td>
                    <td class="text-left"><?= htmlspecialchars($r['marque_type'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['numero_or'] ?? '-') ?></td>
                    <td class="text-left"><?= nl2br(htmlspecialchars($r['etat_localisation'] ?? '-')) ?></td>
                    <td><?= htmlspecialchars($r['prop_declasser'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['prop_renouveller'] ?? '-') ?></td>
                    <td class="text-left"><?= nl2br(htmlspecialchars($r['observ'] ?? '-')) ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="4" style="text-align:right; padding-right:8px;">Totaux (<?= $total_materiels ?> matériels) :</td>
                <td class="text-left">Mention OM : <?= $total_om ?> | Mention PD : <?= $total_pd ?></td>
                <td><?= $total_declasser ?: '-' ?></td>
                <td><?= $total_renouveller ?: '-' ?></td>
                <td>-</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="8" style="padding:15px;">Aucun matériel d'appui enregistré pour cette période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require 'footer.php'; ?>
</body>
</html>