#!/bin/bash

# E-Commerce Production Deployment Script
# This script prepares and deploys the Laravel e-commerce application

set -e  # Exit on any error

echo "🚀 Starting E-Commerce Production Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root"
    exit 1
fi

# 1. Environment Setup
echo "📋 Setting up environment..."

if [ ! -f ".env" ]; then
    print_status "Creating .env from .env.example"
    cp .env.example .env
    print_warning "Please update .env with your production settings"
    exit 1
fi

# Check environment variables
if [ "$APP_ENV" != "production" ]; then
    print_warning "Consider setting APP_ENV=production in .env"
fi

# 2. Dependencies Installation
echo "📦 Installing dependencies..."

print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

print_status "Installing Node.js dependencies..."
npm ci --production

# 3. Build Assets
echo "🎨 Building assets..."

print_status "Compiling and minifying assets..."
npm run build

# 4. Database Operations
echo "🗄️️  Setting up database..."

# Create backup before migrations
BACKUP_DIR="database/backups"
mkdir -p $BACKUP_DIR
BACKUP_FILE="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"

if [ "$DB_CONNECTION" = "mysql" ]; then
    print_status "Creating database backup..."
    mysqldump --single-transaction --routines --triggers --user="$DB_USERNAME" --password="$DB_PASSWORD" --host="$DB_HOST" "$DB_DATABASE" > "$BACKUP_FILE"
    print_status "Backup created: $BACKUP_FILE"
fi

print_status "Running migrations..."
php artisan migrate --force

print_status "Seeding data (if needed)..."
php artisan db:seed --class=TransactionSeeder --force

# 5. Cache Operations
echo "🚀 Optimizing performance..."

print_status "Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:clear

print_status "Creating production cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. File System Setup
echo "📁 Setting up file system..."

print_status "Creating storage links..."
php artisan storage:link

print_status "Setting correct permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 644 storage/logs/*.log 2>/dev/null || true

# 7. Security Hardening
echo "🔒 Security hardening..."

print_status "Optimizing Composer autoloader..."
composer dump-autoload --optimize --classmap-authoritative

print_status "Removing development dependencies..."
composer remove --dev --no-interaction

# 8. Performance Optimization
echo "⚡ Performance optimizations..."

print_status "Optimizing Composer..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-scripts

# Preload compiled files (if OPcache is enabled)
php artisan config:cache

# 9. Health Checks
echo "🏥 Running health checks..."

print_status "Checking application health..."
php artisan about --only=environment,cache,database

# Check if critical services are running
if command -v redis-cli >/dev/null 2>&1; then
    if redis-cli ping >/dev/null 2>&1; then
        print_status "Redis is running"
    else
        print_error "Redis is not running - please start Redis service"
    fi
fi

# Check web server configuration
print_status "Checking web server configuration..."
if [ -f "/etc/nginx/sites-available/ecommerce" ]; then
    print_status "Nginx configuration found"
elif [ -f "/etc/apache2/sites-available/ecommerce.conf" ]; then
    print_status "Apache configuration found"
else
    print_warning "Web server configuration not found - please configure Nginx or Apache"
fi

# 10. Final Checks
echo "✨ Final deployment checks..."

# Verify key files exist
REQUIRED_FILES=[
    ".env"
    "public/index.php"
    "storage/links"
    "bootstrap/cache"
]

for file in "${REQUIRED_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_status "✓ $file exists"
    else
        print_error "✗ $file missing"
    fi
done

# 11. Services Restart (if needed)
echo "🔄 Service restart recommendations..."

echo "Please restart the following services:"
echo "- PHP-FPM: sudo systemctl restart php-fpm"
echo "- Web Server: sudo systemctl restart nginx (or apache2)"
echo "- Redis: sudo systemctl restart redis (if using Redis)"

# 12. Post-Deployment Instructions
echo ""
echo "🎉 Deployment completed successfully!"
echo ""
echo "📋 Post-Deployment Checklist:"
echo "□ Test all application features"
echo "□ Verify checkout process works"
echo "□ Test admin panel functionality"
echo "□ Check email delivery"
echo "□ Monitor error logs"
echo "□ Set up monitoring and alerts"
echo "□ Configure backup automation"
echo ""
echo "📊 Useful Commands:"
echo "View logs: tail -f storage/logs/laravel.log"
echo "Queue status: php artisan queue:monitor"
echo "Cache status: php artisan cache:info"
echo "Clear cache: php artisan cache:clear"
echo ""
echo "🔗 Important URLs:"
echo "Application: $APP_URL"
echo "Admin Panel: $APP_URL/admin"
echo "Health Check: $APP_URL/up"
echo ""

print_status "Deployment script completed successfully!"