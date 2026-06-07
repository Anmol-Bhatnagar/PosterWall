#!/bin/bash
# ============================================================
# PosterWall v2.0 — AWS Ubuntu Setup Script
# Usage: sudo bash setup.sh
# ============================================================

set -e  # Exit on error

echo "🚀 PosterWall v2.0 — AWS Ubuntu Setup Script"
echo "============================================================"

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo "❌ This script must be run as root (use sudo)"
   exit 1
fi

# Variables
DOMAIN="${1:-posterwall.in}"
DB_USER="posterwall_user"
DB_NAME="posterwall2"
APP_PATH="/var/www/posterwall"
LOG_PATH="/var/log/posterwall"

echo "📋 Configuration:"
echo "   Domain: $DOMAIN"
echo "   App Path: $APP_PATH"
echo "   Log Path: $LOG_PATH"
echo ""

# Step 1: Update system
echo "📦 Updating system packages..."
apt-get update && apt-get upgrade -y

# Step 2: Install PHP
echo "📦 Installing PHP 8.2..."
apt-get install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-curl php8.2-json php8.2-mbstring php8.2-xml
phpenmod curl mbstring
systemctl restart php8.2-fpm

# Step 3: Install Apache
echo "📦 Installing Apache..."
apt-get install -y apache2 libapache2-mod-php8.2
a2enmod rewrite headers ssl
systemctl restart apache2

# Step 4: Install MySQL (optional, skip if using RDS)
read -p "Install MySQL Server? (y/n) [n]: " install_mysql
if [ "$install_mysql" == "y" ]; then
    echo "📦 Installing MySQL..."
    apt-get install -y mysql-server
    
    read -sp "Enter MySQL root password: " mysql_root_pass
    echo ""
    
    # Create database and user
    read -sp "Enter posterwall database user password: " db_pass
    echo ""
    
    mysql -u root -p"$mysql_root_pass" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$db_pass';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    echo "✓ Database created: $DB_NAME"
    echo "✓ Database user: $DB_USER"
fi

# Step 5: Create directories
echo "📁 Creating directories..."
mkdir -p $APP_PATH
mkdir -p $LOG_PATH
mkdir -p /tmp/posterwall-sessions

chown -R www-data:www-data $APP_PATH
chown -R www-data:www-data $LOG_PATH
chown -R www-data:www-data /tmp/posterwall-sessions

chmod 755 $APP_PATH
chmod 755 $LOG_PATH
chmod 700 /tmp/posterwall-sessions

# Step 6: Clone/Copy application
echo "📂 Setting up application files..."
if [ ! -f "$APP_PATH/index.php" ]; then
    echo "ℹ️  Copy your application files to: $APP_PATH"
    echo "   Then run: sudo chown -R www-data:www-data $APP_PATH"
fi

# Step 7: Create .env file
echo "⚙️  Creating .env configuration..."
if [ ! -f "$APP_PATH/.env" ]; then
    cp "$APP_PATH/.env.example" "$APP_PATH/.env"
    chmod 600 "$APP_PATH/.env"
    
    echo "📝 Edit the .env file with your configuration:"
    echo "   nano $APP_PATH/.env"
else
    echo "✓ .env file already exists"
fi

# Step 8: Create Apache VirtualHost
echo "🌐 Configuring Apache VirtualHost..."
cat > /etc/apache2/sites-available/posterwall.conf <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    
    DocumentRoot $APP_PATH
    
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/css application/javascript
    </IfModule>
    
    <IfModule mod_headers.c>
        Header set X-Content-Type-Options "nosniff"
        Header set X-Frame-Options "SAMEORIGIN"
    </IfModule>
    
    <FilesMatch \.php\$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    ErrorLog $LOG_PATH/error.log
    CustomLog $LOG_PATH/access.log combined
    
    <Directory $APP_PATH>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Files "config.php">
        Require all denied
    </Files>
    <Files ".env">
        Require all denied
    </Files>
</VirtualHost>
EOF

a2ensite posterwall.conf
a2dissite 000-default 2>/dev/null || true
apache2ctl configtest

# Step 9: PHP Configuration
echo "⚙️  Optimizing PHP configuration..."
sed -i 's/^post_max_size = .*/post_max_size = 50M/' /etc/php/8.2/fpm/php.ini
sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.2/fpm/php.ini
sed -i 's/^display_errors = .*/display_errors = Off/' /etc/php/8.2/fpm/php.ini
sed -i 's/^log_errors = .*/log_errors = On/' /etc/php/8.2/fpm/php.ini
sed -i 's|^error_log = .*|error_log = '"$LOG_PATH"'/php_error.log|' /etc/php/8.2/fpm/php.ini

# Step 10: Firewall
echo "🔒 Configuring firewall..."
ufw allow 22/tcp 2>/dev/null || true
ufw allow 80/tcp 2>/dev/null || true
ufw allow 443/tcp 2>/dev/null || true
ufw --force enable 2>/dev/null || true

# Step 11: Restart services
echo "🔄 Restarting services..."
systemctl restart apache2
systemctl restart php8.2-fpm

# Step 12: SSL Setup (Certbot)
echo ""
read -p "Setup SSL with Certbot (Let's Encrypt)? (y/n) [y]: " setup_ssl
if [ "$setup_ssl" != "n" ]; then
    apt-get install -y certbot python3-certbot-apache
    certbot --apache -d $DOMAIN -d www.$DOMAIN
fi

echo ""
echo "============================================================"
echo "✅ Setup Complete!"
echo "============================================================"
echo ""
echo "📋 Next Steps:"
echo "   1. Edit the .env file: nano $APP_PATH/.env"
echo "   2. Import database: mysql -u $DB_USER -p $DB_NAME < $APP_PATH/db/setup.sql"
echo "   3. Test the site: https://$DOMAIN"
echo ""
echo "📚 Documentation: See DEPLOYMENT.md for detailed instructions"
echo "🔗 Domain: https://$DOMAIN"
echo ""
