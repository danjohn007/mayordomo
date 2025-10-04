-- Add missing settings to global_settings table
USE aqh_mayordomo;

-- Add bank accounts info setting (separate from json bank_accounts)
INSERT INTO global_settings (setting_key, setting_value, setting_type, description, category)
VALUES
    ('bank_accounts_info', '', 'string', 'Información de Cuentas Bancarias para Depósitos', 'payment'),
    ('terms_and_conditions', '', 'string', 'Términos y Condiciones del Sistema', 'legal')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description),
    category = VALUES(category);
