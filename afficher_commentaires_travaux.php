<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
require_once 'db.php';

$mois_saisi = trim($_GET['mois'] ?? report_month_previous());
$recherche_mois = report_month_label($mois_saisi);
$mois_picker = report_month_value($mois_saisi) ?: report_month_previous();
$fiche_courante = 'commentaires';

$stmt = $pdo->prepare("SELECT * FROM rapport_commentaires_travaux WHERE mois_rapport LIKE :mois ORDER BY ordre_affichage ASC, id ASC");
$stmt->execute([':mois' => '%' . trim($recherche_mois) . '%']);
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$introductions = [];
$commentaires  = [];

foreach ($lignes as $r) {
    if (($r['section_type'] ?? '') === 'INTRODUCTION') {
        $introductions[] = $r;
    } else {
        $commentaires[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Introduction & Commentaires / Travaux Exécutés</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 18px; color: #000; line-height: 1.45; }
        .header-doc { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #000; padding-bottom: 8px; }
        .header-doc h3, .header-doc h4 { margin: 3px 0; text-transform: uppercase; }
        h4.sec-title { text-transform: uppercase; margin-top: 18px; margin-bottom: 8px; border-bottom: 1px solid #444; padding-bottom: 3px; font-size: 13px; }
        .bloc-item { margin-bottom: 12px; font-size: 12px; }
        .bloc-titre { font-weight: bold; margin-bottom: 3px; }
        .no-print { margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center; background: #eee; padding: 10px; border-radius: 5px; }
        @media print {
            .no-print { display: none; }
            body { margin: 10px; }
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
        <a href="dupliquer_mois.php?type=commentaires&source=<?= rawurlencode($recherche_mois) ?>">Préparer le mois suivant</a>
        <a href="ajouter_commentaires_travaux.php?mois=<?= rawurlencode($mois_picker) ?>" style="margin-right:15px;">+ Ajouter un commentaire / introduction</a>
        <button class="btn-print" type="button" onclick="window.print()">Imprimer</button>
    </nav>
</div>

<div class="header-doc">
    <h3>RAPPORT MENSUEL D'ACTIVITES TECHNIQUES DES BACS DE TRAVERSEE</h3>
    <h4>CENTRE MATERIELS FLOTTANTS 480 — MOIS DE <?= htmlspecialchars(strtoupper($recherche_mois)) ?></h4>
</div>

<h4 class="sec-title">INTRODUCTION</h4>
<?php if (count($introductions) > 0): ?>
    <?php foreach ($introductions as $intro): ?>
        <div class="bloc-item">
            <?php if (!empty($intro['titre_rubrique']) && strtoupper($intro['titre_rubrique']) !== 'INTRODUCTION'): ?>
                <div class="bloc-titre"><?= htmlspecialchars($intro['titre_rubrique']) ?></div>
            <?php endif; ?>
            <div><?= nl2br(htmlspecialchars($intro['contenu_texte'] ?? '')) ?></div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="font-size:12px;">Aucune introduction enregistrée pour cette période.</p>
<?php endif; ?>

<h4 class="sec-title">QUELQUES COMMENTAIRES ET TRAVAUX EXECUTES (<?= count($commentaires) ?> entrée(s))</h4>
<?php if (count($commentaires) > 0): ?>
    <?php foreach ($commentaires as $c): ?>
        <div class="bloc-item">
            <div class="bloc-titre">❖ <?= htmlspecialchars($c['titre_rubrique'] ?? '') ?></div>
            <div><?= nl2br(htmlspecialchars($c['contenu_texte'] ?? '')) ?></div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="font-size:12px;">Aucun commentaire ou travail exécuté enregistré pour cette période.</p>
<?php endif; ?>

<?php require 'footer.php'; ?>
</body>
</html>