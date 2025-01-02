# Step 1: Use a PHP image with Apache web server
FROM php:8.1-apache

# Step 2: Copy your PHP files into the container
COPY . /var/www/html/

# Step 3: Expose port 80 (default for HTTP)
EXPOSE 80
