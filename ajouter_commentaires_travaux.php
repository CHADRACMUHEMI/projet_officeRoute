<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO rapport_commentaires_travaux (
        mois_rapport, section_type, titre_rubrique, contenu_texte, ordre_affichage
    ) VALUES (
        :mois_rapport, :section_type, :titre_rubrique, :contenu_texte, :ordre_affichage
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'    => report_month_label($mois_rapport),
        ':section_type'    => $_POST['section_type'] ?? 'COMMENTAIRES_TRAVAUX',
        ':titre_rubrique'  => $_POST['titre_rubrique'] ?? '',
        ':contenu_texte'   => $_POST['contenu_texte'] ?? '',
        ':ordre_affichage' => intval($_POST['ordre_affichage'] ?? 1)
    ]);

    header('Location: afficher_commentaires_travaux.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Introduction & Commentaires / Travaux</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Introduction & Commentaires / Travaux Exécutés</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid grid-3">
            <div><label for="mois_rapport">Mois / Année :</label><input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required></div>
            <div>
                <label>Type de section :</label>
                <select name="section_type" required>
                    <option value="COMMENTAIRES_TRAVAUX">QUELQUES COMMENTAIRES ET TRAVAUX EXECUTES</option>
                    <option value="INTRODUCTION">INTRODUCTION</option>
                </select>
            </main>
            <div><label>Ordre d'affichage :</label><input type="number" name="ordre_affichage" value="1"></div>

            <div class="full">
                <label>Titre / Sujet (ex. Bac Isangi A23788 TRAD/M ou Introduction) :</label>
                <input type="text" name="titre_rubrique" required>
            </div>

            <div class="full">
                <label>Texte descriptif :</label>
                <textarea name="contenu_texte" required></textarea>
            </div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>