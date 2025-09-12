# IM.Fuye.io 项目

这是一个基于PHP的即时通讯和论坛系统项目。

## 环境要求

- Docker
- Docker Compose
- PHP 7.4+
- MySQL 5.7+
- Nginx

## 快速开始

### 1. 启动服务

```bash
docker-compose up -d
```

### 2. 进入PHP容器

```bash
docker exec -it sunphp-php bash
```

### 3. 监控日志

监控PHP容器中与mdkeji相关的日志：

```powershell
docker-compose logs --tail=100 -f php | Select-String "mdim"
```

其他有用的日志监控命令：

```bash
# 监控所有PHP日志
docker-compose logs -f php

# 监控最近500行日志
docker-compose logs --tail=500 -f php | Select-String "mdim"

# 监控多个容器
docker-compose logs -f php mysql nginx | Select-String "mdim"
```

解决powershell显示中文乱码问题
```bash
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
```

## 服务说明

- **PHP容器**: `sunphp-php` - PHP 7.4-FPM服务
- **MySQL容器**: `sunphp-mysql` - MySQL 5.7数据库
- **Nginx容器**: `sunphp-nginx` - Web服务器

## 常用命令

```bash
# 查看容器状态
docker-compose ps

# 重启服务
docker-compose restart

# 停止服务
docker-compose down

# 查看服务日志
docker-compose logs [service_name]

# 进入MySQL容器
docker exec -it sunphp-mysql mysql -u root -p123456 sunphp
```

## 项目结构

```
├── addons/mdkeji_im/          # 主要插件目录
│   ├── core/                  # 核心功能
│   │   ├── controller/        # 控制器
│   │   └── model/            # 模型
│   ├── sql/                  # 数据库脚本
│   └── static/               # 静态资源
├── config/                   # 配置文件
├── public/                   # 公共资源
└── docker-compose.yml        # Docker配置
```

## 开发说明

### 论坛功能

项目包含完整的论坛功能：

- 帖子发布/编辑/删除
- 评论系统（支持回复）
- 点赞功能
- 用户权限管理

### API接口

主要接口包括：

- `doMobileGetForumPostList` - 获取帖子列表
- `doMobilePublishForumPost` - 发布/编辑帖子
- `doMobileDeleteForumPost` - 删除帖子
- `doMobileGetForumCommentList` - 获取评论列表
- `doMobileAddForumComment` - 添加评论
- `doMobileDeleteForumComment` - 删除评论
- `doMobileLikeForumComment` - 点赞评论

## 故障排除

### 查看错误日志

```powershell
# 监控PHP错误日志
docker-compose logs --tail=100 -f php | Select-String "error"

# 监控所有错误
docker-compose logs -f php | Select-String -Pattern "error|exception|fatal"
```

### 常见问题

1. **容器启动失败**: 检查端口是否被占用
2. **数据库连接失败**: 确认MySQL容器已启动
3. **权限问题**: 检查文件权限设置

## 许可证

本项目采用MIT许可证。