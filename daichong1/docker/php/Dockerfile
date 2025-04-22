# docker/php/Dockerfile
FROM php:7.4-fpm
# 安装依赖及扩展
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip mysqli

# 调整PHP配置
#RUN mv "\$PHP_INI_DIR/php.ini-production" "\$PHP_INI_DIR/php.ini"

COPY docker/php/conf.d/ /usr/local/etc/php/conf.d/

# 设置工作目录
WORKDIR /var/www/html
