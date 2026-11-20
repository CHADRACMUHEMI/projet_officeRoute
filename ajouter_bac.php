<?php
// Qctiver l'affichage des erreurs pour le debogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db.php'; // Votre fichier de connexion PDO ($pdo)

$message = "";
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = htmlspecialchars($_POST['item']);
    $matricule = htmlspecialchars($_POST['matricule']);
    $nom_bac = htmlspecialchars($_POST['nom_bac']);
    $type_bac = htmlspecialchars($_POST['type_bac']);
    $num_contrat = !empty($_POST['num_contrat']) ? htmlspecialchars($_POST['num_contrat']) : '-';
    $identification_materiel = htmlspecialchars($_POST['identification_materiel']);
    
    $delai_debut = !empty($_POST['delai_debut']) ? $_POST['delai_debut'] : null;
    $delai_fin = !empty($_POST['delai_fin']) ? $_POST['delai_fin'] : null;
    
    $observation_statut = htmlspecialchars($_POST['observation_statut']);
    $nouvel_entretien_tb = htmlspecialchars($_POST['nouvel_entretien_tb']);
    $nouvel_entretien_bb = htmlspecialchars($_POST['nouvel_entretien_bb']);
    $prochain_entretien_tb = htmlspecialchars($_POST['prochain_entretien_tb']);
    $prochain_entretien_bb = htmlspecialchars($_POST['prochain_entretien_bb']);
    
    $user_id = $_SESSION['user_id'] ?? null;

    $sql = "INSERT INTO travaux_bacs (
                mois_rapport, item, matricule, nom_bac, type_bac, num_contrat, 
                identification_materiel, delai_debut, delai_fin, observation_statut, 
                nouvel_entretien_tb, nouvel_entretien_bb, prochain_entretien_tb, prochain_entretien_bb, user_id
            ) VALUES (
                :mois_rapport, :item, :matricule, :nom_bac, :type_bac, :num_contrat, 
                :identification_materiel, :delai_debut, :delai_fin, :observation_statut, 
                :nouvel_entretien_tb, :nouvel_entretien_bb, :prochain_entretien_tb, :prochain_entretien_bb, :user_id
            )";

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([
        ':mois_rapport' => report_month_label($mois_rapport),
        ':item' => $item,
        ':matricule' => $matricule,
        ':nom_bac' => $nom_bac,
        ':type_bac' => $type_bac,
        ':num_contrat' => $num_contrat,
        ':identification_materiel' => $identification_materiel,
        ':delai_debut' => $delai_debut,
        ':delai_fin' => $delai_fin,
        ':observation_statut' => $observation_statut,
        ':nouvel_entretien_tb' => $nouvel_entretien_tb,
        ':nouvel_entretien_bb' => $nouvel_entretien_bb,
        ':prochain_entretien_tb' => $prochain_entretien_tb,
        ':prochain_entretien_bb' => $prochain_entretien_bb,
        ':user_id' => $user_id
    ])) {
        header('Location: afficher_fiche.php?mois=' . rawurlencode($mois_rapport));
        exit;
    } else {
        $message = "Erreur lors de l'enregistrement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Rapport Modèle DMB</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>

<div class="entry-container">
    <h2>Nouvel enregistrement - Rapport DMB</h2>
    <?php if ($message): ?>
        <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="grid">
            <div>
                <label for="mois_rapport">Mois et année du rapport :</label>
                <input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required>
            </div>
            <div>
                <label>Item :</label>
                <input type="text" name="item" placeholder="Ex: 01" required>
            </div>
            <div>
                <label>Matricule :</label>
                <input type="text" name="matricule" placeholder="Ex: A23678" required>
            </div>
            <div>
                <label>Nom du Bac :</label>
                <input type="text" name="nom_bac" placeholder="Ex: KISANGANI III" required>
            </div>
            <div>
                <label>Type :</label>
                <input type="text" name="type_bac" placeholder="Ex: AMB/M" required>
            </div>
            <div>
                <label>N° Contrat :</label>
                <input type="text" name="num_contrat" value="-">
            </div>
            <div class="full">
                <label>Identification du matériel / Travaux (Entretien courant) :</label>
                <textarea name="identification_materiel" rows="4" placeholder="- Nettoyage du circuit de refroidissement...&#10;- Serrage des boulonneries..." required></textarea>
            </div>
            <div>
                <label>Délai d'exécution - Début :</label>
                <input type="date" name="delai_debut">
            </div>
            <div>
                <label>Délai d'exécution - Fin :</label>
                <input type="date" name="delai_fin">
            </div>
        </div>

        <fieldset>
            <legend>Observation / Niveau d'exécution</legend>
            <div class="grid">
                <div class="full">
                    <label>Observation simple (si pas d'index moteur, ex: RAS) :</label>
                    <input type="text" name="observation_statut" placeholder="Laisser vide si vous remplissez les index ci-dessous">
                </div>
                <div>
                    <label>Nouvel entretien - Index Moteur Tb :</label>
                    <input type="text" name="nouvel_entretien_tb" placeholder="Ex: 56h02'">
                </div>
                <div>
                    <label>Nouvel entretien - Index Moteur Bb :</label>
                    <input type="text" name="nouvel_entretien_bb" placeholder="Ex: 56h10'">
                </div>
                <div>
                    <label>Prochain entretien - Index Moteur Tb :</label>
                    <input type="text" name="prochain_entretien_tb" placeholder="Ex: dans 29h46'">
                </div>
                <div>
                    <label>Prochain entretien - Index Moteur Bb :</label>
                    <input type="text" name="prochain_entretien_bb" placeholder="Ex: dans 29h34'">
                </div>
            </div>
        </fieldset>

        <button type="submit">Enregistrer dans la fiche</button>
    </form>
</div>

<?php require 'footer.php'; ?>
</body>
</html>