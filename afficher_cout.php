<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once 'db.php';

$recherche_mois = trim($_GET['mois'] ?? '');
$periode_affichee = $recherche_mois !== '' ? strtoupper($recherche_mois) : 'TOUS LES MOIS';

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

<main class="report-container">
<div class="no-print">
    <form method="GET">
        <label for="mois"><strong>Filtrer (Mois et/ou année) :</strong></label>
        <input id="mois" type="text" name="mois" value="<?= htmlspecialchars($recherche_mois) ?>" placeholder="Ex: SEPTEMBRE 2026">
        <button type="submit">Rechercher</button>
    </form>
    <nav class="site-nav" aria-label="Navigation des fiches">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="afficher_fiche.php">Fiche des travaux</a>
        <a href="afficher_trafic.php">Fiche des trafics</a>
        <a href="afficher_consommation.php">Fiche de consommation</a>
        <a href="dupliquer_mois.php?type=cout&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_cout.php">+ Ajouter un coût</a>
        <a href="ajouter_bac.php">+ Ajouter des travaux</a>
        <a href="ajouter_trafic.php">+ Ajouter un trafic</a>
        <a href="ajouter_consommation.php">+ Ajouter une consommation</a>
        <button type="button" onclick="window.print()">Imprimer la fiche</button>
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
</body>
</html>