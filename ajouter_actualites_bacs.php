<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

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
        ':mois_rapport'       => report_month_label($mois_rapport),
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

    header('Location: afficher_actualites_bacs.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Actualités des Bacs</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container actualites-form">
    <h2>Actualités des bacs</h2>
    <p class="entry-intro">Renseignez la période, la catégorie et les informations du bac.</p>
    <?php if ($message): ?>
        <div class="alert" role="status"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <fieldset>
            <legend>Période et catégorie</legend>
            <div class="grid grid-3">
                <div>
                    <label for="mois_rapport">Mois et année</label>
                    <input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required>
                </div>
                <div class="full">
                    <label for="categorie_bac">Catégorie</label>
                    <select id="categorie_bac" name="categorie_bac" required>
                        <option value="A. BACS EN ORDRE DE MARCHE">A. BACS EN ORDRE DE MARCHE</option>
                        <option value="B. BACS EN PANNES">B. BACS EN PANNES</option>
                        <option value="C. BACS A REFORMER">C. BACS À RÉFORMER</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Identification du bac</legend>
            <div class="grid grid-3">
                <div>
                    <label for="item">Item</label>
                    <input id="item" type="text" name="item" placeholder="Ex. : 01">
                </div>
                <div>
                    <label for="code_point">Code</label>
                    <input id="code_point" type="text" name="code_point" placeholder="Ex. : 4/1">
                </div>
                <div>
                    <label for="matricule">Matricule</label>
                    <input id="matricule" type="text" name="matricule" placeholder="Ex. : A23678">
                </div>
                <div>
                    <label for="nom_bac">Nom du bac</label>
                    <input id="nom_bac" type="text" name="nom_bac" required>
                </div>
                <div>
                    <label for="moteur_marque">Nombre et marque du moteur</label>
                    <input id="moteur_marque" type="text" name="moteur_marque" placeholder="Ex. : 2 JOHN DEERE">
                </div>
                <div>
                    <label for="type_tonnage">Type et tonnage</label>
                    <input id="type_tonnage" type="text" name="type_tonnage" placeholder="Ex. : AMB 35T">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>État et interventions</legend>
            <div class="grid grid-3">
                <div>
                    <label for="etat_b">État (B)</label>
                    <input id="etat_b" type="text" name="etat_b" value="-">
                </div>
                <div>
                    <label for="etat_m">État (M)</label>
                    <input id="etat_m" type="text" name="etat_m" value="-">
                </div>
                <div>
                    <label for="observation">Observation</label>
                    <input id="observation" type="text" name="observation" value="RAS">
                </div>
                <div class="full">
                    <label for="description_pannes">Description des pannes</label>
                    <textarea id="description_pannes" name="description_pannes" rows="4"></textarea>
                </div>
                <div class="full">
                    <label for="travaux_executer">Travaux à exécuter</label>
                    <textarea id="travaux_executer" name="travaux_executer" rows="4"></textarea>
                </div>
            </div>
        </fieldset>

        <div class="entry-actions">
            <button type="submit" class="btn">Enregistrer</button>
        </div>
    </form>
</main>
<?php require 'footer.php'; ?>
</body>
</html>