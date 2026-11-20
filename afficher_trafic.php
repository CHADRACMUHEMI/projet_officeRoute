<?php
// Masquer les avertissements mineurs et dépréciations pour garder l'écran propre
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'trafics';
$periode_affichee = strtoupper($recherche_mois);

$stmt = $pdo->prepare("SELECT * FROM trafics_bacs WHERE mois_rapport LIKE :mois ORDER BY item ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Mensuel Trafics sur les Bacs - <?= htmlspecialchars($periode_affichee) ?></title>
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
        <a href="dupliquer_mois.php?type=trafics&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_trafic.php?mois=<?= rawurlencode($mois_picker) ?>">+ Ajouter un trafic</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer la fiche</button>
    </nav>
</div>

<div class="header-doc">
    <h3>RAPPORT MODELE DMB</h3>
    <h3>RAPPORT MENSUEL TRAFICS SUR LES BACS</h3>
    <h4>MOUVEMENTS POUR : <?= htmlspecialchars($periode_affichee) ?></h4>
</div>

<div class="report-table-wrap">
<table class="report-table" style="font-size: 10px;">
    <thead>
        <tr>
            <th rowspan="2" style="width: 3%;">It.</th>
            <th rowspan="2" style="width: 12%;">Nom</th>
            <th rowspan="2" style="width: 7%;">N° MATRICULE</th>
            <th rowspan="2" style="width: 6%;">NOMBRE JOUR TRAVERSEE</th>
            <th colspan="19">CATEGORIE ET NOMBRE</th>
            <th rowspan="2" style="width: 4%;">OBS</th>
        </tr>
        <tr>
            <th>PTO</th>
            <th>VLO</th>
            <th>CON</th>
            <th>STRU</th>
            <th>CT</th>
            <th>ION</th>
            <th>VLE</th>
            <th>MTO</th>
            <th>MTO CHARGE</th>
            <th>MTO GIZI</th>
            <th>MTO GIZI CHARGE</th>
            <th>VMO</th>
            <th>NEU</th>
            <th>VE</th>
            <th>CAM</th>
            <th>PLD</th>
            <th>VTP</th>
            <th>CRM</th>
            <th>Vache</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($lignes) > 0): ?>
            <?php foreach ($lignes as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item'] ?? '') ?></td>
                    <td class="text-left"><?= htmlspecialchars($row['nom_bac'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['matricule'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['nb_jour_traversee'] ?? '-') ?></td>
                    
                    <!-- Colonnes de Trafic sécurisées -->
                    <td><?= htmlspecialchars($row['pto'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['vlo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['con'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['stru'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['ct'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['ion'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['vle'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['mto'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['mto_charge'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['mto_gizi'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['mto_gizi_charge'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['vmo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['neu'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['ve'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['cam'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['pld'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['vtp'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['crm'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['vache'] ?? '-') ?></td>
                    
                    <td><?= htmlspecialchars($row['obs'] ?? 'RAS') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="24" style="text-align: center; padding: 15px;">Aucun trafic trouvé pour cette recherche.</td>
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