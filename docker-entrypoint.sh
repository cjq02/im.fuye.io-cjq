#!/bin/bash
set -e

# 设置权限
chown -R www-data:www-data /var/www/im.fuye.io || true
chmod -R 755 /var/www/im.fuye.io || true
chmod -R 777 /var/www/im.fuye.io/runtime || true

# 启动 GatewayWorker（如果存在）
if [ -f "/var/www/im.fuye.io/addons/mdkeji_im/extend/GatewayWorker/start.php" ]; then
    php /var/www/im.fuye.io/addons/mdkeji_im/extend/GatewayWorker/start.php start -d || echo "GatewayWorker start failed, continuing..."
fi

# 执行传入的命令（默认是 php-fpm）
exec "$@"

