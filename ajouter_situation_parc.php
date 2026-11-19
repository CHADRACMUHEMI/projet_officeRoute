<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO situation_parc_bacs (
        mois_rapport, province, section_etat, numero_ordre, code_point,
        point_passage, matricule, type_capacite, annee_fab,
        axe_routier, type_axe, riviere, territoire, observation
    ) VALUES (
        :mois_rapport, :province, :section_etat, :numero_ordre, :code_point,
        :point_passage, :matricule, :type_capacite, :annee_fab,
        :axe_routier, :type_axe, :riviere, :territoire, :observation
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'  => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
        ':province'      => $_POST['province'] ?? 'PROVINCE DE LA TSHOPO',
        ':section_etat'  => $_POST['section_etat'] ?? '1.1. BACS EN ORDRE DE MARCHE',
        ':numero_ordre'  => $_POST['numero_ordre'] ?? '',
        ':code_point'    => $_POST['code_point'] ?? '',
        ':point_passage' => $_POST['point_passage'] ?? '',
        ':matricule'     => $_POST['matricule'] ?? '',
        ':type_capacite' => $_POST['type_capacite'] ?? '',
        ':annee_fab'     => $_POST['annee_fab'] ?: '-',
        ':axe_routier'   => $_POST['axe_routier'] ?? '',
        ':type_axe'      => $_POST['type_axe'] ?? '',
        ':riviere'       => $_POST['riviere'] ?? '',
        ':territoire'    => $_POST['territoire'] ?? '',
        ':observation'   => $_POST['observation'] ?: '-'
    ]);

    $message = "Ligne ajoutée à la situation du parc avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Situation du Parc Matériel Bacs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 920px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 15px; }
        .full { grid-column: span 3; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 6px; box-sizing: border-box; font-family: Arial, sans-serif; }
        textarea { min-height: 75px; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Situation du Parc Matériel Bacs et Unités Fluviales</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label>Mois / Année :</label><input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required></div>
            <div><label>Province :</label><input type="text" name="province" value="PROVINCE DE LA TSHOPO" required></div>
            <div>
                <label>Catégorie / Section :</label>
                <select name="section_etat" required>
                    <option value="1.1. BACS EN ORDRE DE MARCHE">1.1. BACS EN ORDRE DE MARCHE</option>
                    <option value="1.2. BACS EN PANNE REPARABLE">1.2. BACS EN PANNE REPARABLE</option>
                    <option value="1.3. BACS EN PANNE IRREPARABLE">1.3. BACS EN PANNE IRREPARABLE</option>
                </select>
            </div>

            <div><label>N° (Item) :</label><input type="text" name="numero_ordre" placeholder="Ex: 01 ou 09"></div>
            <div><label>N° Code :</label><input type="text" name="code_point" placeholder="Ex: 4/1"></div>
            <div><label>Point de passage :</label><input type="text" name="point_passage" placeholder="Ex: KISANGANI III" required></div>

            <div><label>Matricule :</label><input type="text" name="matricule" placeholder="Ex: A23678"></div>
            <div><label>Type - Mode de propul. et capacité :</label><input type="text" name="type_capacite" placeholder="Ex: AMB-M-35T."></div>
            <div><label>Ans :</label><input type="text" name="annee_fab" placeholder="Ex: 2008 ou -"></div>

            <div><label>Axe routier :</label><input type="text" name="axe_routier" placeholder="Ex: KISANGANI - OPALA"></div>
            <div><label>Type d'axe :</label><input type="text" name="type_axe" placeholder="Ex: RN, RP1, RR1, RIL"></div>
            <div><label>Rivière :</label><input type="text" name="riviere" placeholder="Ex: FLEUVE CONGO"></div>

            <div><label>Territoire :</label><input type="text" name="territoire" placeholder="Ex: MAKISO"></div>
            <div class="full"><label>Observation :</label><textarea name="observation" placeholder="État technique, pannes, pièces nécessaires..."></textarea></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="afficher_situation_parc.php" style="margin-left: 15px;">Voir la fiche complète →</a>
    </form>
</div>
</body>
</html>