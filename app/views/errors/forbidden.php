<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-md-6 text-center">
            <i class="bi bi-shield-x text-danger" style="font-size: 6rem;"></i>
            <h1 class="display-4 mt-4">403</h1>
            <h2>Acceso Denegado</h2>
            <p class="text-muted"><?= e($message ?? 'No tienes permisos para acceder a esta sección.') ?></p>
            <a href="<?= BASE_URL ?>/dashboard" class="btn btn-primary mt-3">
                <i class="bi bi-house"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
