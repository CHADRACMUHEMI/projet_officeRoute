<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

$message = "";
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = htmlspecialchars($_POST['item']);
    $nom_bac = htmlspecialchars($_POST['nom_bac']);
    $matricule = htmlspecialchars($_POST['matricule']);
    $nb_jour_traversee = !empty($_POST['nb_jour_traversee']) ? htmlspecialchars($_POST['nb_jour_traversee']) : '-';
    
    // Liste complète des champs de trafic incluant mto et mto_charge séparément
    $champs_trafic = ['pto', 'vlo', 'con', 'stru', 'ct', 'ion', 'vle', 'mto', 'mto_charge', 'mto_gizi', 'mto_gizi_charge', 'vmo', 'neu', 've', 'cam', 'pld', 'vtp', 'crm', 'vache'];
    $valeurs_trafic = [];
    foreach ($champs_trafic as $champ) {
        $valeurs_trafic[$champ] = !empty($_POST[$champ]) ? htmlspecialchars($_POST[$champ]) : '-';
    }
    
    $obs = !empty($_POST['obs']) ? htmlspecialchars($_POST['obs']) : 'RAS';
    $user_id = $_SESSION['user_id'] ?? null;

    $sql = "INSERT INTO trafics_bacs (
                mois_rapport, item, nom_bac, matricule, nb_jour_traversee, 
                pto, vlo, con, stru, ct, ion, vle, mto, mto_charge, mto_gizi, mto_gizi_charge, 
                vmo, neu, ve, cam, pld, vtp, crm, vache, obs, user_id
            ) VALUES (
                :mois_rapport, :item, :nom_bac, :matricule, :nb_jour_traversee, 
                :pto, :vlo, :con, :stru, :ct, :ion, :vle, :mto, :mto_charge, :mto_gizi, :mto_gizi_charge, 
                :vmo, :neu, :ve, :cam, :pld, :vtp, :crm, :vache, :obs, :user_id
            )";

    $stmt = $pdo->prepare($sql);
    $params = array_merge([
        ':mois_rapport' => report_month_label($mois_rapport),
        ':item' => $item,
        ':nom_bac' => $nom_bac,
        ':matricule' => $matricule,
        ':nb_jour_traversee' => $nb_jour_traversee,
        ':obs' => $obs,
        ':user_id' => $user_id
    ], array_combine(array_map(fn($k) => ":$k", array_keys($valeurs_trafic)), array_values($valeurs_trafic)));

    if ($stmt->execute($params)) {
        header('Location: afficher_trafic.php?mois=' . rawurlencode($mois_rapport));
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
    <title>Saisie - Rapport Trafics Bacs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>

<div class="entry-container">
    <h2>Saisie - Rapport Mensuel Trafics sur les Bacs</h2>
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
                <label>Item (It.) :</label>
                <input type="text" name="item" placeholder="Ex: 01" required>
            </div>
            <div>
                <label>Nom du Bac :</label>
                <input type="text" name="nom_bac" placeholder="Ex: BANALIA" required>
            </div>
            <div>
                <label>N° Matricule :</label>
                <input type="text" name="matricule" placeholder="Ex: A23597" required>
            </div>
            <div>
                <label>Nombre de jours traversée :</label>
                <input type="text" name="nb_jour_traversee" value="-" placeholder="Ex: EN, 30, ECHOUER...">
            </div>
            <div>
                <label>Observation (OBS) :</label>
                <input type="text" name="obs" value="RAS" placeholder="Ex: RAS, PR, PAS...">
            </div>
        </div>

        <fieldset>
            <legend>Catégorie et Nombre (Trafics)</legend>
            <div class="grid-3">
                <div><label>PTO :</label><input type="text" name="pto" value="-"></div>
                <div><label>VLO :</label><input type="text" name="vlo" value="-"></div>
                <div><label>CON :</label><input type="text" name="con" value="-"></div>
                <div><label>STRU :</label><input type="text" name="stru" value="-"></div>
                <div><label>CT :</label><input type="text" name="ct" value="-"></div>
                <div><label>ION :</label><input type="text" name="ion" value="-"></div>
                <div><label>VLE :</label><input type="text" name="vle" value="-"></div>
                <div><label>MTO (Moto) :</label><input type="text" name="mto" value="-"></div>
                <div><label>MTO CHARGE (Moto chargée) :</label><input type="text" name="mto_charge" value="-"></div>
                <div><label>MTO GIZI :</label><input type="text" name="mto_gizi" value="-"></div>
                <div><label>MTO GIZI CHARGE :</label><input type="text" name="mto_gizi_charge" value="-"></div>
                <div><label>VMO :</label><input type="text" name="vmo" value="-"></div>
                <div><label>NEU :</label><input type="text" name="neu" value="-"></div>
                <div><label>VE :</label><input type="text" name="ve" value="-"></div>
                <div><label>CAM :</label><input type="text" name="cam" value="-"></div>
                <div><label>PLD :</label><input type="text" name="pld" value="-"></div>
                <div><label>VTP :</label><input type="text" name="vtp" value="-"></div>
                <div><label>CRM :</label><input type="text" name="crm" value="-"></div>
                <div><label>Vache :</label><input type="text" name="vache" value="-"></div>
            </div>
        </fieldset>

        <button type="submit">Enregistrer le trafic</button>
    </form>
</div>

<?php require 'footer.php'; ?>
</body>
</html>