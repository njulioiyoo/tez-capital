FROM php:8.2-apache

# Install dependencies including Node.js
RUN apt-get update && \
    apt-get install -y wget git zip unzip zlib1g-dev libzip-dev libpng-dev \
    curl libcurl4-openssl-dev libjpeg-dev libfreetype6-dev libgmp-dev libpq-dev \
    supervisor inotify-tools

# Install Node.js 18
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) mysqli pdo_mysql pdo_pgsql gmp
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd zip pcntl exif curl

# Enable Apache modules
RUN a2enmod rewrite

# Set ServerName to avoid warnings
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy custom VirtualHost config
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy docker startup script
COPY docker-start.sh /usr/local/bin/docker-start.sh

# Make startup script executable
RUN chmod +x /usr/local/bin/docker-start.sh

# Set working directory
WORKDIR /var/www/html

# Expose ports
EXPOSE 80 5173

# Start services
CMD ["/usr/local/bin/docker-start.sh"]
