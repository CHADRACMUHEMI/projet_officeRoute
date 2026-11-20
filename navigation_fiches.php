<?php
$navigation_fiches = [
    'travaux' => ['Fiche des travaux', 'afficher_fiche.php'],
    'trafics' => ['Fiche des trafics', 'afficher_trafic.php'],
    'consommation' => ['Fiche de consommation', 'afficher_consommation.php'],
    'cout' => ['Fiche des coûts', 'afficher_cout.php'],
    'actualites' => ['Actualités des bacs', 'afficher_actualites_bacs.php'],
    'commentaires' => ['Commentaires et travaux', 'afficher_commentaires_travaux.php'],
    'inventaire' => ['Inventaire atelier', 'afficher_inventaire_atelier.php'],
    'materiel_appui' => ['Matériel d’appui', 'afficher_materiel_appui.php'],
    'mouvement' => ['Mouvement carburant', 'afficher_mouvement.php'],
    'mouvement_magasin' => ['Mouvement magasin', 'afficher_mouvement_magasin.php'],
    'parc_flottant' => ['Parc flottant', 'afficher_parc_flottant.php'],
    'situation_parc' => ['Situation du parc bacs', 'afficher_situation_parc.php'],
    'synthese_flottants' => ['Synthèse des matériels flottants', 'afficher_synthese_flottants.php']
];
$navigation_fiche_courante = $fiche_courante ?? '';
$navigation_mois = $mois_picker ?? $mois_rapport ?? '';
$navigation_query = $navigation_mois !== '' ? '?mois=' . rawurlencode($navigation_mois) : '';
?>
<nav class="site-nav fiche-nav" aria-label="Navigation vers les autres fiches">
    <?php if ($navigation_fiche_courante !== 'dashboard'): ?>
        <a href="dashboard.php">Tableau de bord</a>
    <?php endif; ?>
    <?php foreach ($navigation_fiches as $type => $fiche): ?>
        <?php if ($type !== $navigation_fiche_courante): ?>
            <a href="<?= htmlspecialchars($fiche[1] . $navigation_query, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($fiche[0], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
