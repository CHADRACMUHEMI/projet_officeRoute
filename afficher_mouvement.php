<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$recherche_mois = $_GET['mois'] ?? 'SEPTEMBRE 2026';

$stmt = $pdo->prepare("SELECT * FROM mouvement_carburant_dap WHERE mois_rapport LIKE :mois ORDER BY id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cols = ['gasoil', 'essence', 'sae50', 'sae40', 'sae10', 'atf', 'liquide_frein', 'graisse', 'eau_vive', 'anti_gel'];

$stock_debut = array_fill_keys($cols, 0.0);
$tot_entrees = array_fill_keys($cols, 0.0);
$tot_sorties = array_fill_keys($cols, 0.0);
$solde       = array_fill_keys($cols, 0.0);

$lignes_debut   = [];
$lignes_entrees = [];
$lignes_sorties = [];

foreach ($lignes as $row) {
    $cat = $row['categorie'] ?? 'ENTREE';
    if ($cat === 'STOCK_DEBUT') {
        $lignes_debut[] = $row;
        foreach ($cols as $c) $stock_debut[$c] += floatval($row[$c] ?? 0);
    } elseif ($cat === 'ENTREE') {
        $lignes_entrees[] = $row;
        foreach ($cols as $c) $tot_entrees[$c] += floatval($row[$c] ?? 0);
    } else {
        $lignes_sorties[] = $row;
        foreach ($cols as $c) $tot_sorties[$c] += floatval($row[$c] ?? 0);
    }
}

$total_disponible = [];
foreach ($cols as $c) {
    $total_disponible[$c] = $stock_debut[$c] + $tot_entrees[$c];
    $solde[$c] = $total_disponible[$c] - $tot_sorties[$c];
}

function fmt($val) {
    return ($val != 0) ? rtrim(rtrim(number_format($val, 2, ',', ' '), '0'), ',') : '-';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Situation Mouvement Carburant & Lubrifiant</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; padding-left: 6px; }
        .section-title { background: #e9ecef; font-weight: bold; text-align: left; padding-left: 6px; }
        .total-row { font-weight: bold; background: #f4f4f4; }
        .solde-row { font-weight: bold; background: #dfe8f3; }
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
        <a href="ajouter_mouvement.php" style="margin-right:15px;">+ Ajouter un mouvement</a>
        <button onclick="window.print()">Imprimer</button>
    </div>
</div>

<div class="header-doc">
    <h3>RAPPORT MODELE D.A.P</h3>
    <h3>MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h3>
    <h3>SITUATION MOUVEMENT CARBURANT & LUBRIFIANT</h3>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:22%;">PROVENANCE / DESTINATION</th>
            <th rowspan="2">GAS-OIL EN LITRE</th>
            <th rowspan="2">ESSENCE EN LITRE</th>
            <th colspan="8">LUBRIFIANTS EN LITRES</th>
            <th rowspan="2">OBS</th>
        </tr>
        <tr>
            <th>SAE50</th>
            <th>SAE40</th>
            <th>SAE10</th>
            <th>ATF</th>
            <th>LIQUIDE DE FREIN</th>
            <th>GRAISSE</th>
            <th>EAU VIVE</th>
            <th>ANTI-GEL</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="12" class="section-title">STOCK AU DEBUT MOIS</td></tr>
        <?php foreach ($lignes_debut as $r): ?>
        <tr>
            <td class="text-left"><?= htmlspecialchars($r['provenance']) ?></td>
            <?php foreach ($cols as $c): ?><td><?= fmt($r[$c]) ?></td><?php endforeach; ?>
            <td><?= htmlspecialchars($r['obs']) ?></td>
        </tr>
        <?php endforeach; ?>

        <tr><td colspan="12" class="section-title">ENTREE</td></tr>
        <?php foreach ($lignes_entrees as $r): ?>
        <tr>
            <td class="text-left"><?= htmlspecialchars($r['provenance']) ?></td>
            <?php foreach ($cols as $c): ?><td><?= fmt($r[$c]) ?></td><?php endforeach; ?>
            <td><?= htmlspecialchars($r['obs']) ?></td>
        </tr>
        <?php endforeach; ?>

        <tr class="total-row">
            <td class="text-left">TOTAUX (Stock début + Entrées)</td>
            <?php foreach ($cols as $c): ?><td><?= fmt($total_disponible[$c]) ?></td><?php endforeach; ?>
            <td>-</td>
        </tr>

        <tr><td colspan="12" class="section-title">SORTIE OU DESTINATION</td></tr>
        <?php foreach ($lignes_sorties as $r): ?>
        <tr>
            <td class="text-left"><?= htmlspecialchars($r['provenance']) ?></td>
            <?php foreach ($cols as $c): ?><td><?= fmt($r[$c]) ?></td><?php endforeach; ?>
            <td><?= htmlspecialchars($r['obs']) ?></td>
        </tr>
        <?php endforeach; ?>

        <tr class="total-row">
            <td class="text-left">TOTAUX (Sorties)</td>
            <?php foreach ($cols as $c): ?><td><?= fmt($tot_sorties[$c]) ?></td><?php endforeach; ?>
            <td>-</td>
        </tr>

        <tr class="solde-row">
            <td class="text-left">SOLDE</td>
            <?php foreach ($cols as $c): ?><td><?= fmt($solde[$c]) ?></td><?php endforeach; ?>
            <td>-</td>
        </tr>
    </tbody>
</table>

</body>
</html>