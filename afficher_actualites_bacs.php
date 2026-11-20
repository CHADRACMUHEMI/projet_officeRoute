<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'actualites';

$stmt = $pdo->prepare("SELECT * FROM actualites_bacs WHERE mois_rapport LIKE :mois ORDER BY categorie_bac ASC, item ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$groupes = [];
$total_general = 0;

foreach ($lignes as $r) {
    $cat = $r['categorie_bac'] ?: 'AUTRES';
    $groupes[$cat][] = $r;
    $total_general++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Actualités Bacs - Centre Matériels Flottants 480</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
        .cat-row td { background: #e6e6e6; font-weight: bold; text-align: left; }
        .sub-row td { background: #f5f5f5; font-weight: bold; text-align: right; }
        .total-row td { background: #dce6f1; font-weight: bold; text-align: right; font-size: 11px; }
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
        <a href="dupliquer_mois.php?type=actualites&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_actualites_bacs.php?mois=<?= rawurlencode($mois_picker) ?>" style="margin-right:15px;">+ Ajouter un bac</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer</button>
    </nav>
</div>

<div class="header-doc">
    <h3>ACTUALITES BACS - CENTRE MATERIELS FLOTTANTS 480</h3>
    <h4>MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:4%;">IT.</th>
            <th rowspan="2" style="width:5%;">CODE</th>
            <th rowspan="2" style="width:7%;">MATR.</th>
            <th rowspan="2" style="width:10%;">NOM DU BAC</th>
            <th rowspan="2" style="width:11%;">NOMBRE MOTEUR ET MARQUE</th>
            <th colspan="2">ETAT</th>
            <th rowspan="2" style="width:8%;">TYPE ET TONNAGE</th>
            <th rowspan="2" style="width:18%;">DESCRIPTION DES PANNES</th>
            <th rowspan="2" style="width:18%;">TRAVAUX A EXECUTER</th>
            <th rowspan="2" style="width:13%;">OBSERVATION</th>
        </tr>
        <tr>
            <th style="width:3%;">B</th>
            <th style="width:3%;">M</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($total_general > 0): ?>
            <?php foreach ($groupes as $cat => $items): ?>
                <tr class="cat-row">
                    <td colspan="11"><?= htmlspecialchars($cat) ?></td>
                </tr>
                <?php foreach ($items as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['item'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['code_point'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['matricule'] ?? '') ?></td>
                        <td class="text-left"><strong><?= htmlspecialchars($r['nom_bac'] ?? '') ?></strong></td>
                        <td class="text-left"><?= htmlspecialchars($r['moteur_marque'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['etat_b'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['etat_m'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($r['type_tonnage'] ?? '') ?></td>
                        <td class="text-left"><?= nl2br(htmlspecialchars($r['description_pannes'] ?? '-')) ?></td>
                        <td class="text-left"><?= nl2br(htmlspecialchars($r['travaux_executer'] ?? '-')) ?></td>
                        <td class="text-left"><?= nl2br(htmlspecialchars($r['observation'] ?? 'RAS')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="sub-row">
                    <td colspan="11">Total <?= htmlspecialchars($cat) ?> : <?= count($items) ?> bac(s)</td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="11">TOTAL GENERAL BACS : <?= $total_general ?> bac(s)</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="11" style="padding:15px;">Aucune donnée trouvée pour cette période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require 'footer.php'; ?>
</body>
</html>