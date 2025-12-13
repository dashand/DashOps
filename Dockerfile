FROM php:8.2-apache

# Install dependencies for PHP extensions (PostgreSQL and LDAP)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libldap2-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install pdo pdo_pgsql ldap

# Enable Apache mod_rewrite and mod_ssl
RUN a2enmod rewrite ssl

# Generate self-signed certificate for "native" HTTPS
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/ssl-cert-snakeoil.key \
    -out /etc/ssl/certs/ssl-cert-snakeoil.pem \
    -subj "/C=FR/ST=France/L=Paris/O=DashOps/OU=IT/CN=localhost"

# Configure Apache to use SSL and disable HTTP (port 80)
RUN a2ensite default-ssl \
    && a2dissite 000-default

# Copy application source code to web root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Fix permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html


# Expose port 443 (HTTPS)
EXPOSE 443
