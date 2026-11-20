<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'cout';
$periode_affichee = strtoupper($recherche_mois);

$stmt = $pdo->prepare("SELECT * FROM cout_exploitation_bacs WHERE mois_rapport LIKE :mois ORDER BY item ASC");
$stmt->execute([':mois' => '%' . $recherche_mois . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variables pour le calcul des totaux
$tot_personnel = 0;
$tot_entretien = 0;
$tot_fonctionnement = 0;
$tot_general = 0;

function extraire_nombre($valeur) {
    preg_match('/([0-9\s,.]+)/', $valeur, $matches);
    if (!empty($matches[1])) {
        return floatval(str_replace([' ', ','], ['', '.'], $matches[1]));
    }
    return 0;
}

foreach ($lignes as $row) {
    $tot_personnel += extraire_nombre($row['charge_personnel']);
    $tot_entretien += extraire_nombre($row['charge_entretien']);
    $tot_fonctionnement += extraire_nombre($row['fonctionnement']);
    $tot_general += extraire_nombre($row['total_fc']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Coût d'Exploitation Bacs et Tiers - <?= htmlspecialchars($periode_affichee) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="report-page">
<?php require __DIR__ . '/app_header.php'; ?>

<main class="report-container">
<div class="no-print">
    <form method="GET">
        <label for="mois"><strong>Filtrer (Mois et/ou année) :</strong></label>
        <input id="mois" type="month" name="mois" value="<?= htmlspecialchars($mois_picker) ?>" required>
        <button type="submit">Rechercher</button>
    </form>
    <nav class="site-nav fiche-actions" aria-label="Actions sur la fiche">
        <a href="dupliquer_mois.php?type=cout&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_cout.php?mois=<?= rawurlencode($mois_picker) ?>">+ Ajouter un coût</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer la fiche</button>
    </nav>
</div>

<div class="header-doc">
    <h3>RAPPORT MODELE DMB</h3>
    <h3>MOIS DE <?= htmlspecialchars($periode_affichee) ?></h3>
    <h3>COUT D'EXPLOITATION BACS ET TIERS</h3>
</div>

<div class="report-table-wrap">
<table class="report-table" style="font-size: 11px;">
    <thead>
        <tr>
            <th rowspan="2" style="width: 4%;">Item</th>
            <th rowspan="2" style="width: 8%;">N° BAC</th>
            <th rowspan="2" style="width: 15%;">NOM DU POINT DE PASSAGE</th>
            <th rowspan="2" style="width: 10%;">RECETTE DU BAC</th>
            <th rowspan="2" style="width: 10%;">ETAT EN FIN DU MOIS</th>
            <th colspan="4">COUT D'EXPLOITATION EN FC</th>
            <th rowspan="2" style="width: 15%;">OBSERV.</th>
        </tr>
        <tr>
            <th>CHARGE DU PERSONNEL</th>
            <th>CHARGE DU ENTRETIEN / REPARATION</th>
            <th>FONCTIONNEMENT</th>
            <th>TOTAL FC</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($lignes) > 0): ?>
            <?php foreach ($lignes as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['matricule'] ?? '') ?></td>
                    <td class="text-left"><?= htmlspecialchars($row['nom_bac'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['recette_bac'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['etat_fin_mois'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['charge_personnel'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['charge_entretien'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['fonctionnement'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['total_fc'] ?? '-') ?></td>
                    <td class="text-left"><?= htmlspecialchars($row['observ'] ?? 'RAS') ?></td>
                </tr>
            <?php endforeach; ?>

            <!-- LIGNE TOTAL GENERAL -->
            <tr class="total-row">
                <td colspan="5" style="text-align: right; padding-right: 10px;">Total-général :</td>
                <td><?= $tot_personnel ? number_format($tot_personnel, 0, ',', ' ') : '-' ?></td>
                <td><?= $tot_entretien ? number_format($tot_entretien, 0, ',', ' ') : '-' ?></td>
                <td><?= $tot_fonctionnement ? number_format($tot_fonctionnement, 0, ',', ' ') : '-' ?></td>
                <td><?= $tot_general ? number_format($tot_general, 0, ',', ' ') : '-' ?></td>
                <td>-</td>
            </tr>

        <?php else: ?>
            <tr>
                <td colspan="10" class="text-center">Aucune donnée trouvée pour cette recherche.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<section class="report-signatures" aria-label="Visa et signatures">
    <h3>Pour l'adjoint technique</h3>
    <div class="report-signature-grid">
        <div class="report-signature">
            <p>Chef magasinier</p>
            <div class="signature-space" aria-hidden="true"></div>
        </div>
        <div class="report-signature">
            <p>Chargé d'ord./Excel</p>
            <div class="signature-space" aria-hidden="true"></div>
        </div>
        <div class="report-signature">
            <p>Chef de centre</p>
            <div class="signature-space" aria-hidden="true"></div>
        </div>
    </div>
</section>
</main>
<?php require 'footer.php'; ?>
</body>
</html>