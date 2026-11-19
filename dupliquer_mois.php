<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$fiches = [
    'travaux' => [
        'titre' => 'Fiche des travaux',
        'table' => 'travaux_bacs',
        'colonnes' => 'item, matricule, nom_bac, type_bac, num_contrat, identification_materiel, delai_debut, delai_fin, observation_statut, nouvel_entretien_tb, nouvel_entretien_bb, prochain_entretien_tb, prochain_entretien_bb, user_id'
    ],
    'trafics' => [
        'titre' => 'Fiche des trafics',
        'table' => 'trafics_bacs',
        'colonnes' => 'item, nom_bac, matricule, nb_jour_traversee, pto, vlo, con, stru, ct, ion, vle, mto, mto_charge, mto_gizi, mto_gizi_charge, vmo, neu, ve, cam, pld, vtp, crm, vache, obs, user_id'
    ],
    'consommation' => [
        'titre' => 'Fiche de consommation',
        'table' => 'consommation_bacs',
        'colonnes' => 'item, matricule, nom_bac, type_bac, carb_or, carb_tiers, carb_total, carb_essence, lub_or_1, lub_sae40, lub_or_2, lub_tiers, lub_sae50, lub_sae10, liquide_frein, graisse, atf, eau_vive, antigel, heures_bb, heures_tb, jour_bb, jour_tb'
    ],
    'cout' => [
        'titre' => "Fiche des coûts d'exploitation",
        'table' => 'cout_exploitation_bacs',
        'colonnes' => 'item, matricule, nom_bac, recette_bac, etat_fin_mois, charge_personnel, charge_entretien, fonctionnement, total_fc, observ'
    ]
];

$type_fiche = $_POST['type_fiche'] ?? $_GET['type'] ?? '';
$source_mois = trim($_POST['source_mois'] ?? $_GET['source'] ?? '');
$nouveau_mois = trim($_POST['nouveau_mois'] ?? '');
$message = '';
$erreur = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($fiches[$type_fiche])) {
        $message = 'Veuillez choisir une fiche valide.';
        $erreur = true;
    } elseif ($source_mois === '' || $nouveau_mois === '') {
        $message = 'Veuillez renseigner le mois source et le nouveau mois.';
        $erreur = true;
    } elseif (strlen($source_mois) > 100 || strlen($nouveau_mois) > 100) {
        $message = 'Les noms de période ne peuvent pas dépasser 100 caractères.';
        $erreur = true;
    } elseif (strcasecmp($source_mois, $nouveau_mois) === 0) {
        $message = 'Le mois source et le nouveau mois doivent être différents.';
        $erreur = true;
    } else {
        $fiche = $fiches[$type_fiche];
        $table = $fiche['table'];
        $colonnes = $fiche['colonnes'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM `$table` WHERE LOWER(TRIM(mois_rapport)) = LOWER(TRIM(:mois))"
            );
            $stmt->execute([':mois' => $source_mois]);
            $nombre_source = (int) $stmt->fetchColumn();

            if ($nombre_source === 0) {
                $message = 'Aucune ligne trouvée pour le mois source.';
                $erreur = true;
            } else {
                $stmt->execute([':mois' => $nouveau_mois]);
                $nombre_destination = (int) $stmt->fetchColumn();

                if ($nombre_destination > 0) {
                    $message = 'Le mois de destination contient déjà des lignes. Aucune copie n’a été effectuée.';
                    $erreur = true;
                } else {
                    $stmt = $pdo->prepare(
                        "INSERT INTO `$table` (mois_rapport, $colonnes)
                         SELECT :nouveau_mois, $colonnes
                         FROM `$table`
                         WHERE LOWER(TRIM(mois_rapport)) = LOWER(TRIM(:source_mois))"
                    );
                    $stmt->execute([
                        ':nouveau_mois' => $nouveau_mois,
                        ':source_mois' => $source_mois
                    ]);
                    $nombre_copie = $stmt->rowCount();
                    $message = $nombre_copie . ' ligne(s) copiée(s) vers ' . $nouveau_mois . '. L’ancien mois est conservé.';
                }
            }

            $pdo->commit();
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erreur lors de la copie d’un rapport mensuel : ' . $e->getMessage());
            $message = 'Une erreur est survenue pendant la copie. Aucune donnée n’a été copiée.';
            $erreur = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un rapport à partir d’un mois existant</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="entry-page">
<main class="entry-container">
    <nav class="site-nav" aria-label="Navigation des fiches">
        <a href="dashboard.php">Tableau de bord</a>
        <a href="afficher_fiche.php">Fiche des travaux</a>
        <a href="afficher_trafic.php">Fiche des trafics</a>
        <a href="afficher_consommation.php">Fiche de consommation</a>
        <a href="afficher_cout.php">Fiche des coûts</a>
    </nav>

    <h2>Créer un rapport pour un nouveau mois</h2>
    <p>Cette fonction convient lorsque les données restent identiques d’un mois à l’autre : les lignes du mois source seront copiées, et l’ancien rapport restera inchangé. Choisissez un mois de destination sans données pour éviter les doublons.</p>

    <?php if ($message): ?>
        <div class="alert <?= $erreur ? 'alert-error' : '' ?>" role="<?= $erreur ? 'alert' : 'status' ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div class="full">
                <label for="type_fiche">Fiche à renouveler :</label>
                <select id="type_fiche" name="type_fiche" required>
                    <option value="">Choisir une fiche</option>
                    <?php foreach ($fiches as $type => $fiche): ?>
                        <option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" <?= $type_fiche === $type ? 'selected' : '' ?>>
                            <?= htmlspecialchars($fiche['titre'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="source_mois">Mois source :</label>
                <input id="source_mois" type="text" name="source_mois" value="<?= htmlspecialchars($source_mois, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex : SEPTEMBRE 2026" maxlength="100" required>
            </div>
            <div>
                <label for="nouveau_mois">Nouveau mois :</label>
                <input id="nouveau_mois" type="text" name="nouveau_mois" value="<?= htmlspecialchars($nouveau_mois, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex : OCTOBRE 2026" maxlength="100" required>
            </div>
        </div>
        <button type="submit">Copier les lignes vers le nouveau mois</button>
    </form>
</main>
</body>
</html>
