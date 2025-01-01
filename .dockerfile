# Use the official PHP image with Apache
FROM php:8.1-apache

# Install dependencies (optional if needed for your project)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your project files into the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html

# Expose port 80 for web traffic
EXPOSE 80
