<?php
/**
 * Test Connection Script
 * Verifies database connection and base URL configuration
 */

require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - MajorBot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><i class="bi bi-activity"></i> Test de Conexión MajorBot</h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- PHP Version -->
                        <div class="mb-4">
                            <h5><i class="bi bi-code-slash"></i> Versión de PHP</h5>
                            <div class="alert <?= version_compare(PHP_VERSION, '7.0.0', '>=') ? 'alert-success' : 'alert-danger' ?>">
                                <strong>PHP <?= PHP_VERSION ?></strong>
                                <?php if (version_compare(PHP_VERSION, '7.0.0', '>=')): ?>
                                    <i class="bi bi-check-circle-fill float-end"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill float-end"></i>
                                    <p class="mb-0 mt-2">Se requiere PHP 7.0 o superior</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Base URL -->
                        <div class="mb-4">
                            <h5><i class="bi bi-link-45deg"></i> URL Base</h5>
                            <div class="alert alert-info">
                                <strong><?= BASE_URL ?></strong>
                                <i class="bi bi-check-circle-fill float-end"></i>
                            </div>
                        </div>
                        
                        <!-- Database Connection -->
                        <div class="mb-4">
                            <h5><i class="bi bi-database"></i> Conexión a Base de Datos</h5>
                            <?php
                            try {
                                require_once 'config/database.php';
                                $db = Database::getInstance()->getConnection();
                                
                                // Test query
                                $stmt = $db->query("SELECT VERSION() as version");
                                $mysqlVersion = $stmt->fetch();
                                
                                echo '<div class="alert alert-success">';
                                echo '<strong>✓ Conexión exitosa</strong><i class="bi bi-check-circle-fill float-end"></i>';
                                echo '<p class="mb-0 mt-2">MySQL Versión: ' . $mysqlVersion['version'] . '</p>';
                                echo '<p class="mb-0">Host: ' . DB_HOST . '</p>';
                                echo '<p class="mb-0">Base de Datos: ' . DB_NAME . '</p>';
                                echo '</div>';
                                
                                // Check if tables exist
                                $stmt = $db->query("SHOW TABLES");
                                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                
                                if (count($tables) > 0) {
                                    echo '<div class="alert alert-success">';
                                    echo '<strong>✓ Base de datos configurada</strong>';
                                    echo '<p class="mb-0 mt-2">Tablas encontradas: ' . count($tables) . '</p>';
                                    echo '<ul class="mb-0 mt-2">';
                                    foreach ($tables as $table) {
                                        echo '<li>' . $table . '</li>';
                                    }
                                    echo '</ul>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="alert alert-warning">';
                                    echo '<strong>⚠ Base de datos vacía</strong>';
                                    echo '<p class="mb-0 mt-2">No se encontraron tablas. Por favor ejecuta el script schema.sql</p>';
                                    echo '</div>';
                                }
                                
                            } catch (PDOException $e) {
                                echo '<div class="alert alert-danger">';
                                echo '<strong>✗ Error de conexión</strong><i class="bi bi-x-circle-fill float-end"></i>';
                                echo '<p class="mb-0 mt-2">Error: ' . $e->getMessage() . '</p>';
                                echo '<p class="mb-0 mt-2"><strong>Solución:</strong></p>';
                                echo '<ol>';
                                echo '<li>Verifica que MySQL esté ejecutándose</li>';
                                echo '<li>Verifica las credenciales en config/config.php</li>';
                                echo '<li>Crea la base de datos ejecutando: <code>mysql -u root -p < database/schema.sql</code></li>';
                                echo '</ol>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Required Extensions -->
                        <div class="mb-4">
                            <h5><i class="bi bi-puzzle"></i> Extensiones PHP Requeridas</h5>
                            <?php
                            $required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl'];
                            $allInstalled = true;
                            
                            foreach ($required as $ext) {
                                $installed = extension_loaded($ext);
                                if (!$installed) $allInstalled = false;
                                
                                echo '<div class="alert ' . ($installed ? 'alert-success' : 'alert-danger') . ' py-2">';
                                echo '<strong>' . $ext . '</strong>';
                                echo $installed ? ' <i class="bi bi-check-circle-fill float-end"></i>' : ' <i class="bi bi-x-circle-fill float-end"></i>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Directory Permissions -->
                        <div class="mb-4">
                            <h5><i class="bi bi-folder"></i> Permisos de Directorios</h5>
                            <?php
                            $dirs = [
                                ROOT_PATH . '/public',
                                ROOT_PATH . '/config'
                            ];
                            
                            foreach ($dirs as $dir) {
                                $writable = is_writable($dir);
                                echo '<div class="alert ' . ($writable ? 'alert-success' : 'alert-warning') . ' py-2">';
                                echo '<strong>' . basename($dir) . '</strong>';
                                echo $writable ? ' (escribible) <i class="bi bi-check-circle-fill float-end"></i>' : ' (solo lectura) <i class="bi bi-exclamation-triangle-fill float-end"></i>';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Instructions -->
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> Próximos Pasos</h5>
                            <ol class="mb-0">
                                <li>Si aún no has creado la base de datos, ejecuta:
                                    <br><code>mysql -u root -p < database/schema.sql</code>
                                </li>
                                <li>Para cargar datos de ejemplo, ejecuta:
                                    <br><code>mysql -u root -p < database/sample_data.sql</code>
                                </li>
                                <li>Accede a la aplicación:
                                    <br><a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                                    </a>
                                </li>
                            </ol>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
