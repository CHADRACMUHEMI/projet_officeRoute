<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO materiel_appui_bacs (
        mois_rapport, item, categorie, marque_type, numero_or,
        etat_localisation, prop_declasser, prop_renouveller, observ
    ) VALUES (
        :mois_rapport, :item, :categorie, :marque_type, :numero_or,
        :etat_localisation, :prop_declasser, :prop_renouveller, :observ
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'      => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
        ':item'              => $_POST['item'] ?? '',
        ':categorie'         => $_POST['categorie'] ?? '',
        ':marque_type'       => $_POST['marque_type'] ?? '',
        ':numero_or'         => $_POST['numero_or'] ?: '-',
        ':etat_localisation' => $_POST['etat_localisation'] ?: '-',
        ':prop_declasser'    => $_POST['prop_declasser'] ?: '-',
        ':prop_renouveller'  => $_POST['prop_renouveller'] ?: '-',
        ':observ'            => $_POST['observ'] ?: '-'
    ]);

    $message = "Matériel d'appui enregistré avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Matériel d'Appui à l'Intervention Bacs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 15px; }
        .full { grid-column: span 3; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input, textarea { width: 100%; padding: 6px; box-sizing: border-box; font-family: Arial, sans-serif; }
        textarea { min-height: 65px; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Matériel d'Appui à l'Intervention Bacs</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label>Mois / Année :</label><input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required></div>
            <div><label>Item :</label><input type="text" name="item" placeholder="Ex: 01"></div>
            <div><label>N°OR :</label><input type="text" name="numero_or" placeholder="Ex: 131332 ou -"></div>

            <div><label>Catégorie :</label><input type="text" name="categorie" placeholder="Ex: Camion Benne fixe" required></div>
            <div style="grid-column: span 2;"><label>Marque & Type :</label><input type="text" name="marque_type" placeholder="Ex: Sinotruck"></div>

            <div><label>Proposition - À Déclasser :</label><input type="text" name="prop_declasser" placeholder="Ex: X, 1 ou -" value="-"></div>
            <div><label>Proposition - À Renouveller :</label><input type="text" name="prop_renouveller" placeholder="Ex: X, 1 ou -" value="-"></div>
            <div><label>Observation :</label><input type="text" name="observ" value="-"></div>

            <div class="full"><label>État et Localisation :</label><textarea name="etat_localisation" placeholder="Ex: OM, se trouve au CMR 470"></textarea></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="afficher_materiel_appui.php" style="margin-left: 15px;">Voir la fiche complète →</a>
    </form>
</div>
</body>
</html>