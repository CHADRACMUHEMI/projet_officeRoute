<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'situation_parc';
$recherche_prov = $_GET['province'] ?? 'TSHOPO';

$sql = "SELECT * FROM situation_parc_bacs 
        WHERE mois_rapport LIKE :mois 
          AND province LIKE :prov 
        ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':mois' => '%' . trim($recherche_mois) . '%',
    ':prov' => '%' . trim($recherche_prov) . '%'
]);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement par section (1.1, 1.2, etc.) pour calculer les totaux par catégorie
$groupes = [];
$total_general = 0;

foreach ($lignes as $row) {
    $sec = $row['section_etat'] ?: 'AUTRES BACS';
    if (!isset($groupes[$sec])) {
        $groupes[$sec] = [];
    }
    $groupes[$sec][] = $row;
    $total_general++;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Situation du Parc Matériel Bacs et Unités Fluviales</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 5px 4px; text-align: center; vertical-align: top; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
        .section-row td { background: #e6e6e6; font-weight: bold; text-align: left; padding: 6px; }
        .subtotal-row td { background: #f5f5f5; font-weight: bold; text-align: right; }
        .total-row td { background: #dce6f1; font-weight: bold; text-align: right; font-size: 11px; }
        .no-print { margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: #eee; padding: 10px; border-radius: 5px; flex-wrap: wrap; gap: 8px; }
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
    <form method="GET" style="margin:0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <label><strong>Mois / Année :</strong></label>
        <input type="month" name="mois" value="<?= htmlspecialchars($mois_picker) ?>" required>
        <label><strong>Province :</strong></label>
        <input type="text" name="province" value="<?= htmlspecialchars($recherche_prov) ?>" style="padding:5px; width:170px;">
        <button type="submit" style="padding:5px 10px;">Filtrer</button>
    </form>
    <nav class="site-nav fiche-actions" aria-label="Actions sur la fiche">
        <a href="dupliquer_mois.php?type=situation_parc&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_situation_parc.php?mois=<?= rawurlencode($mois_picker) ?>" style="margin-right:15px;">+ Ajouter un bac</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer</button>
    </nav>
</div>

<div class="header-doc">
    <h3>SITUATION DU PARCS MATERIEL BACS ET UNITES FLUVIALES</h3>
    <h4><?= htmlspecialchars(strtoupper($recherche_prov)) ?> — <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
</div>

<table>
    <thead>
        <tr>
            <th style="width:4%;">N°</th>
            <th style="width:5%;">N° CODE</th>
            <th style="width:11%;">POINT DE PASSAGE</th>
            <th style="width:7%;">MATR.</th>
            <th style="width:9%;">TYPE-MODE DE PROPUL. ET CAPACITE</th>
            <th style="width:5%;">ANS</th>
            <th style="width:11%;">AXE ROUTIER</th>
            <th style="width:5%;">TYPE D'AXE</th>
            <th style="width:8%;">RIVIERE</th>
            <th style="width:8%;">TERRITOIRE</th>
            <th style="width:26%;">OBSERVATION</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($total_general > 0): ?>
            <?php foreach ($groupes as $nom_section => $items): ?>
                <tr class="section-row">
                    <td colspan="11"><?= htmlspecialchars($nom_section) ?></td>
                </tr>
                <?php foreach ($items as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['numero_ordre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['code_point'] ?? '') ?></td>
                        <td class="text-left"><strong><?= htmlspecialchars($r['point_passage'] ?? '') ?></strong></td>
                        <td><?= htmlspecialchars($r['matricule'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['type_capacite'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['annee_fab'] ?? '') ?></td>
                        <td class="text-left"><?= htmlspecialchars($r['axe_routier'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['type_axe'] ?? '') ?></td>
                        <td class="text-left"><?= htmlspecialchars($r['riviere'] ?? '') ?></td>
                        <td class="text-left"><?= htmlspecialchars($r['territoire'] ?? '') ?></td>
                        <td class="text-left"><?= nl2br(htmlspecialchars($r['observation'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="subtotal-row">
                    <td colspan="11">Sous-total (<?= htmlspecialchars($nom_section) ?>) : <?= count($items) ?> bac(s)</td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="11">TOTAL GENERAL BACS ENREGISTRES : <?= $total_general ?> bac(s)</td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="11" style="padding:15px;">Aucun enregistrement trouvé pour ce filtre.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require 'footer.php'; ?>
</body>
</html>