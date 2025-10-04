<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-gear"></i> Configuración Global del Sistema</h1>
            
            <?php if ($flash = flash('success')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($flash = flash('error')): ?>
                <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
                    <?= $flash['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?= BASE_URL ?>/superadmin/settings">
                <!-- Payment Configuration -->
                <?php if (isset($settingsByCategory['payment'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Configuración de Pagos</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['payment'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="setting_<?= e($setting['setting_key']) ?>" class="form-select">
                                        <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Habilitado</option>
                                        <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Deshabilitado</option>
                                    </select>
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input type="number" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>" step="0.01">
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Email/SMTP Configuration -->
                <?php if (isset($settingsByCategory['email'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-envelope"></i> Configuración de Email (SMTP)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['email'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="setting_<?= e($setting['setting_key']) ?>" class="form-select">
                                        <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Habilitado</option>
                                        <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Deshabilitado</option>
                                    </select>
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input type="number" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php elseif ($setting['setting_key'] === 'smtp_password'): ?>
                                    <input type="password" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>" 
                                           placeholder="********">
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Loyalty Program Configuration -->
                <?php if (isset($settingsByCategory['loyalty'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-star"></i> Programa de Lealtad</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['loyalty'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="setting_<?= e($setting['setting_key']) ?>" class="form-select">
                                        <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Habilitado</option>
                                        <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Deshabilitado</option>
                                    </select>
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input type="number" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>" step="0.01">
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Financial Configuration -->
                <?php if (isset($settingsByCategory['financial'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Configuración Financiera</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['financial'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="setting_<?= e($setting['setting_key']) ?>" class="form-select">
                                        <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Habilitado</option>
                                        <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Deshabilitado</option>
                                    </select>
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input type="number" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>" step="0.01">
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Site Information -->
                <?php if (isset($settingsByCategory['site'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-globe"></i> Información del Sitio</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['site'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_key'] === 'site_description'): ?>
                                    <textarea name="setting_<?= e($setting['setting_key']) ?>" 
                                              class="form-control" rows="3"><?= e($setting['setting_value']) ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Subscription Configuration -->
                <?php if (isset($settingsByCategory['subscription'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Configuración de Suscripciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['subscription'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="setting_<?= e($setting['setting_key']) ?>" class="form-select">
                                        <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Habilitado</option>
                                        <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Deshabilitado</option>
                                    </select>
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input type="number" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>" step="0.01">
                                <?php elseif (strpos($setting['setting_key'], 'date') !== false): ?>
                                    <input type="date" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- WhatsApp Configuration -->
                <?php if (isset($settingsByCategory['whatsapp'])): ?>
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-whatsapp"></i> Configuración de WhatsApp</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($settingsByCategory['whatsapp'] as $setting): ?>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <?= e($setting['description']) ?>
                                </label>
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                    <select name="setting_<?= e($setting['setting_key']) ?>" class="form-select">
                                        <option value="1" <?= $setting['setting_value'] == '1' ? 'selected' : '' ?>>Habilitado</option>
                                        <option value="0" <?= $setting['setting_value'] == '0' ? 'selected' : '' ?>>Deshabilitado</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" name="setting_<?= e($setting['setting_key']) ?>" 
                                           class="form-control" value="<?= e($setting['setting_value']) ?>" 
                                           placeholder="Ej: +52 1 999 123 4567">
                                <?php endif; ?>
                                <small class="text-muted">Clave: <?= e($setting['setting_key']) ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Save Button -->
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> Guardar Configuración
                        </button>
                        <a href="<?= BASE_URL ?>/superadmin" class="btn btn-secondary btn-lg">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
