<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO inventaire_materiel_atelier (
        mois_rapport, famille_nom, designation,
        a_reformer, actif, panne, disponible, ordre_affichage
    ) VALUES (
        :mois_rapport, :famille_nom, :designation,
        :a_reformer, :actif, :panne, :disponible, :ordre_affichage
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'    => report_month_label($mois_rapport),
        ':famille_nom'     => $_POST['famille_nom'] ?? 'I.-MACHINE OUTILS',
        ':designation'     => $_POST['designation'] ?? '',
        ':a_reformer'      => intval($_POST['a_reformer'] ?? 0),
        ':actif'           => intval($_POST['actif'] ?? 0),
        ':panne'           => intval($_POST['panne'] ?? 0),
        ':disponible'      => intval($_POST['disponible'] ?? 0),
        ':ordre_affichage' => intval($_POST['ordre_affichage'] ?? 1)
    ]);

    header('Location: afficher_inventaire_atelier.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Inventaire Matériel d'Atelier</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container inventory-form">
    <h2>Inventaire et état du matériel d’atelier</h2>
    <p class="entry-intro">Renseignez le mois du rapport, la famille de matériel et son état.</p>
    <?php if ($message): ?>
        <div class="alert" role="status"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <fieldset>
            <legend>Période et classement</legend>
            <div class="grid">
                <div>
                    <label for="mois_rapport">Mois et année</label>
                    <input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required>
                </div>
                <div>
                    <label for="ordre_affichage">Ordre d’affichage</label>
                    <input id="ordre_affichage" type="number" name="ordre_affichage" value="1">
                </div>
                <div class="full">
                    <label for="famille_nom">Famille de matériel</label>
                    <input id="famille_nom" type="text" name="famille_nom" placeholder="Ex. : I.-MACHINE OUTILS, II. ÉQU. ATEL. MÉC. AUTO" required>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Matériel et état</legend>
            <div class="grid">
                <div class="full">
                    <label for="designation">Désignation</label>
                    <input id="designation" type="text" name="designation" placeholder="Ex. : I.1 Cisaille, III.9 Groupe électrogène" required>
                </div>
                <div>
                    <label for="actif">Actif</label>
                    <input id="actif" type="number" name="actif" value="0" min="0">
                </div>
                <div>
                    <label for="panne">En panne</label>
                    <input id="panne" type="number" name="panne" value="0" min="0">
                </div>
                <div>
                    <label for="disponible">Disponible</label>
                    <input id="disponible" type="number" name="disponible" value="0" min="0">
                </div>
                <div>
                    <label for="a_reformer">À réformer</label>
                    <input id="a_reformer" type="number" name="a_reformer" value="0" min="0">
                </div>
            </div>
        </fieldset>

        <button type="submit">Enregistrer l’équipement</button>
    </form>
</main>
<?php require 'footer.php'; ?>
</body>
</html>