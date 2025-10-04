<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 text-center">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 6rem;"></i>
            <h1 class="display-4 mt-4">404</h1>
            <h2>Página No Encontrada</h2>
            <p class="text-muted">La página que buscas no existe o ha sido movida.</p>
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary mt-3">
                <i class="bi bi-house"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
