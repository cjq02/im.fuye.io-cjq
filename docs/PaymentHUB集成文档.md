# PaymentHUB 集成文档

## 目录
- [概述](#概述)
- [PaymentHUB 简介](#paymenthub-简介)
- [系统架构](#系统架构)
- [集成流程](#集成流程)
- [API 接口说明](#api-接口说明)
- [代码实现](#代码实现)
- [配置说明](#配置说明)
- [测试验证](#测试验证)
- [常见问题](#常见问题)

---

## 概述

本文档说明如何将 PaymentHUB 支付网关聚合平台集成到富业 IM 系统中，实现统一的支付管理功能。

### 目标
- 用户点击充值 → 后端调用 PaymentHUB API → 资金进入预设的支付网关账户（支付宝/微信）
- 支持多种支付方式（支付宝、微信支付等）
- 统一管理多个支付网关

---

## PaymentHUB 简介

### 什么是 PaymentHUB
PaymentHUB 是一个支付网关聚合平台，用于统一管理和调用多个支付网关（如支付宝、微信支付、Stripe 等）。

### 核心特点
- **多支付网关支持**：支持支付宝、微信支付、信用卡、加密货币等多种支付方式
- **统一 API 接口**：提供统一的 REST API 接口，简化集成
- **资金直接到账**：用户支付后，资金直接进入你配置的支付网关账户
- **安全可靠**：支持签名验证、回调通知等安全机制

### 资金流向
```
用户支付 → PaymentHUB（路由） → 你的支付网关账户（支付宝/微信） → 你的银行卡
```

**重要说明**：
- PaymentHUB 不处理资金，只负责路由和调用
- 资金直接进入你的支付网关商户账户
- 需要通过支付网关的提现功能将资金转到银行卡

---

## 系统架构

### 多层架构说明

PaymentHUB 作为支付网关聚合平台，支持多层架构：

```
上游系统（游戏平台/业务系统）
  ↓
PaymentHUB（支付网关聚合平台）
  ↓
下游支付网关（多个国家的支付平台）
  ↓
用户完成支付
```

### 架构层次

#### 1. 上游系统（游戏平台）
- **角色**：业务发起方，需要处理支付
- **特点**：可能是多个游戏平台，每个平台有自己的业务逻辑
- **对接方式**：通过 PaymentHUB 提供的统一 API 接口

#### 2. PaymentHUB（中间层）
- **角色**：支付网关聚合平台
- **功能**：
  - 接收上游系统的支付请求
  - 路由到合适的支付网关
  - 统一管理多个支付网关
  - 处理支付回调和通知
- **优势**：统一接口，简化对接

#### 3. 下游支付网关（多个支付平台）
- **角色**：实际处理支付的平台
- **类型**：
  - 官方平台：PayPal、Stripe（通常不接游戏业务）
  - 地区支付平台：各国的小型支付平台（接游戏业务）
- **特点**：每个支付网关对接方法可能不同，但都有对接文档

### 现有系统架构
```
前端（uni-app）
  ↓
后端 API（site.php）
  ↓
支付处理（PayController.php）
  ↓
支付网关（支付宝/微信）
```

### 集成 PaymentHUB 后的单层架构
```
前端（uni-app）
  ↓
后端 API（site.php）
  ↓
PaymentHUB API（新增）
  ↓
支付网关（支付宝/微信）
```

### 集成 PaymentHUB 后的多层架构（游戏平台场景）
```
游戏平台 A
游戏平台 B  ──→  PaymentHUB  ──→  支付网关1（泰国）
游戏平台 C       （统一API）        支付网关2（越南）
                                     支付网关3（印尼）
                                     支付网关4（菲律宾）
                                     ...
```

### 数据流
1. **用户发起充值**
   - 前端：`app/uni_im/pages/my/score/score.vue`
   - 调用：`POST /recharge`

2. **后端处理**
   - 创建订单：`addons/mdkeji_im/site.php::doMobilerecharge()`
   - 调用 PaymentHUB API 创建支付订单
   - 返回支付链接/二维码

3. **用户完成支付**
   - 跳转到 PaymentHUB 支付页面
   - 选择支付方式完成支付

4. **支付回调**
   - PaymentHUB 回调：`payment/paymenthub/notify.php`
   - 更新订单状态
   - 增加用户余额

---

## 集成流程

### 步骤 1：购买和部署 PaymentHUB
1. 从 CodeCanyon 购买 PaymentHUB
2. 部署到服务器（独立域名或子域名）
3. 完成初始配置

### 步骤 2：配置支付网关

#### 2.1 配置标准支付网关
在 PaymentHUB 后台配置：
- **支付宝**：配置 AppID、私钥、公钥等
- **微信支付**：配置商户号、API 密钥等
- **PayPal**：配置 Client ID、Secret 等（如果支持）

#### 2.2 配置地区支付网关（重要）
对于各个国家的小型支付平台，需要：

1. **获取支付网关对接文档**
   - 从支付平台获取 API 文档
   - 了解对接方式（REST API、Webhook 等）
   - 获取商户凭证（API Key、Secret 等）

2. **在 PaymentHUB 中添加自定义网关**
   - 进入「支付网关管理」
   - 选择「添加自定义网关」
   - 填写网关信息：
     - 网关名称（如：泰国支付网关）
     - 网关类型（REST API / SOAP / 其他）
     - API 端点地址
     - 商户凭证
     - 回调地址

3. **配置网关参数**
   - 根据对接文档配置必要参数
   - 设置签名方式
   - 配置回调通知地址

4. **测试网关连接**
   - 使用测试环境验证连接
   - 测试创建支付订单
   - 测试回调通知

### 步骤 3：获取 API 凭证
从 PaymentHUB 后台获取：
- API Key
- API Secret
- API Base URL（如：`https://paymenthub.yourdomain.com/api`）

### 步骤 4：对接上游游戏平台

#### 4.1 为游戏平台提供 API 接口
PaymentHUB 作为中间层，需要向上游游戏平台提供统一的 API：

**接口示例**：`POST /api/v1/game/payment/create`

```json
{
  "game_id": "game_001",
  "user_id": "user_123",
  "amount": "100.00",
  "currency": "THB",
  "country": "TH",
  "description": "游戏充值",
  "notify_url": "https://gameplatform.com/payment/notify"
}
```

**响应**：
```json
{
  "status": "success",
  "data": {
    "payment_id": "pay_123456789",
    "payment_url": "https://paymenthub.com/pay/pay_123456789",
    "gateway": "thailand_payment_gateway_1"
  }
}
```

#### 4.2 实现网关路由策略
根据用户所在国家/地区，自动选择合适的支付网关：

```php
// 网关路由逻辑
function routeGateway($country, $currency) {
    $gatewayMap = [
        'TH' => 'thailand_payment_gateway_1',  // 泰国
        'VN' => 'vietnam_payment_gateway_1',   // 越南
        'ID' => 'indonesia_payment_gateway_1', // 印尼
        'PH' => 'philippines_payment_gateway_1', // 菲律宾
        // ... 更多国家
    ];
    
    return $gatewayMap[$country] ?? 'default_gateway';
}
```

### 步骤 5：集成到现有系统
1. 创建 PaymentHUB 客户端类
2. 修改充值接口，调用 PaymentHUB API
3. 创建回调处理接口
4. 更新前端支付流程
5. 实现多网关路由逻辑

---

## API 接口说明

### PaymentHUB API 端点

#### 1. 创建支付订单
**接口地址**：`POST /api/v1/payment/create`

**请求参数**：
```json
{
  "amount": "100.00",
  "currency": "CNY",
  "gateway": "alipay",
  "order_id": "202501011200001",
  "description": "账户充值",
  "return_url": "https://yourdomain.com/payment/return",
  "notify_url": "https://yourdomain.com/payment/paymenthub/notify",
  "customer": {
    "name": "张三",
    "email": "user@example.com"
  }
}
```

**响应示例**：
```json
{
  "status": "success",
  "data": {
    "payment_id": "pay_123456789",
    "payment_url": "https://paymenthub.yourdomain.com/pay/pay_123456789",
    "qr_code": "https://paymenthub.yourdomain.com/qr/pay_123456789",
    "expires_at": "2025-01-01 12:30:00"
  }
}
```

#### 2. 查询支付状态
**接口地址**：`GET /api/v1/payment/status/{payment_id}`

**响应示例**：
```json
{
  "status": "success",
  "data": {
    "payment_id": "pay_123456789",
    "order_id": "202501011200001",
    "status": "completed",
    "amount": "100.00",
    "currency": "CNY",
    "gateway": "alipay",
    "paid_at": "2025-01-01 12:15:30"
  }
}
```

#### 3. 支付回调通知
**接口地址**：`POST /payment/paymenthub/notify`（你的服务器）

**回调参数**：
```json
{
  "payment_id": "pay_123456789",
  "order_id": "202501011200001",
  "status": "completed",
  "amount": "100.00",
  "currency": "CNY",
  "gateway": "alipay",
  "transaction_id": "2025010122001234567890",
  "paid_at": "2025-01-01 12:15:30",
  "signature": "abc123def456..."
}
```

---

## 代码实现

### 1. 创建 PaymentHUB 客户端类

**文件路径**：`extend/sunphp/payment/PaymentHub.php`

```php
<?php

namespace sunphp\payment;

use think\facade\Log;
use think\facade\Config;

class PaymentHub
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    
    public function __construct()
    {
        // 从配置文件读取
        $this->apiKey = config('paymenthub.api_key');
        $this->apiSecret = config('paymenthub.api_secret');
        $this->baseUrl = config('paymenthub.base_url');
    }
    
    /**
     * 创建支付订单
     * @param array $params 支付参数
     * @return array
     */
    public function createPayment($params)
    {
        $url = $this->baseUrl . '/api/v1/payment/create';
        
        $data = [
            'amount' => $params['amount'],
            'currency' => $params['currency'] ?? 'CNY',
            'gateway' => $params['gateway'],
            'order_id' => $params['order_id'],
            'description' => $params['description'] ?? '账户充值',
            'return_url' => $params['return_url'],
            'notify_url' => $params['notify_url'],
            'customer' => $params['customer'] ?? []
        ];
        
        // 添加签名
        $data['signature'] = $this->generateSignature($data);
        
        $response = $this->httpPost($url, $data);
        
        if ($response['status'] === 'success') {
            return $response['data'];
        }
        
        throw new \Exception('创建支付订单失败：' . ($response['message'] ?? '未知错误'));
    }
    
    /**
     * 查询支付状态
     * @param string $paymentId 支付ID
     * @return array
     */
    public function getPaymentStatus($paymentId)
    {
        $url = $this->baseUrl . '/api/v1/payment/status/' . $paymentId;
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey
        ];
        
        $response = $this->httpGet($url, $headers);
        
        if ($response['status'] === 'success') {
            return $response['data'];
        }
        
        throw new \Exception('查询支付状态失败：' . ($response['message'] ?? '未知错误'));
    }
    
    /**
     * 验证回调签名
     * @param array $data 回调数据
     * @return bool
     */
    public function verifySignature($data)
    {
        $signature = $data['signature'] ?? '';
        unset($data['signature']);
        
        $expectedSignature = $this->generateSignature($data);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * 生成签名
     * @param array $data 数据
     * @return string
     */
    private function generateSignature($data)
    {
        ksort($data);
        $string = http_build_query($data) . '&key=' . $this->apiSecret;
        return md5($string);
    }
    
    /**
     * HTTP POST 请求
     * @param string $url URL
     * @param array $data 数据
     * @return array
     */
    private function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('HTTP 请求失败，状态码：' . $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * HTTP GET 请求
     * @param string $url URL
     * @param array $headers 请求头
     * @return array
     */
    private function httpGet($url, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('HTTP 请求失败，状态码：' . $httpCode);
        }
        
        return json_decode($response, true);
    }
}
```

### 2. 创建配置文件

**文件路径**：`config/paymenthub.php`

```php
<?php

return [
    // PaymentHUB API 配置
    'api_key' => env('PAYMENTHUB_API_KEY', ''),
    'api_secret' => env('PAYMENTHUB_API_SECRET', ''),
    'base_url' => env('PAYMENTHUB_BASE_URL', 'https://paymenthub.yourdomain.com'),
    
    // 回调地址
    'notify_url' => env('PAYMENTHUB_NOTIFY_URL', 'https://yourdomain.com/payment/paymenthub/notify'),
    'return_url' => env('PAYMENTHUB_RETURN_URL', 'https://yourdomain.com/payment/return'),
];
```

### 3. 修改充值接口

**文件路径**：`addons/mdkeji_im/site.php`

在 `doMobilerecharge()` 方法中添加 PaymentHUB 调用：

```php
public function doMobilerecharge()
{
    global $_W, $_GPC;
    if (checkToken()) {
        $user = M('user')->get(array('sessionid' => $_GPC['sessionid']));

        // 青少年模式禁止付款
        if ($user['adolescent'] == 1) {
            return jsonResult(400, mdkeji_lang_get('青少年模式，禁止付款！'), array());
        }

        if (
            isset($user['id']) && isset($_GPC['money'])
            && preg_match('/^(0|[1-9]\d*)(\.\d{1,2})?$/', $_GPC['money'])
            && floatval($_GPC['money']) > 0
        ) {
            // 创建订单
            $data = array(
                'tradeno' => date("YmdHis"),
                'type' => 2,
                'tid' => md5(uniqid(mt_rand(), true)),
                'uid' => $user['id'],
                'money' => $_GPC['money'],
                'create_time' => date("Y-m-d H:i:s", time()),
                'paystate' => 0,
                'rid' => $_GPC['rid'] ?? '',
                'des' => mdkeji_lang_get("lang_0070")
            );

            $res = M('order')->insert($data);
            if ($res > 0) {
                // 调用 PaymentHUB 创建支付
                try {
                    $paymentHub = new \sunphp\payment\PaymentHub();
                    
                    // 根据支付方式选择网关
                    $gateway = 'alipay'; // 默认支付宝
                    if (isset($_GPC['payway'])) {
                        $gateway = $_GPC['payway'] == 2 ? 'wechat' : 'alipay';
                    }
                    
                    $paymentParams = [
                        'amount' => number_format($_GPC['money'], 2, '.', ''),
                        'currency' => 'CNY',
                        'gateway' => $gateway,
                        'order_id' => $data['tid'],
                        'description' => '账户充值',
                        'return_url' => config('paymenthub.return_url'),
                        'notify_url' => config('paymenthub.notify_url'),
                        'customer' => [
                            'name' => $user['nickname'] ?? '',
                            'email' => $user['email'] ?? ''
                        ]
                    ];
                    
                    $paymentResult = $paymentHub->createPayment($paymentParams);
                    
                    // 保存支付ID到订单表（需要添加 payment_id 字段）
                    // M('order')->update(['payment_id' => $paymentResult['payment_id']], ['tid' => $data['tid']]);
                    
                    return jsonResult(200, mdkeji_lang_get('操作成功'), [
                        'tid' => $data['tid'],
                        'payment_url' => $paymentResult['payment_url'],
                        'qr_code' => $paymentResult['qr_code'] ?? '',
                        'payment_id' => $paymentResult['payment_id']
                    ]);
                    
                } catch (\Exception $e) {
                    // 记录错误日志
                    \think\facade\Log::error('PaymentHUB 创建支付失败：' . $e->getMessage());
                    
                    // 返回订单号，使用原有支付方式
                    return jsonResult(200, mdkeji_lang_get('操作成功'), array('tid' => $data['tid']));
                }
            }
        }
    }
    return jsonResult(400, mdkeji_lang_get('参数错误'), array());
}
```

### 4. 创建回调处理接口

**文件路径**：`payment/paymenthub/notify.php`

```php
<?php

namespace think;

define('SUN_IN', true);

require __DIR__ . '/../../vendor/autoload.php';

use app\admin\model\CoreApp;
use app\admin\model\CoreOrder;
use think\App;
use sunphp\payment\PaymentHub;

$app = new App();
$app->initialize();

$log = $app->log;
$request = $app->request;

$log->write('PaymentHUB 回调通知：' . $request->domain() . $request->url());
$log->write($request->post());

$data = $request->post();

// 验证签名
try {
    $paymentHub = new PaymentHub();
    
    if (!$paymentHub->verifySignature($data)) {
        $log->write('PaymentHUB 签名验证失败');
        exit('fail');
    }
    
    // 验证订单
    $order = M('order')->get(array('tid' => $data['order_id']));
    if (empty($order)) {
        $log->write('PaymentHUB 订单不存在：' . $data['order_id']);
        exit('fail');
    }
    
    // 检查订单状态
    if ($order['paystate'] == 1) {
        $log->write('PaymentHUB 订单已支付：' . $data['order_id']);
        exit('success');
    }
    
    // 验证金额
    if (abs(floatval($order['money']) - floatval($data['amount'])) > 0.01) {
        $log->write('PaymentHUB 金额不匹配：订单金额 ' . $order['money'] . '，支付金额 ' . $data['amount']);
        exit('fail');
    }
    
    // 验证支付状态
    if ($data['status'] !== 'completed') {
        $log->write('PaymentHUB 支付未完成：' . $data['status']);
        exit('fail');
    }
    
    // 更新订单状态
    M('order')->update([
        'paystate' => 1,
        'paytime' => date('Y-m-d H:i:s', strtotime($data['paid_at'])),
        'transaction_id' => $data['transaction_id'] ?? ''
    ], ['tid' => $data['order_id']]);
    
    // 通知模块支付成功
    $module = CoreApp::where([
        'identity' => $order['module'] ?? 'mdkeji_im',
        'is_delete' => 0
    ])->find();
    
    if (!empty($module)) {
        $request->setPathinfo('PayResult/notify');
        $notify_post = [
            'from' => 'notify',
            'result' => 'success',
            'type' => $data['gateway'],
            'acid' => $order['acid'] ?? 0,
            'module' => $order['module'] ?? 'mdkeji_im',
            'tid' => $order['tid'],
            'title' => $order['des'] ?? '账户充值',
            'fee' => $order['money']
        ];
        
        if ($module['dir'] == 'addons') {
            require_once root_path() . 'extend/sunphp/addons/payresult.php';
        } else {
            $request->withPost($notify_post);
            $http = $app->http;
            $http->name($order['module'] ?? 'mdkeji_im');
            $http->run($request);
        }
    }
    
    $log->write('PaymentHUB 支付成功处理完成：' . $data['order_id']);
    
    exit('success');
    
} catch (\Exception $e) {
    $log->write('PaymentHUB 回调处理异常：' . $e->getMessage());
    exit('fail');
}
```

### 5. 更新前端支付流程

**文件路径**：`app/uni_im/common/js/pay.js`

在 `IMPay()` 函数中添加 PaymentHUB 支付支持：

```javascript
// 在 IMPay 函数中添加
function paymentHubPay(data) {
    return new Promise((resolve, reject) => {
        // 如果后端返回了 payment_url，直接跳转
        if (data.payment_url) {
            // H5 环境
            if (window.location) {
                window.location.href = data.payment_url;
            } else {
                // uni-app 环境
                uni.navigateTo({
                    url: '/pages/webview/webview?url=' + encodeURIComponent(data.payment_url)
                });
            }
            resolve();
        } else if (data.qr_code) {
            // 显示二维码
            // 实现二维码显示逻辑
            resolve();
        } else {
            reject('未获取到支付链接');
        }
    });
}
```

---

## 配置说明

### 1. 环境变量配置

在 `.env` 文件中添加：

```env
# PaymentHUB 配置
PAYMENTHUB_API_KEY=your_api_key_here
PAYMENTHUB_API_SECRET=your_api_secret_here
PAYMENTHUB_BASE_URL=https://paymenthub.yourdomain.com
PAYMENTHUB_NOTIFY_URL=https://yourdomain.com/payment/paymenthub/notify
PAYMENTHUB_RETURN_URL=https://yourdomain.com/payment/return
```

### 2. PaymentHUB 后台配置

1. **登录 PaymentHUB 后台**
2. **配置支付网关**：
   - 支付宝：填写 AppID、私钥、公钥证书等
   - 微信支付：填写商户号、API 密钥等
3. **获取 API 凭证**：
   - API Key
   - API Secret
4. **配置回调地址**：
   - 通知回调：`https://yourdomain.com/payment/paymenthub/notify`
   - 返回地址：`https://yourdomain.com/payment/return`

### 3. 数据库字段扩展

如果需要保存 PaymentHUB 的支付ID，需要在订单表中添加字段：

```sql
ALTER TABLE `ims_mdkeji_im_order` 
ADD COLUMN `payment_id` VARCHAR(100) DEFAULT '' COMMENT 'PaymentHUB支付ID' AFTER `tid`;
```

---

## 测试验证

### 1. 测试流程

1. **创建测试订单**
   - 前端发起充值请求
   - 检查后端是否正确调用 PaymentHUB API
   - 验证返回的支付链接

2. **测试支付流程**
   - 使用测试账号完成支付
   - 验证回调是否正确接收
   - 检查订单状态是否更新
   - 验证用户余额是否增加

3. **测试异常情况**
   - 支付失败处理
   - 网络异常处理
   - 签名验证失败处理

### 2. 日志查看

查看支付相关日志：
- 后端日志：`runtime/log/`
- PaymentHUB 回调日志：`payment/paymenthub/notify.php` 中的日志记录

---

## 多支付网关对接方案

### 业务场景说明

在游戏平台业务中，经常遇到以下情况：
- **官方支付平台**（如 PayPal、Stripe）通常不接受游戏业务
- **地区支付平台**：各国存在小型支付平台，愿意承接游戏业务
- **对接方法不同**：每个支付网关的对接文档和 API 可能不同

### PaymentHUB 的作用

PaymentHUB 作为中间层，可以：
1. **统一接口**：向上游游戏平台提供统一的 API
2. **网关适配**：对接不同格式的支付网关 API
3. **智能路由**：根据国家/地区自动选择合适的支付网关
4. **统一管理**：集中管理所有支付网关的配置和状态

### 对接流程

#### 1. 获取支付网关对接文档

对于每个需要对接的支付网关：

1. **联系支付网关提供商**
   - 获取 API 对接文档
   - 获取测试环境账号
   - 了解对接要求和限制

2. **分析对接文档**
   - API 接口格式（REST API / SOAP / 其他）
   - 请求参数格式
   - 签名/加密方式
   - 回调通知格式
   - 错误处理机制

3. **准备对接信息**
   - 商户 ID / API Key
   - API Secret / 私钥
   - API 端点地址
   - 回调通知地址

#### 2. 在 PaymentHUB 中配置网关

##### 方法一：使用 PaymentHUB 内置网关模板

如果 PaymentHUB 支持该支付网关：

1. 进入「支付网关管理」
2. 选择对应的网关模板
3. 填写配置信息
4. 测试连接

##### 方法二：自定义网关适配器

如果 PaymentHUB 不支持该支付网关，需要创建自定义适配器：

**文件结构**：
```
paymenthub/
  app/
    Gateways/
      Custom/
        ThailandGateway.php      # 泰国支付网关适配器
        VietnamGateway.php        # 越南支付网关适配器
        IndonesiaGateway.php      # 印尼支付网关适配器
        ...
```

**适配器示例**：`ThailandGateway.php`

```php
<?php

namespace App\Gateways\Custom;

use App\Gateways\GatewayInterface;

class ThailandGateway implements GatewayInterface
{
    private $apiKey;
    private $apiSecret;
    private $apiUrl;
    
    public function __construct($config)
    {
        $this->apiKey = $config['api_key'];
        $this->apiSecret = $config['api_secret'];
        $this->apiUrl = $config['api_url'];
    }
    
    /**
     * 创建支付订单
     */
    public function createPayment($params)
    {
        // 根据支付网关文档构建请求
        $requestData = [
            'merchant_id' => $this->apiKey,
            'order_id' => $params['order_id'],
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'callback_url' => $params['notify_url'],
            'return_url' => $params['return_url'],
            'timestamp' => time()
        ];
        
        // 生成签名（根据网关文档的签名规则）
        $requestData['signature'] = $this->generateSignature($requestData);
        
        // 发送请求
        $response = $this->httpPost($this->apiUrl . '/create', $requestData);
        
        // 解析响应（根据网关文档的响应格式）
        return [
            'payment_id' => $response['transaction_id'],
            'payment_url' => $response['payment_url'],
            'qr_code' => $response['qr_code'] ?? ''
        ];
    }
    
    /**
     * 验证回调签名
     */
    public function verifyCallback($data)
    {
        $signature = $data['signature'] ?? '';
        unset($data['signature']);
        
        $expectedSignature = $this->generateSignature($data);
        
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * 处理回调通知
     */
    public function handleCallback($data)
    {
        // 验证签名
        if (!$this->verifyCallback($data)) {
            throw new \Exception('签名验证失败');
        }
        
        // 返回统一格式
        return [
            'payment_id' => $data['transaction_id'],
            'order_id' => $data['order_id'],
            'status' => $data['status'] === 'success' ? 'completed' : 'failed',
            'amount' => $data['amount'],
            'transaction_id' => $data['transaction_id'],
            'paid_at' => $data['paid_time']
        ];
    }
    
    /**
     * 生成签名（根据网关文档）
     */
    private function generateSignature($data)
    {
        ksort($data);
        $string = http_build_query($data) . '&key=' . $this->apiSecret;
        return md5($string);
    }
    
    /**
     * HTTP POST 请求
     */
    private function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new \Exception('HTTP 请求失败，状态码：' . $httpCode);
        }
        
        return json_decode($response, true);
    }
}
```

#### 3. 配置网关路由规则

在 PaymentHUB 后台配置网关路由：

**路由规则示例**：

| 国家代码 | 货币 | 网关名称 | 优先级 |
|---------|------|---------|--------|
| TH | THB | thailand_gateway_1 | 1 |
| TH | THB | thailand_gateway_2 | 2 |
| VN | VND | vietnam_gateway_1 | 1 |
| ID | IDR | indonesia_gateway_1 | 1 |
| PH | PHP | philippines_gateway_1 | 1 |

**路由逻辑**：
```php
// PaymentHUB 内部路由逻辑
function routeGateway($country, $currency) {
    // 1. 查找匹配的网关
    $gateways = Gateway::where('country', $country)
        ->where('currency', $currency)
        ->where('status', 'active')
        ->orderBy('priority', 'asc')
        ->get();
    
    // 2. 选择优先级最高的可用网关
    foreach ($gateways as $gateway) {
        if ($this->checkGatewayHealth($gateway)) {
            return $gateway;
        }
    }
    
    // 3. 如果没有可用网关，使用默认网关
    return Gateway::where('is_default', 1)->first();
}
```

#### 4. 实现网关健康检查

定期检查网关状态，自动切换故障网关：

```php
// 网关健康检查
function checkGatewayHealth($gateway) {
    try {
        // 发送测试请求
        $response = $this->sendTestRequest($gateway);
        
        // 检查响应时间
        if ($response['response_time'] > 5000) {
            return false; // 响应时间过长
        }
        
        // 检查错误率
        if ($gateway['error_rate'] > 0.1) {
            return false; // 错误率过高
        }
        
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
```

### 对接不同支付网关的注意事项

#### 1. API 格式差异

不同支付网关的 API 格式可能不同：

**格式 A（JSON）**：
```json
{
  "merchant_id": "xxx",
  "order_id": "xxx",
  "amount": "100.00"
}
```

**格式 B（表单）**：
```
merchant_id=xxx&order_id=xxx&amount=100.00
```

**格式 C（XML）**：
```xml
<request>
  <merchant_id>xxx</merchant_id>
  <order_id>xxx</order_id>
  <amount>100.00</amount>
</request>
```

**解决方案**：在适配器中统一转换为 PaymentHUB 内部格式。

#### 2. 签名方式差异

不同支付网关的签名方式可能不同：

- **MD5 签名**：`md5(data + key)`
- **SHA256 签名**：`hash_hmac('sha256', data, key)`
- **RSA 签名**：使用私钥签名
- **自定义签名**：网关特定的签名规则

**解决方案**：在适配器中实现对应的签名方法。

#### 3. 回调通知格式差异

不同支付网关的回调格式可能不同：

**格式 A**：
```json
{
  "status": "success",
  "order_id": "xxx",
  "amount": "100.00"
}
```

**格式 B**：
```json
{
  "code": "200",
  "data": {
    "order_id": "xxx",
    "amount": "100.00"
  }
}
```

**解决方案**：在适配器中统一转换为 PaymentHUB 标准格式。

#### 4. 错误处理差异

不同支付网关的错误码和错误信息格式可能不同：

**解决方案**：建立错误码映射表，统一错误处理。

```php
// 错误码映射
$errorMap = [
    'thailand_gateway' => [
        'E001' => 'insufficient_balance',
        'E002' => 'invalid_card',
        'E003' => 'network_error'
    ],
    'vietnam_gateway' => [
        '1001' => 'insufficient_balance',
        '1002' => 'invalid_card',
        '1003' => 'network_error'
    ]
];
```

### 网关管理最佳实践

1. **多网关冗余**
   - 每个国家配置至少 2 个支付网关
   - 主网关故障时自动切换到备用网关

2. **监控和告警**
   - 监控网关响应时间
   - 监控网关成功率
   - 设置告警阈值

3. **定期测试**
   - 定期测试网关连接
   - 测试支付流程
   - 测试回调通知

4. **日志记录**
   - 记录所有网关请求
   - 记录网关响应
   - 记录错误信息

5. **版本管理**
   - 记录网关 API 版本
   - 跟踪网关文档更新
   - 及时更新适配器

---

## 常见问题

### Q1: 资金会进入哪里？
**A**: 资金会直接进入你在 PaymentHUB 中配置的支付网关账户（支付宝/微信商户账户），不会经过 PaymentHUB。

### Q2: 如何提现？
**A**: 需要通过支付网关的提现功能将资金转到银行卡：
- 支付宝：在支付宝商户平台申请提现
- 微信支付：在微信支付商户平台申请提现

### Q3: 支持哪些支付方式？
**A**: 取决于你在 PaymentHUB 中配置的支付网关，常见的有：
- 支付宝
- 微信支付
- 信用卡（Stripe、PayPal 等）
- 其他支付方式

### Q4: 如何切换支付方式？
**A**: 在调用 PaymentHUB API 时，通过 `gateway` 参数指定：
- `alipay`：支付宝
- `wechat`：微信支付
- 其他网关名称

### Q5: 回调通知失败怎么办？
**A**: 
1. 检查回调 URL 是否可访问
2. 检查服务器防火墙设置
3. 查看 PaymentHUB 后台的日志
4. 实现主动查询机制，定期查询未完成的订单状态

### Q6: 如何实现主动查询订单状态？
**A**: 可以添加定时任务，查询未完成的订单：

```php
// 定时任务：查询未完成的 PaymentHUB 订单
public function checkPaymentHubOrders()
{
    $orders = M('order')->where([
        'paystate' => 0,
        'type' => 2,
        'create_time' => ['>', date('Y-m-d H:i:s', time() - 3600)] // 1小时内的订单
    ])->select();
    
    $paymentHub = new \sunphp\payment\PaymentHub();
    
    foreach ($orders as $order) {
        if (empty($order['payment_id'])) {
            continue;
        }
        
        try {
            $status = $paymentHub->getPaymentStatus($order['payment_id']);
            
            if ($status['status'] === 'completed') {
                // 更新订单状态
                // 增加用户余额
            }
        } catch (\Exception $e) {
            // 记录错误
        }
    }
}
```

### Q7: 如何对接多个不同的支付网关？
**A**: 每个支付网关的对接方法可能不同，但都有对接文档。在 PaymentHUB 中：
1. 获取支付网关的对接文档
2. 创建自定义网关适配器（如果 PaymentHUB 不支持）
3. 在适配器中实现统一的接口方法
4. 配置网关路由规则

详见「多支付网关对接方案」章节。

### Q8: 如何对接上游游戏平台？
**A**: PaymentHUB 向上游游戏平台提供统一的 API 接口：
1. 游戏平台调用 PaymentHUB API 创建支付订单
2. PaymentHUB 根据用户国家/地区路由到合适的支付网关
3. 支付完成后，PaymentHUB 回调游戏平台
4. 游戏平台更新订单状态

详见「对接上游游戏平台」章节。

### Q9: 支付网关对接方法不同怎么办？
**A**: 这是正常情况。解决方案：
1. **创建适配器**：为每个支付网关创建适配器类
2. **统一接口**：适配器实现统一的接口方法
3. **格式转换**：在适配器中处理不同格式的 API
4. **签名适配**：实现不同网关的签名方法

详见「多支付网关对接方案」章节中的适配器示例。

### Q10: 如何选择支付网关？
**A**: PaymentHUB 支持智能路由：
1. **按国家路由**：根据用户所在国家选择对应网关
2. **按货币路由**：根据订单货币选择对应网关
3. **按优先级路由**：同一国家配置多个网关，按优先级选择
4. **健康检查**：自动跳过故障网关，选择可用网关

---

## 注意事项

1. **安全性**
   - 务必验证回调签名
   - 使用 HTTPS 传输
   - 保护 API 密钥安全

2. **错误处理**
   - 实现完善的异常处理
   - 记录详细的日志
   - 提供降级方案（PaymentHUB 不可用时使用原有支付方式）

3. **性能优化**
   - 异步处理回调通知
   - 实现订单状态缓存
   - 避免重复处理回调

4. **合规性**
   - 遵守支付网关的使用规范
   - 确保符合相关法律法规
   - 保护用户隐私信息

---

## 对接上游游戏平台

### 业务场景

PaymentHUB 作为支付网关聚合平台，需要向上游游戏平台提供统一的支付接口。

### 架构设计

```
游戏平台 A ──┐
游戏平台 B ──┼──→ PaymentHUB ──→ 多个支付网关
游戏平台 C ──┘    (统一API)
```

### 为游戏平台提供的 API

#### 1. 创建支付订单接口

**接口地址**：`POST /api/v1/game/payment/create`

**请求参数**：
```json
{
  "game_id": "game_001",
  "user_id": "user_123",
  "amount": "100.00",
  "currency": "THB",
  "country": "TH",
  "description": "游戏充值",
  "notify_url": "https://gameplatform.com/payment/notify",
  "return_url": "https://gameplatform.com/payment/return",
  "metadata": {
    "server_id": "server_001",
    "character_id": "char_123"
  }
}
```

**响应示例**：
```json
{
  "status": "success",
  "data": {
    "payment_id": "pay_123456789",
    "payment_url": "https://paymenthub.com/pay/pay_123456789",
    "qr_code": "https://paymenthub.com/qr/pay_123456789",
    "gateway": "thailand_payment_gateway_1",
    "expires_at": "2025-01-01 12:30:00"
  }
}
```

#### 2. 查询支付状态接口

**接口地址**：`GET /api/v1/game/payment/status/{payment_id}`

**响应示例**：
```json
{
  "status": "success",
  "data": {
    "payment_id": "pay_123456789",
    "order_id": "game_001_user_123_202501011200001",
    "status": "completed",
    "amount": "100.00",
    "currency": "THB",
    "gateway": "thailand_payment_gateway_1",
    "paid_at": "2025-01-01 12:15:30"
  }
}
```

#### 3. 支付回调通知

**接口地址**：游戏平台提供的回调 URL

**回调参数**：
```json
{
  "payment_id": "pay_123456789",
  "order_id": "game_001_user_123_202501011200001",
  "status": "completed",
  "amount": "100.00",
  "currency": "THB",
  "gateway": "thailand_payment_gateway_1",
  "transaction_id": "th_gateway_2025010122001234567890",
  "paid_at": "2025-01-01 12:15:30",
  "signature": "abc123def456..."
}
```

### 实现代码示例

#### PaymentHUB 端：游戏平台支付接口

```php
<?php

namespace App\Controllers\Api\V1\Game;

use App\Controllers\BaseController;

class PaymentController extends BaseController
{
    /**
     * 创建支付订单（供游戏平台调用）
     */
    public function createPayment()
    {
        $request = $this->request;
        
        // 验证游戏平台身份
        $gamePlatform = $this->verifyGamePlatform($request);
        if (!$gamePlatform) {
            return $this->jsonError('无效的游戏平台');
        }
        
        // 验证参数
        $params = $this->validatePaymentParams($request);
        
        // 根据国家/地区路由到合适的支付网关
        $gateway = $this->routeGateway($params['country'], $params['currency']);
        
        // 创建支付订单
        $paymentResult = $this->createPaymentOrder([
            'game_id' => $params['game_id'],
            'user_id' => $params['user_id'],
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'gateway' => $gateway,
            'notify_url' => $params['notify_url'],
            'return_url' => $params['return_url'],
            'metadata' => $params['metadata'] ?? []
        ]);
        
        return $this->jsonSuccess([
            'payment_id' => $paymentResult['payment_id'],
            'payment_url' => $paymentResult['payment_url'],
            'qr_code' => $paymentResult['qr_code'] ?? '',
            'gateway' => $gateway,
            'expires_at' => $paymentResult['expires_at']
        ]);
    }
    
    /**
     * 路由到合适的支付网关
     */
    private function routeGateway($country, $currency)
    {
        // 查找匹配的网关
        $gateway = Gateway::where('country', $country)
            ->where('currency', $currency)
            ->where('status', 'active')
            ->orderBy('priority', 'asc')
            ->first();
        
        if (!$gateway) {
            // 使用默认网关
            $gateway = Gateway::where('is_default', 1)->first();
        }
        
        return $gateway->name;
    }
    
    /**
     * 验证游戏平台身份
     */
    private function verifyGamePlatform($request)
    {
        $apiKey = $request->header('X-API-Key');
        $signature = $request->header('X-Signature');
        
        $gamePlatform = GamePlatform::where('api_key', $apiKey)->first();
        if (!$gamePlatform) {
            return false;
        }
        
        // 验证签名
        $expectedSignature = $this->generateSignature($request->all(), $gamePlatform->api_secret);
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        return $gamePlatform;
    }
}
```

#### 游戏平台端：调用 PaymentHUB API

```php
<?php

class PaymentHubClient
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    
    public function __construct()
    {
        $this->apiKey = config('paymenthub.api_key');
        $this->apiSecret = config('paymenthub.api_secret');
        $this->baseUrl = config('paymenthub.base_url');
    }
    
    /**
     * 创建支付订单
     */
    public function createPayment($params)
    {
        $url = $this->baseUrl . '/api/v1/game/payment/create';
        
        $data = [
            'game_id' => $params['game_id'],
            'user_id' => $params['user_id'],
            'amount' => $params['amount'],
            'currency' => $params['currency'],
            'country' => $params['country'],
            'description' => $params['description'] ?? '游戏充值',
            'notify_url' => $params['notify_url'],
            'return_url' => $params['return_url'],
            'metadata' => $params['metadata'] ?? []
        ];
        
        $signature = $this->generateSignature($data);
        
        $response = $this->httpPost($url, $data, [
            'X-API-Key: ' . $this->apiKey,
            'X-Signature: ' . $signature
        ]);
        
        return $response['data'];
    }
    
    /**
     * 生成签名
     */
    private function generateSignature($data)
    {
        ksort($data);
        $string = http_build_query($data) . '&key=' . $this->apiSecret;
        return md5($string);
    }
}
```

### 安全机制

1. **API 密钥认证**
   - 每个游戏平台分配唯一的 API Key 和 Secret
   - 所有请求必须包含有效的 API Key

2. **签名验证**
   - 所有请求必须包含签名
   - 使用 HMAC-SHA256 或 MD5 签名
   - 防止请求被篡改

3. **IP 白名单**
   - 限制游戏平台的 IP 地址
   - 只允许白名单内的 IP 访问

4. **频率限制**
   - 限制每个游戏平台的请求频率
   - 防止恶意请求

### 监控和统计

1. **支付统计**
   - 按游戏平台统计支付金额
   - 按支付网关统计支付金额
   - 按国家/地区统计支付金额

2. **性能监控**
   - 监控 API 响应时间
   - 监控支付成功率
   - 监控网关可用性

3. **异常告警**
   - 支付失败率过高时告警
   - 网关故障时告警
   - API 异常时告警

---

## 相关链接

- [PaymentHUB 产品页面](https://codecanyon.net/item/paymenthub-simplify-online-payment-with-multiple-gateways/45305230)
- [PaymentHUB 文档](https://paymenthub.yourdomain.com/docs)（需要根据实际文档地址修改）
- [支付宝开放平台](https://open.alipay.com/)
- [微信支付商户平台](https://pay.weixin.qq.com/)

---

## 更新日志

- **2025-01-XX**：初始版本，完成基础集成文档
- **2025-01-XX**：添加多层架构说明、多支付网关对接方案、对接上游游戏平台章节

