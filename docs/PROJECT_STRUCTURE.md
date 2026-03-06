# im.fuye.io 项目结构说明

## 根目录结构

```
im.fuye.io/
├── addons/                    # 微擎/兼容插件目录
│   ├── addons.md
│   └── mdkeji_im/             # 独立 Git 仓库，本仓库不包含（见 .gitignore）
├── app/                       # ThinkPHP 应用（admin、demo 等）
├── config/                    # 应用配置（数据库、日志、路由等）
├── data/                      # 数据/配置（含 .env，不提交）
├── docs/                      # 文档
├── extend/                    # 扩展（sunphp 等）
├── nginx/                     # Nginx 配置
├── public/                    # Web 入口与静态资源
├── route/                     # 路由
├── runtime/                   # 运行时缓存与日志（不提交）
├── scripts/                   # 运维/检查脚本
├── view/                      # 模板
├── web/                       # Web 入口
├── attachment/                # 用户上传附件（不提交）
├── composer.json / .lock
├── docker-compose.yml
├── Dockerfile
└── .gitignore                 # 排除 mdkeji_im、runtime、vendor 等
```

## 说明

- **addons/mdkeji_im**：独立 Git 项目，部署时单独 clone/更新，本仓库通过 `.gitignore` 排除，不纳入 im.fuye.io 的版本控制。
- **runtime / attachment / data/mysql**：运行时与用户数据，不提交。
- **vendor / node_modules**：依赖由 composer/npm 安装，不提交。

## 服务器 Git 初始化并推送到 GitHub

见仓库根目录 `docs/GIT_SERVER_SETUP.md` 或下方命令摘要。
