# Docker 路径统一说明

## 修改内容

为了统一开发环境和生产环境的路径，已将 Docker 容器内的项目路径从 `/var/www/html` 改为 `/var/www/im.fuye.io`。

## 已修改的文件

### 1. docker-compose.yml
- PHP 容器挂载路径：`./:/var/www/im.fuye.io`
- Nginx 容器挂载路径：`./:/var/www/im.fuye.io`
- GatewayWorker 启动路径更新
- 权限设置路径更新

### 2. nginx/conf.d/default.conf
- Nginx root 路径：`/var/www/im.fuye.io`

### 3. 代码文件
- `addons/mdkeji_im/core/crontab/telegram_sync.php`
- `addons/mdkeji_im/scripts/telegram/sync_messages.py`

所有路径已统一为 `/var/www/im.fuye.io`。

## 重启步骤

### 方法一：完全重启（推荐）

```bash
# 1. 停止所有容器
docker-compose down

# 2. 启动容器（会应用新的配置）
docker-compose up -d

# 3. 查看容器状态
docker-compose ps

# 4. 查看日志确认 GatewayWorker 启动成功
docker-compose logs -f php
```

### 方法二：重启单个容器

```bash
# 重启 PHP 容器（包括 GatewayWorker）
docker-compose restart php

# 重启 Nginx 容器
docker-compose restart nginx
```

## 验证路径

```bash
# 1. 进入 PHP 容器验证路径
docker exec -it sunphp-php bash
ls -la /var/www/im.fuye.io/
exit

# 2. 进入 Nginx 容器验证路径
docker exec -it sunphp-nginx bash
ls -la /var/www/im.fuye.io/
exit

# 3. 验证 GatewayWorker 进程
docker exec sunphp-php ps aux | grep GatewayWorker
```

## 检查 Telegram 同步

```bash
# 查看 Telegram 同步日志
docker exec sunphp-php tail -f /var/www/im.fuye.io/runtime/mdim/log/$(date +%Y%m)/$(date +%d).log | grep telegram_sync
```

## 注意事项

1. **路径统一**：现在开发环境（Docker）和生产环境（CentOS）都使用 `/var/www/im.fuye.io`，无需修改代码即可部署
2. **Session 文件**：Telegram session 文件位置不变，仍在 `addons/mdkeji_im/scripts/telegram/`
3. **附件目录**：附件目录在容器内是 `/var/www/im.fuye.io/attachment`
4. **日志目录**：日志目录在容器内是 `/var/www/im.fuye.io/runtime/mdim/log`

## 生产环境部署

生产环境无需修改任何代码，只需确保：
- 项目部署在 `/var/www/im.fuye.io`
- Python venv 在 `/var/www/im.fuye.io/addons/mdkeji_im/scripts/telegram/venv`
- 所有路径自动匹配

## 常见问题

### Q: 重启后 WebSocket 连接失败？

```bash
# 检查 GatewayWorker 是否运行
docker exec sunphp-php ps aux | grep php

# 如果没有运行，手动启动
docker exec sunphp-php php /var/www/im.fuye.io/addons/mdkeji_im/extend/GatewayWorker/start.php start -d
```

### Q: Nginx 502 错误？

```bash
# 检查 PHP-FPM 是否运行
docker exec sunphp-php ps aux | grep php-fpm

# 重启 PHP 容器
docker-compose restart php
```

### Q: 文件权限问题？

```bash
# 进入容器修复权限
docker exec sunphp-php chown -R www-data:www-data /var/www/im.fuye.io
```

## 回滚方案

如果需要回滚到旧路径（不推荐）：

```bash
# 1. 使用 git 恢复文件
git checkout HEAD -- docker-compose.yml nginx/conf.d/default.conf

# 2. 手动恢复代码中的路径（将 /var/www/im.fuye.io 改回 /var/www/html）

# 3. 重启容器
docker-compose down && docker-compose up -d
```

## 完成

执行上述重启步骤后，所有服务应正常运行，Telegram 同步功能会继续工作。

