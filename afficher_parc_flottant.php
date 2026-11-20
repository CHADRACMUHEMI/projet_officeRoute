<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'parc_flottant';

$stmt = $pdo->prepare("SELECT * FROM situation_parc_flottant WHERE mois_rapport LIKE :mois ORDER BY ordre_affichage ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function calculer_ratios($total, $a_reformer, $actif, $disponible) {
    $tr = ($total > 0) ? round(($a_reformer / $total) * 100, 1) : 0;
    $td = ($actif > 0) ? round(($disponible / $actif) * 100, 1) : 0;
    return [$tr, $td];
}

function fmt_pct($val) {
    return str_replace('.', ',', (string)$val) . '%';
}

$groupes = [];
foreach ($lignes as $r) {
    $g = $r['groupe_nom'] ?: 'AUTRES';
    if (!isset($groupes[$g])) {
        $groupes[$g] = [];
    }
    $groupes[$g][] = $r;
}

$bacs_total = 0; $bacs_ref = 0; $bacs_actif = 0; $bacs_panne = 0; $bacs_dispo = 0;
$fluv_total = 0; $fluv_ref = 0; $fluv_actif = 0; $fluv_panne = 0; $fluv_dispo = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>II. Situation du Parc Matériel Flottant</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 12px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; padding-left: 7px; }
        .group-row td { background: #e9ecef; font-weight: bold; text-align: left; }
        .subtotal-row td { background: #f5f5f5; font-weight: bold; }
        .ensemble-row td { background: #dce6f1; font-weight: bold; }
        .grand-total-row td { background: #cfdbe8; font-weight: bold; font-size: 11px; }
        .no-print { margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: #eee; padding: 10px; border-radius: 5px; }
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
        <a href="dupliquer_mois.php?type=parc_flottant&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_parc_flottant.php?mois=<?= rawurlencode($mois_picker) ?>" style="margin-right:15px;">+ Ajouter une ligne</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer</button>
    </nav>
</div>

<div class="header-doc">
    <h3>II. SITUATION DU PARC MATERIEL FLOTTANT</h3>
    <h4>MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:32%;">DESIGNATION</th>
            <th colspan="3">INVENTAIRE DU PARC</th>
            <th colspan="4">ETAT DU PARC</th>
        </tr>
        <tr>
            <th>TOTAL</th>
            <th>A REFORMER</th>
            <th>TR.</th>
            <th>ACTIF</th>
            <th>PANNE</th>
            <th>DISPONIBLE</th>
            <th>TD.</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $ensemble_bacs_affiche = false;

        foreach ($groupes as $nom_groupe => $items):
            $is_fluvial = (stripos($nom_groupe, 'FLUVIAL') !== false || stripos($nom_groupe, 'VI.') !== false || stripos($nom_groupe, 'VI ') !== false);

            if ($is_fluvial && !$ensemble_bacs_affiche):
                [$eb_tr, $eb_td] = calculer_ratios($bacs_total, $bacs_ref, $bacs_actif, $bacs_dispo);
        ?>
                <tr class="ensemble-row">
                    <td class="text-left">ENSEMBLE PARC BACS</td>
                    <td><?= $bacs_total ?></td>
                    <td><?= $bacs_ref ?></td>
                    <td><?= fmt_pct($eb_tr) ?></td>
                    <td><?= $bacs_actif ?></td>
                    <td><?= $bacs_panne ?></td>
                    <td><?= $bacs_dispo ?></td>
                    <td><?= fmt_pct($eb_td) ?></td>
                </tr>
        <?php
                $ensemble_bacs_affiche = true;
            endif;

            $sub_total = 0; $sub_ref = 0; $sub_actif = 0; $sub_panne = 0; $sub_dispo = 0;
        ?>
            <tr class="group-row">
                <td colspan="8"><?= htmlspecialchars($nom_groupe) ?></td>
            </tr>
            <?php foreach ($items as $r):
                $ref   = intval($r['a_reformer'] ?? 0);
                $actif = intval($r['actif'] ?? 0);
                $panne = intval($r['panne'] ?? 0);
                $dispo = intval($r['disponible'] ?? 0);
                $total = $actif + $ref;

                [$tr, $td] = calculer_ratios($total, $ref, $actif, $dispo);

                $sub_total += $total; $sub_ref += $ref; $sub_actif += $actif; $sub_panne += $panne; $sub_dispo += $dispo;
                if ($is_fluvial) {
                    $fluv_total += $total; $fluv_ref += $ref; $fluv_actif += $actif; $fluv_panne += $panne; $fluv_dispo += $dispo;
                } else {
                    $bacs_total += $total; $bacs_ref += $ref; $bacs_actif += $actif; $bacs_panne += $panne; $bacs_dispo += $dispo;
                }
            ?>
                <tr>
                    <td class="text-left"><?= htmlspecialchars($r['designation'] ?? '') ?></td>
                    <td><strong><?= $total ?></strong></td>
                    <td><?= $ref ?></td>
                    <td><?= fmt_pct($tr) ?></td>
                    <td><strong><?= $actif ?></strong></td>
                    <td><?= $panne ?></td>
                    <td><?= $dispo ?></td>
                    <td><?= fmt_pct($td) ?></td>
                </tr>
            <?php endforeach;

            [$st_tr, $st_td] = calculer_ratios($sub_total, $sub_ref, $sub_actif, $sub_dispo);
            $libelle_st = $is_fluvial ? 'TOTAL UNITES FLUVIALES' : 'SOUS TOTAL';
            ?>
            <tr class="subtotal-row">
                <td class="text-left"><?= $libelle_st ?></td>
                <td><?= $sub_total ?></td>
                <td><?= $sub_ref ?></td>
                <td><?= fmt_pct($st_tr) ?></td>
                <td><?= $sub_actif ?></td>
                <td><?= $sub_panne ?></td>
                <td><?= $sub_dispo ?></td>
                <td><?= fmt_pct($st_td) ?></td>
            </tr>
        <?php endforeach;

        if (!$ensemble_bacs_affiche):
            [$eb_tr, $eb_td] = calculer_ratios($bacs_total, $bacs_ref, $bacs_actif, $bacs_dispo);
        ?>
            <tr class="ensemble-row">
                <td class="text-left">ENSEMBLE PARC BACS</td>
                <td><?= $bacs_total ?></td>
                <td><?= $bacs_ref ?></td>
                <td><?= fmt_pct($eb_tr) ?></td>
                <td><?= $bacs_actif ?></td>
                <td><?= $bacs_panne ?></td>
                <td><?= $bacs_dispo ?></td>
                <td><?= fmt_pct($eb_td) ?></td>
            </tr>
        <?php endif;

        $gt_total = $bacs_total + $fluv_total;
        $gt_ref   = $bacs_ref + $fluv_ref;
        $gt_actif = $bacs_actif + $fluv_actif;
        $gt_panne = $bacs_panne + $fluv_panne;
        $gt_dispo = $bacs_dispo + $fluv_dispo;
        [$gt_tr, $gt_td] = calculer_ratios($gt_total, $gt_ref, $gt_actif, $gt_dispo);
        ?>
        <tr class="grand-total-row">
            <td class="text-left">TOTAL GENERAL PARC FLOTTANT</td>
            <td><?= $gt_total ?></td>
            <td><?= $gt_ref ?></td>
            <td><?= fmt_pct($gt_tr) ?></td>
            <td><?= $gt_actif ?></td>
            <td><?= $gt_panne ?></td>
            <td><?= $gt_dispo ?></td>
            <td><?= fmt_pct($gt_td) ?></td>
        </tr>
    </tbody>
</table>

<?php require 'footer.php'; ?>
</body>
</html>