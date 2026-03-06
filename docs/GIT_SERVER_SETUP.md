# 把服务器上的 im.fuye.io 提交到 GitHub

**背景**：本地 `im.fuye.io` 是 Git 项目；服务器上的 `/var/www/im.fuye.io` 不是 Git 项目，且与本地未同步。现在要把**服务器上**的该目录初始化为 Git 并推送到 GitHub（以服务器当前文件为准）。

## 前提

- 服务器已安装 `git`
- 已在 GitHub 创建好**空仓库**（如 `your-org/im.fuye.io`）
- 本地已有正确的 `.gitignore`（排除 `addons/mdkeji_im`、runtime、attachment 等）

---

## 步骤

### 1. 把本地的 .gitignore 传到服务器

在**本机**（WSL 或 PowerShell）执行，把当前项目里的 `.gitignore` 拷到服务器，避免把 `addons/mdkeji_im`、runtime、vendor 等提交上去：

```bash
# 在 im.fuye.io 项目根目录下执行（或把路径改成你本地的项目路径）
scp .gitignore root@fuye.io:/var/www/im.fuye.io/
```

### 2. SSH 登录服务器

```bash
ssh root@fuye.io
```

### 3. 在服务器上进入项目目录并初始化 Git

```bash
cd /var/www/im.fuye.io

# 确认没有 .git（当前不是 git 项目）
ls -la .git   # 若存在且要重新来，可先 rm -rf .git

git init
git add .
git status    # 确认没有 addons/mdkeji_im、runtime、vendor、attachment
git commit -m "chore: initial commit from server"
```

### 4. 添加 GitHub 远程并推送

把 `YOUR_ORG/im.fuye.io` 换成你的 GitHub 仓库：

```bash
git remote add origin https://github.com/YOUR_ORG/im.fuye.io.git
# 或 SSH： git remote add origin git@github.com:YOUR_ORG/im.fuye.io.git

git branch -M main
git push -u origin main
```

若 GitHub 仓库已有内容（如 README），需要先拉再推：

```bash
git pull origin main --allow-unrelated-histories
# 解决冲突后
git push -u origin main
```

### 5. 可选：确认 .gitignore 生效

```bash
git check-ignore -v addons/mdkeji_im/core/controller/Some.php
# 应显示被 .gitignore 某条规则匹配
```

---

## 说明

- 提交的是**服务器上** `/var/www/im.fuye.io` 的当前内容，与本地 im.fuye.io 是否一致无关。
- **addons/mdkeji_im** 被 `.gitignore` 排除，不会进入该仓库；在服务器上单独用其自己的 Git 管理。
- 之后若要在本地与 GitHub 同步，可在本地 `git remote add origin <同一 GitHub 地址>` 再 pull/push，注意本地与服务器可能产生冲突，需按需合并。
