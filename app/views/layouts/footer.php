    </main>
    
    <?php if (isLoggedIn()): ?>
    <!-- Footer -->
    <footer class="bg-light py-3 mt-5">
        <div class="container-fluid text-center text-muted">
            <small>&copy; <?= date('Y') ?> MajorBot - Sistema de Mayordomía Online v<?= APP_VERSION ?></small>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/public/js/app.js"></script>
    
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
</body>
</html>
