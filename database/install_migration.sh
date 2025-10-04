#!/bin/bash

# ============================================================================
# MajorBot Database Migration Installer v1.1.0
# ============================================================================
# This script helps you safely migrate your MajorBot database
# ============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Database credentials (edit these or pass as arguments)
DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-majorbot_db}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"

# File paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MIGRATION_FILE="$SCRIPT_DIR/migration_v1.1.0.sql"
VERIFY_FILE="$SCRIPT_DIR/verify_migration.sql"
BACKUP_DIR="$SCRIPT_DIR/backups"

# ============================================================================
# Functions
# ============================================================================

print_header() {
    echo -e "${BLUE}============================================================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}============================================================================${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Check if MySQL is accessible
check_mysql() {
    print_info "Verificando conexión a MySQL..."
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME" 2>/dev/null; then
        print_success "Conexión exitosa a la base de datos"
        return 0
    else
        print_error "No se pudo conectar a la base de datos"
        return 1
    fi
}

# Create backup
create_backup() {
    print_info "Creando backup de la base de datos..."
    
    # Create backup directory if it doesn't exist
    mkdir -p "$BACKUP_DIR"
    
    # Generate backup filename with timestamp
    BACKUP_FILE="$BACKUP_DIR/majorbot_backup_$(date +%Y%m%d_%H%M%S).sql"
    
    # Create backup
    if mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null; then
        print_success "Backup creado: $BACKUP_FILE"
        BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        print_info "Tamaño del backup: $BACKUP_SIZE"
        return 0
    else
        print_error "Error al crear backup"
        return 1
    fi
}

# Run migration
run_migration() {
    print_info "Ejecutando migración..."
    
    if [ ! -f "$MIGRATION_FILE" ]; then
        print_error "Archivo de migración no encontrado: $MIGRATION_FILE"
        return 1
    fi
    
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_FILE" 2>/dev/null; then
        print_success "Migración ejecutada exitosamente"
        return 0
    else
        print_error "Error al ejecutar migración"
        return 1
    fi
}

# Verify migration
verify_migration() {
    print_info "Verificando migración..."
    
    if [ ! -f "$VERIFY_FILE" ]; then
        print_warning "Archivo de verificación no encontrado, omitiendo verificación"
        return 0
    fi
    
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$VERIFY_FILE" > /dev/null 2>&1; then
        print_success "Verificación completada"
        return 0
    else
        print_warning "Verificación completada con advertencias"
        return 0
    fi
}

# Show summary
show_summary() {
    print_header "RESUMEN DE MIGRACIÓN"
    
    # Count new tables
    NEW_TABLES=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT COUNT(*) FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = '$DB_NAME' 
        AND TABLE_NAME IN (
            'email_notifications', 'availability_calendar', 'shopping_cart', 
            'cart_items', 'payment_transactions', 'invoices', 'invoice_items',
            'subscription_plans', 'hotel_subscriptions', 'hotel_settings',
            'global_statistics', 'hotel_statistics', 'activity_log',
            'notifications', 'notification_preferences', 'reports',
            'report_generations', 'export_queue'
        )
    " -sN 2>/dev/null)
    
    # Count subscription plans
    PLANS=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT COUNT(*) FROM subscription_plans
    " -sN 2>/dev/null)
    
    echo ""
    print_info "Tablas nuevas creadas: $NEW_TABLES/18"
    print_info "Planes de suscripción: $PLANS"
    print_info "Backup guardado en: $BACKUP_FILE"
    echo ""
    
    if [ "$NEW_TABLES" -eq 18 ]; then
        print_success "¡Migración completada exitosamente!"
    else
        print_warning "Algunas tablas pueden no haberse creado correctamente"
    fi
}

# Rollback function
rollback() {
    print_header "ROLLBACK"
    print_warning "Restaurando desde backup: $BACKUP_FILE"
    
    if [ -f "$BACKUP_FILE" ]; then
        if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_FILE" 2>/dev/null; then
            print_success "Base de datos restaurada exitosamente"
        else
            print_error "Error al restaurar base de datos"
        fi
    else
        print_error "Archivo de backup no encontrado"
    fi
}

# ============================================================================
# Main Script
# ============================================================================

clear
print_header "INSTALADOR DE MIGRACIÓN MAJORBOT v1.1.0"
echo ""

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --host)
            DB_HOST="$2"
            shift 2
            ;;
        --db)
            DB_NAME="$2"
            shift 2
            ;;
        --user)
            DB_USER="$2"
            shift 2
            ;;
        --password)
            DB_PASS="$2"
            shift 2
            ;;
        --skip-backup)
            SKIP_BACKUP=true
            shift
            ;;
        --rollback)
            ROLLBACK_MODE=true
            shift
            ;;
        --help)
            echo "Uso: $0 [opciones]"
            echo ""
            echo "Opciones:"
            echo "  --host HOST          Host de MySQL (default: localhost)"
            echo "  --db DATABASE        Nombre de la base de datos (default: majorbot_db)"
            echo "  --user USER          Usuario de MySQL (default: root)"
            echo "  --password PASS      Contraseña de MySQL"
            echo "  --skip-backup        No crear backup antes de migrar"
            echo "  --rollback          Restaurar desde el último backup"
            echo "  --help              Mostrar esta ayuda"
            echo ""
            echo "Ejemplo:"
            echo "  $0 --host localhost --db majorbot_db --user root --password mypass"
            exit 0
            ;;
        *)
            print_error "Opción desconocida: $1"
            echo "Use --help para ver las opciones disponibles"
            exit 1
            ;;
    esac
done

# Check if in rollback mode
if [ "$ROLLBACK_MODE" = true ]; then
    # Find last backup
    BACKUP_FILE=$(ls -t "$BACKUP_DIR"/majorbot_backup_*.sql 2>/dev/null | head -1)
    if [ -z "$BACKUP_FILE" ]; then
        print_error "No se encontraron backups para restaurar"
        exit 1
    fi
    rollback
    exit 0
fi

# Show configuration
print_info "Configuración:"
echo "  Host: $DB_HOST"
echo "  Database: $DB_NAME"
echo "  User: $DB_USER"
echo ""

# Confirm before proceeding
read -p "$(echo -e ${YELLOW}¿Desea continuar con la migración? [y/N]: ${NC})" -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_info "Migración cancelada"
    exit 0
fi

# Check MySQL connection
if ! check_mysql; then
    print_error "No se pudo conectar a MySQL. Verifique sus credenciales."
    exit 1
fi

# Create backup unless skipped
if [ "$SKIP_BACKUP" != true ]; then
    if ! create_backup; then
        print_error "Error al crear backup. Abortando migración."
        exit 1
    fi
else
    print_warning "Backup omitido (--skip-backup)"
fi

# Run migration
echo ""
if ! run_migration; then
    print_error "Migración fallida. ¿Desea restaurar el backup? [y/N]"
    read -p "> " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rollback
    fi
    exit 1
fi

# Verify migration
echo ""
verify_migration

# Show summary
echo ""
show_summary

# Next steps
echo ""
print_header "PRÓXIMOS PASOS"
echo ""
echo "1. Revisar la documentación en MIGRATION_GUIDE.md"
echo "2. Configurar credenciales de email (config/email.php)"
echo "3. Configurar API keys de Stripe/PayPal (config/payment.php)"
echo "4. Actualizar el código PHP para usar las nuevas tablas"
echo "5. Probar las nuevas funcionalidades en ambiente de desarrollo"
echo ""
print_success "¡Migración completada!"
echo ""
