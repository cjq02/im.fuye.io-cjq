# sunphp

## 介绍
[sunphp](https://gitee.com/bluestear/sunphp) 是一个支持多用户，多平台，多应用的开发框架！其根本目的是实现快速开发公众号、小程序、H5、APP，无需开发者重复造轮子！为每一位开发者赋能！

## 官方文档
[sunphp官方文档](https://bluestear.github.io/sunphp-web)


## 技术支持
采用vue管理后台、thinkphp6多应用模式开发，安装环境要求：PHP7.4+Mysql（推荐5.7）+Nginx/Apache

## 功能支持
内置常用的微信支付、支付宝支付、文件上传、七牛云存储、阿里云OSS、腾讯云COS，微信登录、阿里云短信、腾讯云短信、邮件发送等功能，开发者只需要查看/app/demo/controller/目录下的示例，对照使用既可！

## 兼容支持
/addons/目录下创建的模块，作为兼容性功能，支持兼容运行微擎2.0模块。(但是**不推荐作为新应用开发**)

## 环境要求
安装环境要求：PHP7.4+Mysql（推荐5.7）+Nginx/Apache

## 安装步骤
1. 下载[install.php](https://bluestear.github.io/sunphp-web/install.html)文件到网站根目录
2. 取消php7.4禁用函数——exec
3. 访问https://您的域名/install.php安装本系统
4. 根据提示配置您的数据库、管理后台admin密码
5. 在服务器文件/etc/ssh/ssh_config末尾添加配置
<br/>
Include /www/wwwroot/sunphp.git/*.conf


## 免费商用版
[sunphp](https://gitee.com/bluestear/sunphp) 支持**免费商用**（需保留代码注释版权，页面版权信息），支持**Apache2.0开源协议**。


## 允许行为
1. 允许免费商用
2. 允许基于框架开发应用模块并作为独立系统出售
3. 允许修改系统logo图标、favicon.ico


## 禁止行为
1. 禁止修改框架代码并再次**发布框架衍生版与Sunphp产生恶意竞争**的行为
2. 禁止任意删除修改代码注释版权、页面版权信息
3. 禁止任何违反Apache2.0开源协议及《中华人民共和国著作权法》的行为


## 高级商用版
免费商用版已经能满足大部分用户需求，如果有以下特殊需求，可付费开通[高级商用版](https://mall.sunphp.cn/pages/goods_details/index?id=12)
1. 如需隐藏版权信息（powered by Sunphp），需付费开通高级商用版
2. 如需隐藏系统在线升级功能、应用市场功能，需付费开通高级商用版
3. 如需其他特殊的定制需求，欢迎联系[商务合作](https://bluestear.github.io/sunphp-web/cooperation/)


## 正版校验
[高级商用版校验](https://mall.sunphp.cn/check.html)，全网唯一入口！
1. 点击校验网址
2. 输入激活秘钥，点击校验按钮
3. 如果提示正版则为正版授权，反之则为假冒！


## 特技

1.  使用 Readme\_XXX.md 来支持不同的语言，例如 Readme\_en.md, Readme\_zh.md
2.  Gitee 官方博客 [blog.gitee.com](https://blog.gitee.com)
3.  你可以 [https://gitee.com/explore](https://gitee.com/explore) 这个地址来了解 Gitee 上的优秀开源项目
4.  [GVP](https://gitee.com/gvp) 全称是 Gitee 最有价值开源项目，是综合评定出的优秀开源项目
5.  Gitee 官方提供的使用手册 [https://gitee.com/help](https://gitee.com/help)
6.  Gitee 封面人物是一档用来展示 Gitee 会员风采的栏目 [https://gitee.com/gitee-stars/](https://gitee.com/gitee-stars/)

# 项目启动与调试指南

## 一、启动 Docker 服务

1. **安装 Docker Desktop**
   - 访问 [Docker 官网](https://www.docker.com/products/docker-desktop/) 下载并安装 Docker Desktop。
   - 启动 Docker Desktop，确保其正常运行。

2. **启动服务容器**
   - 打开终端（PowerShell），进入项目根目录：
     ```powershell
     cd D:\me\epiboly\fuye\projects\im.fuye.io
     ```
   - 构建并启动容器：
     ```powershell
     docker-compose up -d --build
     ```
   - 启动成功后，MySQL、PHP、Nginx 等服务会自动运行。

3. **访问服务**
   - 通过浏览器访问：
     - Nginx 默认：http://localhost
     - 其他服务请参考具体端口映射。

---

## 二、Xdebug 调试配置与使用

### 1. VSCode/Cursor 调试配置

- 打开 `.vscode/launch.json`，确认如下配置：
  ```json
  {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
          "/var/www/html": "${workspaceFolder}"
      },
      "log": true,
      "xdebugSettings": {
          "max_data": 65535,
          "show_hidden": 1,
          "max_children": 100,
          "max_depth": 5
      }
  }
  ```

### 2. Docker Xdebug 配置说明

- `docker-compose.yml` 已配置：
  - Xdebug 端口为 9003（**未做端口映射**，即没有 `- "9003:9003"`）
  - Xdebug 通过 `host.docker.internal` 连接宿主机 IDE

### 3. 调试步骤

1. **确保本地 9003 端口未被占用**
   - 可用命令检查：
     ```powershell
     netstat -ano | findstr :9003
     ```
   - 如被占用，请释放端口。

2. **启动 Docker 服务**（见上文）

3. **在 VSCode/Cursor 启动调试**
   - 选择"Listen for Xdebug"配置，点击启动调试。

4. **访问 PHP 页面或接口**
   - 触发断点，Xdebug 会自动连接到 IDE。

---

## 常见问题

- **端口冲突**：如遇 `listen EACCES: permission denied 0.0.0.0:9003`，请确认本地没有其他进程占用 9003 端口，且 docker-compose 未做 9003 端口映射。
- **断点无效**：请确认 Xdebug 配置、IDE 监听端口、`pathMappings` 设置正确。

---

如有其他问题，请联系项目维护者或查阅相关文档。
