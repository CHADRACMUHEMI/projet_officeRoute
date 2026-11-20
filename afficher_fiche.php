<?php
// Masquer les avertissements mineurs et dépréciations pour garder l'écran propre
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once 'db.php';

// Récupération de la recherche (mois et/ou année tapés par l'utilisateur)
$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'travaux';
$periode_affichee = strtoupper($recherche_mois);

// Utilisation de LIKE pour afficher les résultats dès que le texte correspond (partiellement ou totalement)
$stmt = $pdo->prepare("SELECT * FROM travaux_bacs WHERE mois_rapport LIKE :mois ORDER BY item ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Modèle DMB - <?= htmlspecialchars($periode_affichee) ?></title>
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
        <a href="dupliquer_mois.php?type=travaux&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_bac.php?mois=<?= rawurlencode($mois_picker) ?>">+ Ajouter une ligne</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer la fiche</button>
    </nav>
</div>

<div class="header-doc">
    <h3>RAPPORT MODELE DMB</h3>
    <h3>MOIS DE <?= htmlspecialchars($periode_affichee) ?></h3>
    <br>
    <h4>ETAT D'AVANCEMENT DES TRAVAUX BACS</h4>
</div>

<div class="report-table-wrap">
<table class="report-table" style="font-size: 13px;">
    <thead>
        <tr>
            <th rowspan="2" style="width: 4%;">Item</th>
            <th rowspan="2" style="width: 9%;">MATRICULE</th>
            <th rowspan="2" style="width: 13%;">NOM DU BAC</th>
            <th rowspan="2" style="width: 8%;">TYPE</th>
            <th rowspan="2" style="width: 8%;">N° CONTRAT</th>
            <th rowspan="2" style="width: 28%;">IDENTIFICATION DU MATERIEL</th>
            <th colspan="2" style="width: 12%;">DELAIS D'EXECUTION</th>
            <th rowspan="2" style="width: 18%;">OBSERVATION<br>NIVEAU D'EXECUTION</th>
        </tr>
        <tr>
            <th style="width: 6%;">DEBUT</th>
            <th style="width: 6%;">FIN</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($lignes) > 0): ?>
            <?php foreach ($lignes as $row): ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($row['item'] ?? '') ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['matricule'] ?? '') ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['nom_bac'] ?? '') ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['type_bac'] ?? '') ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['num_contrat'] ?? '-') ?></td>
                    
                    <!-- Colonne Identification du matériel -->
                    <td class="text-left">
                        <?php 
                        $texte = htmlspecialchars($row['identification_materiel'] ?? '');
                        if (stripos($texte, 'ENTRETIEN COURANT') === 0) {
                            $lignes_texte = explode("\n", $texte, 2);
                            echo '<span class="titre-section">' . $lignes_texte[0] . '</span>';
                            if (isset($lignes_texte[1])) {
                                echo nl2br(trim($lignes_texte[1]));
                            }
                        } else {
                            echo '<div class="text-center" style="font-weight:bold;">' . nl2br($texte) . '</div>';
                        }
                        ?>
                    </td>

                    <!-- Colonnes Début et Fin -->
                    <td class="text-center">
                        <?= !empty($row['delai_debut']) ? date('d/m/Y', strtotime($row['delai_debut'])) : '' ?>
                    </td>
                    <td class="text-center">
                        <?= !empty($row['delai_fin']) ? date('d/m/Y', strtotime($row['delai_fin'])) : '' ?>
                    </td>

                    <!-- Colonne Observation / Niveau d'exécution -->
                    <td class="text-left">
                        <?php if (!empty($row['observation_statut'])): ?>
                            <div class="text-center"><?= htmlspecialchars($row['observation_statut']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($row['nouvel_entretien_tb']) || !empty($row['nouvel_entretien_bb'])): ?>
                            <div class="obs-block">
                                <span class="titre-section">Nouvel entretien</span>
                                <u>Index Moteur :</u><br>
                                <?php if (!empty($row['nouvel_entretien_tb'])): ?>
                                    -Tb = <?= htmlspecialchars($row['nouvel_entretien_tb']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($row['nouvel_entretien_bb'])): ?>
                                    -Bb = <?= htmlspecialchars($row['nouvel_entretien_bb']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($row['prochain_entretien_tb']) || !empty($row['prochain_entretien_bb'])): ?>
                            <div class="obs-block" style="margin-bottom:0;">
                                <span class="titre-section">Prochain entretien</span>
                                <u>Index Moteur :</u><br>
                                <?php if (!empty($row['prochain_entretien_tb'])): ?>
                                    -Tb = <?= htmlspecialchars($row['prochain_entretien_tb']) ?><br>
                                <?php endif; ?>
                                <?php if (!empty($row['prochain_entretien_bb'])): ?>
                                    -Bb = <?= htmlspecialchars($row['prochain_entretien_bb']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="text-center">Aucune donnée trouvée pour cette recherche.</td>
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