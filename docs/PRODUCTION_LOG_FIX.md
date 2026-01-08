# 生产环境日志问题排查与修复

## 问题诊断

生产环境没有日志的主要原因：

1. **日志级别配置为空**：`config/log.php` 中 `level => []` 导致不记录任何日志
2. **错误日志重定向问题**：`app/index.php` 将错误日志重定向到 `php://stderr`（Docker专用），生产环境可能丢失
3. **日志目录权限问题**：`runtime/log/` 目录可能不存在或没有写权限
4. **实时写入未开启**：`realtime_write => false` 可能导致日志延迟

## 修复步骤

### 1. 修复日志配置（已修复）

配置文件 `config/log.php` 已更新：
- 添加日志级别：`['error', 'warning', 'info', 'sql', 'notice', 'alert']`
- 开启实时写入：`realtime_write => true`
- 错误和警告独立记录：`apart_level => ['error', 'warning']`

### 2. 生产环境操作步骤

#### 2.1 创建并设置日志目录权限

```bash
# 进入项目目录
cd /path/to/im.fuye.io

# 创建日志目录
mkdir -p runtime/log
mkdir -p runtime/logs

# 设置权限（根据实际web服务器用户调整）
# 如果使用 nginx
chown -R nginx:nginx runtime/
chmod -R 755 runtime/
chmod -R 777 runtime/log/
chmod -R 777 runtime/logs/

# 或如果使用 apache
chown -R apache:apache runtime/
chmod -R 755 runtime/
chmod -R 777 runtime/log/
chmod -R 777 runtime/logs/
```

#### 2.2 检查PHP错误日志配置

```bash
# 查看PHP配置的错误日志路径
php -i | grep error_log

# 或查看PHP-FPM配置
grep "php_admin_value\[error_log\]" /etc/php-fpm.d/*.conf
grep "catch_workers_output" /etc/php-fpm.d/*.conf
```

#### 2.3 配置PHP-FPM错误日志（如果需要）

编辑 PHP-FPM 配置文件（通常是 `/etc/php-fpm.d/www.conf`）：

```ini
; 错误日志文件路径
php_admin_value[error_log] = /var/log/php-fpm/error.log
php_admin_flag[log_errors] = on

; 捕获worker进程输出（重要）
catch_workers_output = yes
```

重启 PHP-FPM：
```bash
systemctl restart php-fpm
```

#### 2.4 验证日志功能

```bash
# 检查日志目录是否可写
touch runtime/log/test.log && rm runtime/log/test.log
echo "日志目录权限正常" || echo "日志目录权限有问题"

# 测试ThinkPHP日志写入
php -r "
define('SUN_IN', true);
require __DIR__ . '/vendor/autoload.php';
\$app = new think\App();
\$app->initialize();
\think\facade\Log::write('测试日志', 'info');
echo '日志写入测试完成';
"

# 检查是否有日志文件生成
ls -la runtime/log/
```

#### 2.5 查看日志文件

```bash
# ThinkPHP框架日志（按日期）
ls -la runtime/log/

# 查看今天的日志
tail -f runtime/log/$(date +%Y%m%d).log

# 查看错误日志（如果单独记录）
tail -f runtime/log/error.log

# 查看警告日志
tail -f runtime/log/warning.log

# 自定义日志目录（sunphp/log）
ls -la runtime/logs/

# mdim日志
tail -f runtime/mdim/log/$(date +%Y%m)/$(date +%d).log
```

### 3. 生产环境错误日志重定向（可选修复）

如果希望PHP错误也写入文件而不是stderr，可以修改 `app/index.php`：

```php
// 将错误日志重定向到文件（生产环境）
$errorLogPath = root_path() . 'runtime/log/php_errors.log';
if (is_writable(dirname($errorLogPath))) {
    @ini_set('error_log', $errorLogPath);
} else {
    // 如果目录不可写，使用系统默认位置
    @ini_set('error_log', '');
}
@ini_set('log_errors', '1');
```

### 4. 验证清单

- [ ] `runtime/log/` 目录存在且有写权限
- [ ] `runtime/logs/` 目录存在且有写权限
- [ ] PHP-FPM 用户对runtime目录有写权限
- [ ] 日志配置文件已更新
- [ ] 可以手动创建测试日志文件
- [ ] 访问应用后查看是否有日志生成

### 5. 常用日志查看命令

```bash
# 实时查看所有日志
tail -f runtime/log/*.log

# 查看最近100行错误日志
tail -100 runtime/log/error.log

# 查找特定内容
grep "ERROR" runtime/log/*.log

# 查看今天的日志大小
du -sh runtime/log/

# 清理旧日志（保留最近7天）
find runtime/log/ -name "*.log" -mtime +7 -delete
```

## 日志文件位置

- **ThinkPHP框架日志**：`runtime/log/` 目录
  - 按日期：`runtime/log/YYYYMMDD.log`
  - 错误日志：`runtime/log/error.log`
  - 警告日志：`runtime/log/warning.log`

- **自定义日志**：`runtime/logs/` 目录
  - 由 `sunphp/log` 模块使用

- **mdim模块日志**：`runtime/mdim/log/YYYYMM/DD.log`
  - 按月/日分目录存储

- **PHP错误日志**：根据PHP-FPM配置，通常在：
  - `/var/log/php-fpm/error.log`
  - 或系统日志：`/var/log/messages`

## 注意事项

1. 定期清理旧日志，避免磁盘空间不足
2. 生产环境建议关闭调试模式（`APP_DEBUG=false`）
3. 监控日志文件大小，设置日志轮转
4. 敏感信息不要记录到日志中

