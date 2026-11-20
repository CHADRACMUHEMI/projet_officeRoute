<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';
$mois_rapport = report_month_value($_POST['mois_rapport'] ?? $_GET['mois'] ?? '') ?: report_month_previous();

function extraire_qte_num($txt) {
    if (!$txt) return 0.0;
    $clean = preg_replace('/[\s\x{00A0}]+/u', '', trim((string)$txt));
    if (preg_match('/([0-9]+(?:[.,][0-9]+)?)/', $clean, $m)) {
        return floatval(str_replace(',', '.', $m[1]));
    }
    return 0.0;
}

function extraire_unite($txt) {
    if (!$txt) return '';
    if (preg_match('/[0-9]+(?:[.,][0-9]+)?\s*(.+)$/u', trim((string)$txt), $m)) {
        return trim($m[1]);
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $init_raw   = $_POST['stock_initial'] ?? '0';
    $entree_raw = $_POST['entree_qte'] ?? '0';
    $sortie_raw = $_POST['sortie_qte'] ?? '0';

    $init_val   = extraire_qte_num($init_raw);
    $entree_val = extraire_qte_num($entree_raw);
    $sortie_val = extraire_qte_num($sortie_raw);

    // Calcul automatique du stock fin de mois si non saisi manuellement
    $fin_saisi = trim($_POST['stock_fin_mois'] ?? '');
    if ($fin_saisi === '' || $fin_saisi === 'AUTO') {
        $fin_calc = $init_val + $entree_val - $sortie_val;
        $unite = extraire_unite($init_raw) ?: (extraire_unite($entree_raw) ?: extraire_unite($sortie_raw));
        $fin_formatted = rtrim(rtrim(number_format($fin_calc, 2, ',', ' '), '0'), ',');
        $stock_fin_final = ($fin_formatted === '' ? '0' : $fin_formatted) . ($unite ? ' ' . $unite : '');
    } else {
        $stock_fin_final = $fin_saisi;
    }

    $sql = "INSERT INTO mouvement_magasin_dmb003 (
        mois_rapport, modele_ref, item, marque_type, reference_art, designation,
        entree_qte, entree_prix, entree_fre_bad,
        sortie_qte, sortie_prix, sortie_client, sortie_nft, sortie_ds_bs,
        stock_initial, stock_fin_mois
    ) VALUES (
        :mois_rapport, :modele_ref, :item, :marque_type, :reference_art, :designation,
        :entree_qte, :entree_prix, :entree_fre_bad,
        :sortie_qte, :sortie_prix, :sortie_client, :sortie_nft, :sortie_ds_bs,
        :stock_initial, :stock_fin_mois
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':mois_rapport'   => report_month_label($mois_rapport),
        ':modele_ref'     => $_POST['modele_ref'] ?? 'RAPPORT MODELE DMB 003',
        ':item'           => $_POST['item'] ?? '',
        ':marque_type'    => $_POST['marque_type'] ?: '-//-//-',
        ':reference_art'  => $_POST['reference_art'] ?: '-',
        ':designation'    => $_POST['designation'] ?? '',
        ':entree_qte'     => $entree_raw ?: '0',
        ':entree_prix'    => $_POST['entree_prix'] ?: '-',
        ':entree_fre_bad' => $_POST['entree_fre_bad'] ?: '-//-//-',
        ':sortie_qte'     => $sortie_raw ?: '0',
        ':sortie_prix'    => $_POST['sortie_prix'] ?: '-',
        ':sortie_client'  => $_POST['sortie_client'] ?: 'OR',
        ':sortie_nft'     => $_POST['sortie_nft'] ?: '-',
        ':sortie_ds_bs'   => $_POST['sortie_ds_bs'] ?: '-//-//-',
        ':stock_initial'  => $init_raw ?: '0',
        ':stock_fin_mois' => $stock_fin_final
    ]);

    header('Location: afficher_mouvement_magasin.php?mois=' . rawurlencode($mois_rapport));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Saisie - Mouvement Mensuel Entrées & Sorties Magasin</title>
</head>
<body class="entry-page">
<?php require __DIR__ . '/app_header.php'; ?>
<main class="entry-container">
    <h2>Saisie - Mouvement Mensuel Entrées & Sorties Magasin (DMB 003)</h2>
    <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid grid-3">
            <div><label for="mois_rapport">Mois / Année :</label><input id="mois_rapport" type="month" name="mois_rapport" value="<?= htmlspecialchars($mois_rapport) ?>" required></div>
            <div><label>Réf. Modèle :</label><input type="text" name="modele_ref" value="RAPPORT MODELE DMB 003" required></div>
            <div><label>Item :</label><input type="text" name="item" placeholder="Ex: 1"></div>
        </div>

        <h3>Identification de la marchandise</h3>
        <div class="grid grid-3">
            <div><label>Marque Type :</label><input type="text" name="marque_type" value="-//-//-"></div>
            <div><label>Réf :</label><input type="text" name="reference_art" value="-"></div>
            <div><label>Désignation :</label><input type="text" name="designation" placeholder="Ex: Pompe à eau de mer + Rotor et peigne" required></div>
        </div>

        <h3>Mouvement en entrée</h3>
        <div class="grid grid-3">
            <div><label>Qté (Entrée) :</label><input type="text" name="entree_qte" placeholder="Ex: 01 Pce ou 03 Ram" value="0"></div>
            <div><label>Prix (Entrée) :</label><input type="text" name="entree_prix" placeholder="Ex: 350 $ ou 46.000 Fc" value="-"></div>
            <div><label>Fré ou Bad :</label><input type="text" name="entree_fre_bad" value="-//-//-"></div>
        </div>

        <h3>Mouvement en sortie</h3>
        <div class="grid grid-3">
            <div><label>Qté (Sortie) :</label><input type="text" name="sortie_qte" placeholder="Ex: 01 Pce" value="0"></div>
            <div><label>Prix (Sortie) :</label><input type="text" name="sortie_prix" placeholder="Ex: 350 $ ou 46.000 Fc" value="-"></div>
            <div><label>Client :</label><input type="text" name="sortie_client" value="OR"></div>
            <div><label>N°FT :</label><input type="text" name="sortie_nft" value="-"></div>
            <div class="full"><label>D.S. OU B.S :</label><input type="text" name="sortie_ds_bs" placeholder="Ex: 009/09/026" value="-//-//-"></div>
        </div>

        <h3>Stock</h3>
        <div class="grid grid-3">
            <div><label>Stock Initial :</label><input type="text" name="stock_initial" placeholder="Ex: 01 Pce" value="0"></div>
            <div class="full">
                <label>Stock Fin mois (laisser AUTO pour calcul automatique) :</label>
                <input type="text" name="stock_fin_mois" value="AUTO">
            </div>
        </main>

        <button type="submit" class="btn">Enregistrer le mouvement</button>
    </form>
</div>
<?php require 'footer.php'; ?>
</body>
</html>