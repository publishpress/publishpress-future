#! /bin/bash

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

show_header() {
    echo -e "\n${BLUE}=============================================================="
    echo ""
    echo -e "🚀 $1"
}

show_status() {
    echo -e "${GREEN}✓ $1${NC}"
}

show_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

show_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Clean up the database
show_header "Cleaning up the database"
wp db reset --yes
show_status "Database reset completed successfully"

# Install WordPress
show_header "WordPress installation"
if wp core is-installed; then
    show_status "WordPress is already installed"
else
    wp core install --url="$1" --title="$2" --admin_user="admin" --admin_password="admin" --admin_email="test@example.com" --skip-email
    show_status "WordPress installed successfully"
fi

# Make sure WP is updated
show_header "Updating WordPress"
wp core update
show_status "WordPress core updated successfully"

# Make sure the database is up to date
show_header "Updating the database"
wp core update-db
show_status "Database updated successfully"

# Make sure all plugins are updated
show_header "Updating all plugins"
wp plugin update --all
show_status "All plugins updated successfully"

# Make sure all themes are updated
show_header "Updating all themes"
wp theme update --all
show_status "All themes updated successfully"

# Install Classic Editor
show_header "Installing Classic Editor"
wp plugin install classic-editor --activate
show_status "Classic Editor installed and activated"

# Install Spatie Ray as must-use plugin
show_header "Installing Spatie Ray as must-use plugin"
echo -e "${YELLOW}Cleaning up previous Spatie Ray installation...${NC}"
rm -rf /var/www/html/wp-content/mu-plugins/spatie-ray

echo -e "${YELLOW}Installing Spatie Ray plugin...${NC}"
wp plugin install spatie-ray --activate

echo -e "${YELLOW}Setting up as must-use plugin...${NC}"
mkdir -p /var/www/html/wp-content/mu-plugins
mv /var/www/html/wp-content/plugins/spatie-ray /var/www/html/wp-content/mu-plugins/spatie-ray
show_status "Spatie Ray configured as must-use plugin"

echo -e "${YELLOW}Configuring environment type in wp-config.php...${NC}"
wp config set WP_ENVIRONMENT_TYPE "local"
show_status "Environment type set to 'local'"

# Update MailHog options
show_header "Updating MailHog options"
wp config set WP_MAILHOG_SMTP_HOST "mailhog"
wp config set WP_MAILHOG_SMTP_PORT "1025"
wp config set WP_MAILHOG_MAIL_FROM "test@example.com"
wp config set WP_MAILHOG_MAIL_FROM_NAME "Test Email"
wp option update admin_email "test@example.com"
wp option update new_admin_email "test@example.com"
show_status "MailHog configuration completed"

show_header "Bypassing admin email verification"
# Bypass admin email verification
wp option update admin_email_lifespan 2060442225
show_status "Admin email verification bypassed"

# Update configuration
show_header "Updating configuration"
echo -e "${YELLOW}Setting debug options...${NC}"
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw
wp config set WP_DEBUG_DISPLAY false --raw
wp config set SCRIPT_DEBUG true --raw
show_status "Debug configuration completed"

# Activate Future Pro
show_header "Activating Future Pro"
wp plugin activate publishpress-future-pro
show_status "Future Pro activated successfully"

echo -e "\n${GREEN}================================================================"
echo -e "✨ WordPress environment setup completed successfully!"
echo -e "================================================================"
