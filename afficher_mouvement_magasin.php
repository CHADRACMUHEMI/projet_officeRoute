<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'mouvement_magasin';

$stmt = $pdo->prepare("SELECT * FROM mouvement_magasin_dmb003 WHERE mois_rapport LIKE :mois ORDER BY CAST(item AS UNSIGNED) ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function parse_num($txt) {
    if (!$txt) return 0.0;
    $t = trim((string)$txt);
    if ($t === '' || $t === '-' || $t === '-//-//-') return 0.0;
    $t = preg_replace('/[\s\x{00A0}]+/u', '', $t);
    if (preg_match('/([0-9]+(?:[.,][0-9]+)?)/', $t, $m)) {
        return floatval(str_replace(',', '.', $m[1]));
    }
    return 0.0;
}

function parse_unite($txt) {
    if (!$txt) return '';
    if (preg_match('/[0-9]+(?:[.,][0-9]+)?\s*(.+)$/u', trim((string)$txt), $m)) {
        return trim($m[1]);
    }
    return '';
}

$total_articles = count($lignes);
$sum_entree_qte = 0.0;
$sum_sortie_qte = 0.0;
$sum_init_qte   = 0.0;
$sum_fin_qte    = 0.0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Mouvement Mensuel Entrées & Sorties Magasin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; color: #000; }
        .header-doc { text-align: center; margin-bottom: 12px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 4px 3px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; padding-left: 5px; }
        .total-row { font-weight: bold; background-color: #eaeaea; }
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
        <a href="dupliquer_mois.php?type=mouvement_magasin&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_mouvement_magasin.php?mois=<?= rawurlencode($mois_picker) ?>" style="margin-right:15px;">+ Ajouter un article</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer</button>
    </nav>
</div>

<div class="header-doc">
    <h3>RAPPORT MODELE DMB 003</h3>
    <h4>MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
    <h4>MOUVEMENT MENSUEL ENTREES & SORTIES MAGASIN</h4>
</div>

<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:3%;">Item</th>
            <th colspan="3">IDENTIFICATION DE LA MARCHANDISE</th>
            <th colspan="3">MOUVEMENT EN ENTREE</th>
            <th colspan="5">MOUVEMENT EN SORTIE</th>
            <th colspan="2">STOCK</th>
        </tr>
        <tr>
            <th style="width:7%;">Marque Type</th>
            <th style="width:5%;">Réf</th>
            <th style="width:18%;">Désignation</th>
            <th style="width:6%;">Qté</th>
            <th style="width:7%;">Prix</th>
            <th style="width:7%;">Fré ou Bad</th>
            <th style="width:6%;">Qté</th>
            <th style="width:7%;">Prix</th>
            <th style="width:5%;">Client</th>
            <th style="width:5%;">N°FT</th>
            <th style="width:8%;">D.S. OU B.S</th>
            <th style="width:7%;">Initial</th>
            <th style="width:7%;">Fin mois</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($total_articles > 0): ?>
            <?php foreach ($lignes as $r):
                $q_init   = parse_num($r['stock_initial']);
                $q_entree = parse_num($r['entree_qte']);
                $q_sortie = parse_num($r['sortie_qte']);

                $sum_init_qte   += $q_init;
                $sum_entree_qte += $q_entree;
                $sum_sortie_qte += $q_sortie;

                $fin_aff = $r['stock_fin_mois'];
                if ($fin_aff === '' || $fin_aff === 'AUTO') {
                    $calc = $q_init + $q_entree - $q_sortie;
                    $u = parse_unite($r['stock_initial']) ?: (parse_unite($r['entree_qte']) ?: parse_unite($r['sortie_qte']));
                    $fin_aff = rtrim(rtrim(number_format($calc, 2, ',', ' '), '0'), ',') . ($u ? ' ' . $u : '');
                }
                $sum_fin_qte += parse_num($fin_aff);
            ?>
                <tr>
                    <td><?= htmlspecialchars($r['item'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['marque_type'] ?? '-//-//-') ?></td>
                    <td><?= htmlspecialchars($r['reference_art'] ?? '-') ?></td>
                    <td class="text-left"><?= htmlspecialchars($r['designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($r['entree_qte'] ?? '0') ?></td>
                    <td><?= htmlspecialchars($r['entree_prix'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['entree_fre_bad'] ?? '-//-//-') ?></td>
                    <td><?= htmlspecialchars($r['sortie_qte'] ?? '0') ?></td>
                    <td><?= htmlspecialchars($r['sortie_prix'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['sortie_client'] ?? 'OR') ?></td>
                    <td><?= htmlspecialchars($r['sortie_nft'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($r['sortie_ds_bs'] ?? '-//-//-') ?></td>
                    <td><?= htmlspecialchars($r['stock_initial'] ?? '0') ?></td>
                    <td><strong><?= htmlspecialchars($fin_aff) ?></strong></td>
                </tr>
            <?php endforeach; ?>

            <tr class="total-row">
                <td colspan="4" style="text-align:right; padding-right:8px;">Totaux (<?= $total_articles ?> articles) :</td>
                <td><?= rtrim(rtrim(number_format($sum_entree_qte, 2, ',', ' '), '0'), ',') ?></td>
                <td colspan="2">-</td>
                <td><?= rtrim(rtrim(number_format($sum_sortie_qte, 2, ',', ' '), '0'), ',') ?></td>
                <td colspan="4">-</td>
                <td><?= rtrim(rtrim(number_format($sum_init_qte, 2, ',', ' '), '0'), ',') ?></td>
                <td><?= rtrim(rtrim(number_format($sum_fin_qte, 2, ',', ' '), '0'), ',') ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <td colspan="14" style="padding:15px;">Aucun mouvement trouvé pour cette période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require 'footer.php'; ?>
</body>
</html>