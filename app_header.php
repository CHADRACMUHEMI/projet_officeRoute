<header class="app-header no-print">
    <div class="app-brand">
        <h1>GESTION DE RAPPORT MATERIELLE FLOTANT</h1>
    </div>
    <?php
    if (($fiche_courante ?? '') !== 'dashboard') {
        require __DIR__ . '/navigation_fiches.php';
    }
    ?>
</header>
