<header class="app-header no-print">
    <div class="app-brand">
        <h1>GESTION DE RAPPORT MATERIELLE FLOTANT</h1>
    </div>
    <?php
    if (!in_array(($fiche_courante ?? ''), ['dashboard', 'auth'], true)) {
        require __DIR__ . '/navigation_fiches.php';
    }
    ?>
</header>
