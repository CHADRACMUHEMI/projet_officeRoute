<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$recherche_mois = $_GET['mois'] ?? 'SEPTEMBRE 2026';

$stmt = $pdo->prepare("SELECT * FROM synthese_materiels_flottants WHERE mois_rapport LIKE :mois ORDER BY item ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sum_pd = 0;
$sum_pr = 0;
$sum_om = 0;
$sum_actif = 0;
$sum_total = 0;

$titre_situation = 'Situation au 30 septembre 2026';
$titre_centre    = 'CENTRE 480';
$titre_province  = 'PROVINCE DE LA TSHOPO';

if (!empty($lignes)) {
    $titre_situation = $lignes[0]['date_situation'] ?: $titre_situation;
    $titre_centre    = $lignes[0]['centre_gestion'] ?: $titre_centre;
    $titre_province  = $lignes[0]['province'] ?: $titre_province;
}

$lignes_calculees = [];
foreach ($lignes as $r) {
    $pd = intval($r['a_declasser_pd'] ?? 0);
    $pr = intval($r['panne_rep_pr'] ?? 0);
    $om = intval($r['dispo_om'] ?? 0);

    $actif = $pr + $om;
    $total = $actif + $pd;

    $tr  = ($total > 0) ? round(($pd / $total) * 100, 1) : 0;
    $tda = ($actif > 0) ? round(($om / $actif) * 100, 0) : 0;

    $sum_pd    += $pd;
    $sum_pr    += $pr;
    $sum_om    += $om;
    $sum_actif += $actif;
    $sum_total += $total;

    $r['actif'] = $actif;
    $r['total'] = $total;
    $r['tr']    = $tr;
    $r['tda']   = $tda;
    $lignes_calculees[] = $r;
}

$global_tr  = ($sum_total > 0) ? round(($sum_pd / $sum_total) * 100, 1) : 0;
$global_tda = ($sum_actif > 0) ? round(($sum_om / $sum_actif) * 100, 0) : 0;

function fmt_pct($val) {
    return str_replace('.', ',', (string)$val);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau Synthétique des Matériels Flottants</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 14px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 6px 5px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; padding-left: 8px; }
        .total-row { font-weight: bold; background-color: #eaeaea; }
        .no-print { margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: #eee; padding: 10px; border-radius: 5px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
            @page { size: landscape; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <form method="GET" style="margin:0; display:flex; gap:10px; align-items:center;">
        <label><strong>Mois / Année :</strong></label>
        <input type="text" name="mois" value="<?= htmlspecialchars($recherche_mois) ?>" style="padding:5px; width:200px;">
        <button type="submit" style="padding:5px 10px;">Filtrer</button>
    </form>
    <div>
        <a href="ajouter_synthese_flottants.php" style="margin-right:15px;">+ Ajouter une ligne</a>
        <button onclick="window.print()">Imprimer</button>
    </div>
</div>

<div class="header-doc">
    <h3>B. TABLEAU SYNTHETIQUE DES MATERIELS FLOTTANTS A LA GESTION DU <?= htmlspecialchars(strtoupper($titre_centre)) ?></h3>
    <h4>1. BACS DE TRAVERSEE <?= htmlspecialchars(strtoupper($titre_province)) ?></h4>
    <p style="margin:4px 0; font-weight:bold;"><?= htmlspecialchars($titre_situation) ?></p>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:5%;">It.</th>
            <th rowspan="2" style="width:25%;">Désignation</th>
            <th colspan="3">Total parc à flot</th>
            <th colspan="4">Etat du parc actif</th>
        </tr>
        <tr>
            <th>Total</th>
            <th>A Déclasser (PD)</th>
            <th>Tr (%)</th>
            <th>Actif</th>
            <th>Panne Rép (PR)</th>
            <th>Dispo (OM)</th>
            <th>Tda (%)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($lignes_calculees) > 0): ?>
            <?php foreach ($lignes_calculees as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['item'] ?? '') ?></td>
                    <td class="text-left"><?= htmlspecialchars($r['designation'] ?? '') ?></td>
                    <td><strong><?= $r['total'] ?></strong></td>
                    <td><?= $r['a_declasser_pd'] ?></td>
                    <td><?= fmt_pct($r['tr']) ?></td>
                    <td><strong><?= $r['actif'] ?></strong></td>
                    <td><?= $r['panne_rep_pr'] ?></td>
                    <td><?= $r['dispo_om'] ?></td>
                    <td><?= fmt_pct($r['tda']) ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="2" class="text-left">Total Bacs</td>
                <td><?= $sum_total ?></td>
                <td><?= $sum_pd ?></td>
                <td><?= fmt_pct($global_tr) ?></td>
                <td><?= $sum_actif ?></td>
                <td><?= $sum_pr ?></td>
                <td><?= $sum_om ?></td>
                <td><?= fmt_pct($global_tda) ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="9" style="padding:15px;">Aucune donnée trouvée pour ce mois.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>