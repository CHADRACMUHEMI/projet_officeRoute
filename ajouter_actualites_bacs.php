<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO actualites_bacs (
        mois_rapport, categorie_bac, item, code_point, matricule,
        nom_bac, moteur_marque, etat_b, etat_m, type_tonnage,
        description_pannes, travaux_executer, observation
    ) VALUES (
        :mois_rapport, :categorie_bac, :item, :code_point, :matricule,
        :nom_bac, :moteur_marque, :etat_b, :etat_m, :type_tonnage,
        :description_pannes, :travaux_executer, :observation
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'       => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
        ':categorie_bac'      => $_POST['categorie_bac'] ?? 'A. BACS EN ORDRE DE MARCHE',
        ':item'               => $_POST['item'] ?? '',
        ':code_point'         => $_POST['code_point'] ?? '',
        ':matricule'          => $_POST['matricule'] ?? '',
        ':nom_bac'            => $_POST['nom_bac'] ?? '',
        ':moteur_marque'      => $_POST['moteur_marque'] ?? '',
        ':etat_b'             => $_POST['etat_b'] ?: '-',
        ':etat_m'             => $_POST['etat_m'] ?: '-',
        ':type_tonnage'       => $_POST['type_tonnage'] ?? '',
        ':description_pannes' => $_POST['description_pannes'] ?: '-',
        ':travaux_executer'   => $_POST['travaux_executer'] ?: '-',
        ':observation'        => $_POST['observation'] ?: 'RAS'
    ]);

    $message = "Fiche d'actualité du bac enregistrée avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Actualités des Bacs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 920px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 15px; }
        .full { grid-column: span 3; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 6px; box-sizing: border-box; font-family: Arial, sans-serif; }
        textarea { min-height: 65px; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Actualités Bacs (A. Ordre de marche / B. Pannes / C. À réformer)</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label>Mois / Année :</label><input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required></div>
            <div class="full">
                <label>Catégorie :</label>
                <select name="categorie_bac" required>
                    <option value="A. BACS EN ORDRE DE MARCHE">A. BACS EN ORDRE DE MARCHE</option>
                    <option value="B. BACS EN PANNES">B. BACS EN PANNES</option>
                    <option value="C. BACS A REFORMER">C. BACS A REFORMER</option>
                </select>
            </div>

            <div><label>IT. :</label><input type="text" name="item" placeholder="Ex: 01"></div>
            <div><label>CODE :</label><input type="text" name="code_point" placeholder="Ex: 4/1"></div>
            <div><label>MATR. :</label><input type="text" name="matricule" placeholder="Ex: A23678"></div>

            <div><label>NOM DU BAC :</label><input type="text" name="nom_bac" required></div>
            <div><label>NOMBRE MOTEUR ET MARQUE :</label><input type="text" name="moteur_marque" placeholder="Ex: 2 JOHN DEERE"></div>
            <div><label>TYPE ET TONNAGE :</label><input type="text" name="type_tonnage" placeholder="Ex: AMB 35T."></div>

            <div><label>ETAT (B) :</label><input type="text" name="etat_b" value="-"></div>
            <div><label>ETAT (M) :</label><input type="text" name="etat_m" value="-"></div>
            <div><label>OBSERVATION :</label><input type="text" name="observation" value="RAS"></div>

            <div class="full"><label>DESCRIPTION DES PANNES :</label><textarea name="description_pannes"></textarea></div>
            <div class="full"><label>TRAVAUX A EXECUTER :</label><textarea name="travaux_executer"></textarea></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="afficher_actualites_bacs.php" style="margin-left: 15px;">Voir la fiche complète →</a>
    </form>
</div>
</body>
</html>