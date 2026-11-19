<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

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
        ':mois_rapport'  => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
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

    $message = "Mouvement enregistré avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Mouvement Carburant & Lubrifiant (D.A.P)</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 15px; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 6px; box-sizing: border-box; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Mouvement Carburant & Lubrifiant (D.A.P)</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div>
                <label>Mois / Année :</label>
                <input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required>
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

        <div class="grid">
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
        </div>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="afficher_mouvement.php" style="margin-left: 15px;">Voir le rapport →</a>
    </form>
</div>
</body>
</html>