<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'db.php';

$message = '';

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
        ':mois_rapport'   => $_POST['mois_rapport'] ?? 'SEPTEMBRE 2026',
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

    $message = "Mouvement magasin enregistré avec succès ! (Stock fin de mois calculé : " . htmlspecialchars($stock_fin_final) . ")";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie - Mouvement Mensuel Entrées & Sorties Magasin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f9f9f9; }
        .container { max-width: 960px; margin: auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1); }
        h3 { margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; font-size: 14px; color: #0056b3; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 10px; }
        .full { grid-column: span 3; }
        label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px; }
        input { width: 100%; padding: 6px; box-sizing: border-box; }
        .btn { padding: 10px 16px; background: #0056b3; color: #fff; border: none; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .alert { padding: 10px; background: #d4edda; color: #155724; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Saisie - Mouvement Mensuel Entrées & Sorties Magasin (DMB 003)</h2>
    <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

    <form method="POST">
        <div class="grid">
            <div><label>Mois / Année :</label><input type="text" name="mois_rapport" value="SEPTEMBRE 2026" required></div>
            <div><label>Réf. Modèle :</label><input type="text" name="modele_ref" value="RAPPORT MODELE DMB 003" required></div>
            <div><label>Item :</label><input type="text" name="item" placeholder="Ex: 1"></div>
        </div>

        <h3>Identification de la marchandise</h3>
        <div class="grid">
            <div><label>Marque Type :</label><input type="text" name="marque_type" value="-//-//-"></div>
            <div><label>Réf :</label><input type="text" name="reference_art" value="-"></div>
            <div><label>Désignation :</label><input type="text" name="designation" placeholder="Ex: Pompe à eau de mer + Rotor et peigne" required></div>
        </div>

        <h3>Mouvement en entrée</h3>
        <div class="grid">
            <div><label>Qté (Entrée) :</label><input type="text" name="entree_qte" placeholder="Ex: 01 Pce ou 03 Ram" value="0"></div>
            <div><label>Prix (Entrée) :</label><input type="text" name="entree_prix" placeholder="Ex: 350 $ ou 46.000 Fc" value="-"></div>
            <div><label>Fré ou Bad :</label><input type="text" name="entree_fre_bad" value="-//-//-"></div>
        </div>

        <h3>Mouvement en sortie</h3>
        <div class="grid">
            <div><label>Qté (Sortie) :</label><input type="text" name="sortie_qte" placeholder="Ex: 01 Pce" value="0"></div>
            <div><label>Prix (Sortie) :</label><input type="text" name="sortie_prix" placeholder="Ex: 350 $ ou 46.000 Fc" value="-"></div>
            <div><label>Client :</label><input type="text" name="sortie_client" value="OR"></div>
            <div><label>N°FT :</label><input type="text" name="sortie_nft" value="-"></div>
            <div class="full"><label>D.S. OU B.S :</label><input type="text" name="sortie_ds_bs" placeholder="Ex: 009/09/026" value="-//-//-"></div>
        </div>

        <h3>Stock</h3>
        <div class="grid">
            <div><label>Stock Initial :</label><input type="text" name="stock_initial" placeholder="Ex: 01 Pce" value="0"></div>
            <div class="grid" style="grid-column: span 2; margin: 0;">
                <div style="grid-column: span 2;">
                    <label>Stock Fin mois (laisser AUTO pour calcul automatique) :</label>
                    <input type="text" name="stock_fin_mois" value="AUTO">
                </div>
            </div>
        </div>

        <button type="submit" class="btn">Enregistrer le mouvement</button>
        <a href="afficher_mouvement_magasin.php" style="margin-left: 15px;">Voir la fiche complète →</a>
    </form>
</div>
</body>
</html>