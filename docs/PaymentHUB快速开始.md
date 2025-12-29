# PaymentHUB 快速开始指南

## 5 分钟快速集成

### 第一步：获取 API 凭证

1. 登录 PaymentHUB 后台
2. 进入「API 设置」
3. 获取以下信息：
   - API Key
   - API Secret
   - API Base URL

### 第二步：配置环境变量

在 `.env` 文件中添加：

```env
PAYMENTHUB_API_KEY=your_api_key
PAYMENTHUB_API_SECRET=your_api_secret
PAYMENTHUB_BASE_URL=https://paymenthub.yourdomain.com
PAYMENTHUB_NOTIFY_URL=https://yourdomain.com/payment/paymenthub/notify
```

### 第三步：创建 PaymentHUB 客户端

创建文件：`extend/sunphp/payment/PaymentHub.php`

（代码见完整文档）

### 第四步：修改充值接口

在 `addons/mdkeji_im/site.php` 的 `doMobilerecharge()` 方法中：

```php
// 创建订单后，调用 PaymentHUB
$paymentHub = new \sunphp\payment\PaymentHub();
$paymentResult = $paymentHub->createPayment([
    'amount' => $_GPC['money'],
    'gateway' => 'alipay', // 或 'wechat'
    'order_id' => $data['tid'],
    'notify_url' => config('paymenthub.notify_url')
]);

return jsonResult(200, '操作成功', [
    'tid' => $data['tid'],
    'payment_url' => $paymentResult['payment_url']
]);
```

### 第五步：创建回调接口

创建文件：`payment/paymenthub/notify.php`

（代码见完整文档）

### 第六步：测试

1. 发起充值请求
2. 检查是否返回支付链接
3. 完成支付
4. 验证回调是否收到
5. 检查订单状态和余额

---

## 核心流程

```
用户点击充值
    ↓
后端创建订单
    ↓
调用 PaymentHUB API
    ↓
返回支付链接
    ↓
用户完成支付
    ↓
PaymentHUB 回调
    ↓
更新订单和余额
```

---

## 常见问题速查

**Q: 钱会到哪里？**  
A: 直接进入你的支付宝/微信商户账户

**Q: 如何配置支付宝？**  
A: 在 PaymentHUB 后台配置支付宝商户信息

**Q: 回调收不到？**  
A: 检查 URL 是否可访问，查看 PaymentHUB 后台日志

---

## 详细文档

查看完整文档：[PaymentHUB集成文档.md](./PaymentHUB集成文档.md)

