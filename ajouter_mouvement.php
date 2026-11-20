<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO mouvement_carburant_dap (
        mois_rapport, categorie, provenance,
        gasoil, essence, sae50, sae40, sae10, atf,
        liquide_frein, graisse, eau_vive, anti_gel, obs
    ) VALUES (
        :mois_rapport, :categorie, :provenance,
        :gasoil, :essence, :sae50, :sae40, :sae10, :atf,
        :liquide_frein, :graisse, :eau_vive, :anti_gel, :obs
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'  => report_month_label($mois_rapport),
        ':categorie'     => $_POST['categorie'] ?? 'ENTREE',
        ':provenance'    => $_POST['provenance'] ?? '',
        ':gasoil'        => floatval($_POST['gasoil'] ?? 0),
        ':essence'       => floatval($_POST['essence'] ?? 0),
        ':sae50'         => floatval($_POST['sae50'] ?? 0),
        ':sae40'         => floatval($_POST['sae40'] ?? 0),
        ':sae10'         => floatval($_POST['sae10'] ?? 0),
        ':atf'           => floatval($_POST['atf'] ?? 0),
        ':liquide_frein' => floatval($_POST['liquide_frein'] ?? 0),
        ':graisse'       => floatval($_POST['graisse'] ?? 0),
        ':eau_vive'      => floatval($_POST['eau_vive'] ?? 0),
        ':anti_gel'      => floatval($_POST['anti_gel'] ?? 0),
        ':obs'           => $_POST['obs'] ?: '-'
    ]);

    header('Location: afficher_mouvement.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Mouvement Carburant & Lubrifiant (D.A.P)</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Mouvement Carburant & Lubrifiant (D.A.P)</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid grid-3">
            <div>
                <label>Mois / Année :</label>
                <input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required>
            </div>
            <div>
                <label>Type de mouvement :</label>
                <select name="categorie" required>
                    <option value="STOCK_DEBUT">Stock au début mois</option>
                    <option value="ENTREE">Entrée</option>
                    <option value="SORTIE">Sortie ou destination</option>
                </select>
            </div>
            <div>
                <label>Provenance / Destination :</label>
                <input type="text" name="provenance" placeholder="Ex: D.P 400 ou VEHICULES D'APPUI CENTRE" required>
            </div>
        </div>

        <div class="grid grid-3">
            <div><label>Gas-oil (L) :</label><input type="number" step="0.01" name="gasoil" value="0"></div>
            <div><label>Essence (L) :</label><input type="number" step="0.01" name="essence" value="0"></div>
            <div><label>SAE50 (L) :</label><input type="number" step="0.01" name="sae50" value="0"></div>
            <div><label>SAE40 (L) :</label><input type="number" step="0.01" name="sae40" value="0"></div>
            <div><label>SAE10 (L) :</label><input type="number" step="0.01" name="sae10" value="0"></div>
            <div><label>ATF (L) :</label><input type="number" step="0.01" name="atf" value="0"></div>
            <div><label>Liquide de frein (L) :</label><input type="number" step="0.01" name="liquide_frein" value="0"></div>
            <div><label>Graisse (Kgs/L) :</label><input type="number" step="0.01" name="graisse" value="0"></div>
            <div><label>Eau vive (L) :</label><input type="number" step="0.01" name="eau_vive" value="0"></div>
            <div><label>Anti-gel (L/Kg) :</label><input type="number" step="0.01" name="anti_gel" value="0"></div>
            <div><label>Observation :</label><input type="text" name="obs" value="-"></div>
        </main>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>