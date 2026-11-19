<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

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
        ':mois_rapport'    => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
        ':famille_nom'     => $_POST['famille_nom'] ?? 'I.-MACHINE OUTILS',
        ':designation'     => $_POST['designation'] ?? '',
        ':a_reformer'      => intval($_POST['a_reformer'] ?? 0),
        ':actif'           => intval($_POST['actif'] ?? 0),
        ':panne'           => intval($_POST['panne'] ?? 0),
        ':disponible'      => intval($_POST['disponible'] ?? 0),
        ':ordre_affichage' => intval($_POST['ordre_affichage'] ?? 1)
    ]);

    $message = "Équipement enregistré dans l'inventaire avec succès !";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Inventaire Matériel d'Atelier</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 820px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 15px; }
        .full { grid-column: span 2; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input { width: 100%; padding: 6px; box-sizing: border-box; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Inventaire et État du Matériel d'Atelier</h2>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label>Mois / Année :</label><input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required></div>
            <div><label>Ordre d'affichage :</label><input type="number" name="ordre_affichage" value="1"></div>

            <div class="full"><label>Famille de matériel (ex. I.-MACHINE OUTILS, II.EQU.ATEL.MEC.AUTO, III. EQUIPEMENTS DIVERS) :</label><input type="text" name="famille_nom" required></div>
            <div class="full"><label>Désignation (ex. I.1 cisaille, III.9 Groupe électrogène) :</label><input type="text" name="designation" required></div>

            <div><label>Actif :</label><input type="number" name="actif" value="0" min="0"></div>
            <div><label>Panne :</label><input type="number" name="panne" value="0" min="0"></div>
            <div><label>Disponible :</label><input type="number" name="disponible" value="0" min="0"></div>
            <div><label>À Réformer :</label><input type="number" name="a_reformer" value="0" min="0"></div>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="afficher_inventaire_atelier.php" style="margin-left: 15px;">Voir l'inventaire →</a>
    </form>
</div>
</body>
</html>