<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$recherche_mois = $_GET['mois'] ?? 'SEPTEMBRE 2026';

$stmt = $pdo->prepare("SELECT * FROM inventaire_materiel_atelier WHERE mois_rapport LIKE :mois ORDER BY ordre_affichage ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function calculer_ratios_atelier($total, $reformer, $actif, $disponible) {
    $tr = ($total > 0) ? round(($reformer / $total) * 100, 1) : 0;
    $td = ($actif > 0) ? round(($disponible / $actif) * 100, 1) : 0;
    return [$tr, $td];
}

function fmt_pct_atelier($val) {
    return str_replace('.', ',', (string)$val) . '%';
}

$familles = [];
foreach ($lignes as $r) {
    $f = $r['famille_nom'] ?: 'AUTRES EQUIPEMENTS';
    $familles[$f][] = $r;
}

$gt_total = 0; $gt_ref = 0; $gt_actif = 0; $gt_panne = 0; $gt_dispo = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inventaire du Parc - Famille de Matériel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 12px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; padding-left: 7px; }
        .fam-row td { background: #e9ecef; font-weight: bold; text-align: left; }
        .total-fam td { background: #f5f5f5; font-weight: bold; }
        .grand-total td { background: #dce6f1; font-weight: bold; font-size: 11px; }
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
        <a href="ajouter_inventaire_atelier.php" style="margin-right:15px;">+ Ajouter un matériel</a>
        <button onclick="window.print()">Imprimer</button>
    </div>
</div>

<div class="header-doc">
    <h3>INVENTAIRE ET ETAT DU PARC MATERIEL D'ATELIER</h3>
    <h4>MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:32%;">FAMILLE DE MATERIEL / DESIGNATION</th>
            <th colspan="3">INVENTAIRE DU PARC</th>
            <th colspan="4">ETAT DU PARC</th>
        </tr>
        <tr>
            <th>TOTAL</th>
            <th>REFORMER</th>
            <th>TR.</th>
            <th>ACTIF</th>
            <th>PANNE</th>
            <th>DISPONIBLE</th>
            <th>TD.</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($familles) > 0): ?>
            <?php foreach ($familles as $nom_famille => $items):
                $f_total = 0; $f_ref = 0; $f_actif = 0; $f_panne = 0; $f_dispo = 0;
            ?>
                <tr class="fam-row">
                    <td colspan="8"><?= htmlspecialchars($nom_famille) ?></td>
                </tr>
                <?php foreach ($items as $r):
                    $ref   = intval($r['a_reformer'] ?? 0);
                    $actif = intval($r['actif'] ?? 0);
                    $panne = intval($r['panne'] ?? 0);
                    $dispo = intval($r['disponible'] ?? 0);
                    $total = $actif + $ref;

                    [$tr, $td] = calculer_ratios_atelier($total, $ref, $actif, $dispo);

                    $f_total += $total; $f_ref += $ref; $f_actif += $actif; $f_panne += $panne; $f_dispo += $dispo;
                ?>
                    <tr>
                        <td class="text-left"><?= htmlspecialchars($r['designation'] ?? '') ?></td>
                        <td><strong><?= $total ?></strong></td>
                        <td><?= $ref ?: '-' ?></td>
                        <td><?= fmt_pct_atelier($tr) ?></td>
                        <td><strong><?= $actif ?></strong></td>
                        <td><?= $panne ?: '-' ?></td>
                        <td><?= $dispo ?></td>
                        <td><?= fmt_pct_atelier($td) ?></td>
                    </tr>
                <?php endforeach;

                [$ft_tr, $ft_td] = calculer_ratios_atelier($f_total, $f_ref, $f_actif, $f_dispo);
                $gt_total += $f_total; $gt_ref += $f_ref; $gt_actif += $f_actif; $gt_panne += $f_panne; $gt_dispo += $f_dispo;
                ?>
                <tr class="total-fam">
                    <td class="text-left">TOTAL (<?= htmlspecialchars($nom_famille) ?>)</td>
                    <td><?= $f_total ?></td>
                    <td><?= $f_ref ?: '-' ?></td>
                    <td><?= fmt_pct_atelier($ft_tr) ?></td>
                    <td><?= $f_actif ?></td>
                    <td><?= $f_panne ?></td>
                    <td><?= $f_dispo ?></td>
                    <td><?= fmt_pct_atelier($ft_td) ?></td>
                </tr>
            <?php endforeach;

            [$gt_tr, $gt_td] = calculer_ratios_atelier($gt_total, $gt_ref, $gt_actif, $gt_dispo);
            ?>
            <tr class="grand-total">
                <td class="text-left">TOTAL GENERAL</td>
                <td><?= $gt_total ?></td>
                <td><?= $gt_ref ?: '-' ?></td>
                <td><?= fmt_pct_atelier($gt_tr) ?></td>
                <td><?= $gt_actif ?></td>
                <td><?= $gt_panne ?></td>
                <td><?= $gt_dispo ?></td>
                <td><?= fmt_pct_atelier($gt_td) ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="8" style="padding:15px;">Aucune donnée trouvée pour ce mois.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>