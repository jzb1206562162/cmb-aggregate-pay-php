# 招商银行商户聚合支付接口 V3.4.1

## 文档概述

本文档为招商银行商户聚合支付接口技术规范，面向商户平台服务方的技术架构师、研发工程师、测试工程师和系统运维工程师。

> 注意：JSON 格式的报文需兼容多返回字段的情况。

---

## 目录

1. [术语](#1-术语)
   - [1.1 文档说明](#11-文档说明)
   - [1.2 术语](#12-术语)
2. [接口规则](#2-接口规则)
   - [2.1 服务器地址](#21-服务器地址)
   - [2.2 协议规定](#22-协议规定)
   - [2.3 参数规定](#23-参数规定)
   - [2.4 安全规范](#24-安全规范)
   - [2.5 未知交易通知机制](#25-未知交易通知机制)
   - [2.6 优惠信息字段说明](#26-优惠信息字段说明)
   - [2.7 字段说明](#27-字段说明)
3. [业务流程](#3-业务流程)
4. [订单接口](#4-订单接口)
5. [错误代码说明](#5-错误代码说明)
6. [附录](#附录)

---

## 1. 术语

### 1.1 文档说明

本文阅读对象：商户平台服务方涉及的技术架构师、研发工程师、测试工程师、系统运维工程师。

版本说明：持续更新，详见文档更新日志。

### 1.2 术语

#### 1.2.1 支付模式

1. **收款码主扫支付**：商户系统通过 HTTPS 请求调用聚合收单平台 API 生成动态聚合银标码，用户使用微信、支付宝、银联钱包等第三方 APP "扫一扫"完成支付。适用于 PC 网站支付、实体店单品或订单支付、媒体广告支付等场景。

2. **被扫用户付款码支付**：用户展示微信、支付宝或银联钱包内的"刷卡条码/二维码"给商户系统扫描后直接完成支付。主要应用于线下面对面收银场景。

3. **微信统一下单**：支持微信公众号支付、小程序支付和 APP 支付三种模式。公众号/小程序支付是用户在微信内进入商家页面，通过 JSAPI 接口调起微信支付模块完成支付；APP 支付是用户在商户 APP 内集成 SDK 调起支付模块完成支付。

4. **支付宝服务窗支付**：用户通过支付宝扫码或打开分享链接进入商家页面，商户通过支付宝 JSAPI 接口调起支付模块完成支付。

#### 1.2.2 名词解释

| 名词 | 说明 |
|------|------|
| **聚合收单平台** | 提供聚合收单服务，融合多个支付渠道，一站式资金结算和对账。主要支付媒体包括 ERP 系统、收银台、聚合收款 APP、公众号、固定二维码。支付通道：支付宝、微信、银联二维码。 |
| **商户收银系统** | 商户的 POS 收银系统，负责录入商品信息、生成订单、客户支付、打印小票等功能。 |
| **商户后台系统** | 商户后台处理业务系统的总称，例如商户网站、收银系统、进销存系统、发货系统、客服系统等。 |
| **扫码设备** | 用于商户系统快速读取媒介上图形编码信息的输入设备。 |
| **商户证书** | （暂未启用）聚合收单平台提供的二进制文件，作为识别商户真实身份的凭据。 |
| **签名** | 采用 SM2 算法校验双方身份合法性。商户需验证应答签名，未正确验证签名存在潜在风险。 |
| **支付密码** | 用户开通微信、支付宝、银联钱包等支付时单独设置的密码。 |
| **Openid** | 用户在聚合收单平台内的身份标识，不同商户号拥有不同的 openid。 |

---

## 2. 接口规则

### 2.1 服务器地址

#### 测试环境

| 接口 | 地址 |
|------|------|
| 收款码申请 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/qrcodeapply` |
| 支付结果查询 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/orderquery` |
| 退款申请 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/refund` |
| 退款结果查询 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/refundquery` |
| 付款码收款 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/pay` |
| 微信统一下单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/onlinepay` |
| 付款码收款撤销 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/cancel` |
| 关闭订单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/close` |
| 支付宝服务窗支付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/servpay` |
| 支付宝 native 支付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/zfbqrcode` |
| 对账单下载地址获取 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/statementurl` |
| 秘钥设置 | `https://api.cmburl.cn:8065/polypay/v1.0/mchkey/keyset` |
| 订单二维码申请 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/orderqrcodeapply` |
| 微信小程序下单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/MiniAppOrderApply` |
| 银联云闪付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/cloudpay` |
| 数字人民币统一下单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/ecny/unifiedOrder` |
| 数字人民币统一支付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/ecny/unifiedPayment` |
| 数字人民币子钱包支付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/ecny/subwalletpay` |
| 数字人民币子钱包支付-带合约 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/ecny/contractsubwalletpay` |
| 数字人民币统一下单-带合约 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/ecny/contractUnifiedOrder` |
| 微信委托代扣 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/pap` |
| 微信委托代扣查询 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/paporderquery` |
| 微信委托代扣-支付分 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/pap2` |
| 支付宝 APP 支付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/zfbapp` |
| 支付宝手机网站支付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/zfbwap` |
| 微信支付分预授权 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/permissions` |
| 微信支付分预授权查询 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/querypermissions` |
| 微信支付分解除授权 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/terminatepermissions` |
| 微信支付分创建订单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/order` |
| 微信支付分完结订单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/completeorder` |
| 微信支付分查询订单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/queryorder` |
| 微信支付分取消订单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/cancelorder` |
| 微信支付分修改订单金额 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payscore/modifyorder` |
| 微信授权码查询 openid | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/openidqrybyac` |
| 支付宝先享后付 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/payafteruser/pay` |
| 支付宝商户前置内容咨询 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/alipay/marketing/consult` |
| 支付宝吱口令获取 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/alipay/sharetoken/create` |
| 订单码关闭订单 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/orderqrcodeclose` |
| 智能合约分账（新） | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/contract/benefit` |
| 智能合约分账结果查询 | `https://api.cmburl.cn:8065/polypay/v1.0/mchorders/contract/benefitquery` |

#### 生产环境

| 接口 | 地址 |
|------|------|
| 收款码申请 | `https://api.cmbchina.com/polypay/v1.0/mchorders/qrcodeapply` |
| 支付结果查询 | `https://api.cmbchina.com/polypay/v1.0/mchorders/orderquery` |
| 退款申请 | `https://api.cmbchina.com/polypay/v1.0/mchorders/refund` |
| 退款结果查询 | `https://api.cmbchina.com/polypay/v1.0/mchorders/refundquery` |
| 付款码收款 | `https://api.cmbchina.com/polypay/v1.0/mchorders/pay` |
| 微信统一下单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/onlinepay` |
| 付款码收款撤销 | `https://api.cmbchina.com/polypay/v1.0/mchorders/cancel` |
| 关闭订单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/close` |
| 支付宝服务窗支付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/servpay` |
| 支付宝 native 支付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/zfbqrcode` |
| 对账单下载地址获取 | `https://api.cmbchina.com/polypay/v1.0/mchorders/statementurl` |
| 订单二维码申请 | `https://api.cmbchina.com/polypay/v1.0/mchorders/orderqrcodeapply` |
| 微信小程序下单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/MiniAppOrderApply` |
| 银联云闪付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/cloudpay` |
| 数字人民币统一下单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/ecny/unifiedOrder` |
| 数字人民币统一支付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/ecny/unifiedPayment` |
| 数字人民币子钱包支付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/ecny/subwalletpay` |
| 数字人民币子钱包支付-带合约 | `https://api.cmbchina.com/polypay/v1.0/mchorders/ecny/contractsubwalletpay` |
| 数字人民币统一下单-带合约 | `https://api.cmbchina.com/polypay/v1.0/mchorders/ecny/contractUnifiedOrder` |
| 微信委托代扣 | `https://api.cmbchina.com/polypay/v1.0/mchorders/pap` |
| 微信委托代扣查询 | `https://api.cmbchina.com/polypay/v1.0/mchorders/paporderquery` |
| 微信委托代扣-支付分 | `https://api.cmbchina.com/polypay/v1.0/mchorders/pap2` |
| 支付宝 APP 支付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/zfbapp` |
| 支付宝手机网站支付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/zfbwap` |
| 微信支付分预授权 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/permissions` |
| 微信支付分预授权查询 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/querypermissions` |
| 微信支付分解除授权 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/terminatepermissions` |
| 微信支付分创建订单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/order` |
| 微信支付分完结订单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/completeorder` |
| 微信支付分查询订单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/queryorder` |
| 微信支付分取消订单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/cancelorder` |
| 微信支付分修改订单金额 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payscore/modifyorder` |
| 微信授权码查询 openid | `https://api.cmbchina.com/polypay/v1.0/mchorders/openidqrybyac` |
| 支付宝先享后付 | `https://api.cmbchina.com/polypay/v1.0/mchorders/payafteruser/pay` |
| 支付宝商户前置内容咨询 | `https://api.cmbchina.com/polypay/v1.0/mchorders/alipay/marketing/consult` |
| 支付宝吱口令获取 | `https://api.cmbchina.com/polypay/v1.0/mchorders/alipay/sharetoken/create` |
| 订单码关闭订单 | `https://api.cmbchina.com/polypay/v1.0/mchorders/orderqrcodeclose` |
| 智能合约分账（新） | `https://api.cmbchina.com/polypay/v1.0/mchorders/contract/benefit` |
| 智能合约分账结果查询 | `https://api.cmbchina.com/polypay/v1.0/mchorders/contract/benefitquery` |

> 注意：交易结果通知是银行主动通知给商户的，不提供商户主动调用的 URL。

### 2.2 协议规定

商户接入聚合收单平台调用 API 遵循以下规则：

1. **传输方式**：建议采用 HTTPS 传输
2. **提交方式**：采用 POST 方法提交
3. **数据格式**：提交和返回数据都为 JSON 格式，Content-Type 设置为 `application/json`。所有业务字段（除版本号、编码方式、签名、签名方法、返回码、应答码、错误码、应答信息字段外）都须放在 `biz_content` 中传输
4. **字符编码**：统一采用 UTF-8 字符编码
5. **签名算法**：SM2 国密算法
6. **签名要求**：请求和接收数据均需要校验签名
7. **APP ID 校验**：商户接入时平台会分配一对 APP ID 和 APP SECRET
8. **判断逻辑**：先判断协议字段返回，再判断业务返回
9. **支付/退款交易**：若返回 `returnCode` 为 FAIL 且 `errCode` 为 `SYSTERM_ERROR`，表示平台内部发生未知错误，需要商户系统发起查询直到结果明确
10. **查询接口**：若返回 `returnCode` 为 FAIL 且 `errCode` 为 `SYSTERM_ERROR`，并非一定是交易失败，需继续发起查询
11. **商户必须实现查询机制**：以防网络抖动等异常情况下未及时收到通知，若未实现查询机制风险由商户自行承担
12. **扩展兼容**：招行返回的报文参数可能超出接口定义范围，商户需对增加的参数进行验签处理，在业务处理阶段忽略处理

### 2.3 参数规定

1. **交易金额**：默认为人民币交易，单位为【分】，参数值不能带小数。外币交易精确到币种最小单位
2. **货币类型**：156 为人民币
3. **回调通知地址（notifyUrl）**：
   - 平台从后台直接发送请求到商户后台系统，不能检查用户 cookie/session
   - 可能重复通知，商户需做幂等处理
   - 处理成功后返回 `SUCCESS` 标志
   - 网络开通预计需要 2-3 个工作日
4. **时间**：标准北京时间，时区为东八区
5. **时间戳**：自 1970年1月1日 0点0分0秒以来的秒数（10位数字）
6. **商户订单号**：商户自定义生成，需保持唯一性。已支付或已关单/撤销的订单号不能重新发起支付
7. **非必填字段**：不可填写空格、null 或空字符串等无实际含义内容
8. **商品描述（body）**：若不上送，则默认展示商户简称

### 2.4 安全规范

#### 2.4.1 签名算法

**签名机制：**

1. **筛选并排序**：获取所有请求参数，剔除 `sign` 字段，按 ASCII 码递增排序
2. **拼接**：组合成"参数=参数值"的格式，用 `&` 连接，生成待签名字符串
3. **生成签名值**：采用 SM2withSM3 签名算法，签名方式为 PKCS#1 裸签名，签名 USER_ID 使用国密局推荐 ID `1234567812345678`
4. **组装报文**：将签名赋值给 `sign` 参数，与其他请求参数一起通过 HTTPS POST 传输

**验签机制：**

- **同步返回报文验签**：获取所有响应参数剔除 `sign`，按 ASCII 码递增排序，拼接后用 SM2 公钥验签
- **异步通知验签**：除去 `sign`，将剩余参数进行 url_decode 后字典排序，使用招行公钥验签

**国密秘钥标准规范：**

- SM2 非对称私钥：32 字节字节流，HEX 格式为 64 字节
- SM2 非对称公钥：base64 格式，符合 ANS1 标准，base64 编码后总长度为 124 字节

#### 2.4.2 商户回调 API 安全

建议商户提供的各种回调采用 HTTPS 协议，防止 DNS 劫持、数据被窃取等安全风险。

#### 2.4.3 APP ID 校验

商户接入前会分配一对 APP ID 和 APP SECRET。请求时需将 APP ID 放在报文头中，并对 APP ID、APP SECRET、报文体中的 sign 值、Linux 格式时间戳按 KEY 值首字母排序并用 `&` 连接，然后用 MD5 算法加签（小写字母）。将时间戳、加签结果和 APP ID 一起放在请求报文头中。

#### 2.4.4 敏感信息加密

前提：需完成国密加验签改造。使用 terminalInfo 等敏感信息字段时，需使用国密加密方式传输：

1. 组装字段对象并将其转换为 JSON 字符串
2. 商户自行生成对称秘钥（如 `1234567890123456`）
3. 使用对称秘钥进行 SM4 加密，Base64 编码生成密文
4. 使用招行公钥对该对称秘钥进行 SM2 加密，Base64 编码得到数字信封
5. 将密文放入对应字段，数字信封放入 `encryptKey` 字段

#### 2.4.5 安全防护要求

商户必须遵循测试环境接入流程，未经测试禁止直接接入生产环境。严禁发送任何异常流量，包括但不限于探测、端口扫描、暴力破解、漏洞扫描、拒绝服务攻击等。系统有权对请求 TPS 做限制，严重者可拒绝所有请求。

### 2.5 未知交易通知机制

当平台通过 notifyUrl 发送通知时，若收到应答中 returnCode 和 respCode 不是 `SUCCESS` 或验签失败，平台会按周期重新发起通知。

**通知频率（单位：秒）：**
0/15/15/30/180/600/1200/1800/1800/1800/3600/10800/10800/10800/21600/21600

商户后台系统必须正确处理重复通知，建议采用幂等处理：检查业务数据状态，判断通知是否已处理过。在处理前采用同步锁进行并发控制。

### 2.6 优惠信息字段说明

#### 2.6.1 商品优惠信息（itemDiscount）

微信、支付宝或银联单品优惠功能字段，需使用单品优惠时必传。

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| cost_price | 否 | int | 订单原价，单位分 |
| receipt_id | 否 | String(32) | 商家小票 ID |
| order_info | 否 | object | 银联订单信息 |
| goods_detail | 是 | object | 单品列表，JSON 数组格式 |

**goods_detail 字段说明：**

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| goods_id | 是 | String(32) | 商品编码 |
| third_goods_id | 否 | String(32) | 第三方统一商品编号 |
| goods_name | 否 | String(256) | 商品名称 |
| quantity | 是 | int | 购买数量 |
| price | 是 | int | 商品单价，单位分 |
| goods_category | 否 | String(24) | 商品类目 |
| categories_tree | 否 | String(128) | 商品类目树 |
| body | 否 | String(1000) | 商品描述信息 |
| show_url | 否 | String(400) | 商品展示地址 |
| addn_info | 否 | String(100) | 附加信息 |

#### 2.6.2 优惠券信息（promotionDetail）

微信、支付宝通道交易使用了优惠券支付成功之后返回的优惠券使用信息包。

**微信优惠券信息：**

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| promotion_id | 是 | String(32) | 券或立减优惠 id |
| name | 否 | String(64) | 优惠名称 |
| scope | 否 | String(32) | 优惠范围（GLOBAL/SINGLE） |
| type | 否 | String(32) | 优惠类型（COUPON/DISCOUNT） |
| amount | 否 | int | 优惠券面额 |
| activity_id | 是 | String(32) | 活动 ID |
| wxpay_contribute | 否 | String(32) | 微信出资 |
| merchant_contribute | 否 | String(32) | 商户出资 |
| other_contribute | 否 | String(32) | 其他出资 |
| goods_detail | 是 | String | 单品列表 |

**支付宝优惠券信息：**

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| id | 是 | String(32) | 优惠券 id |
| name | 是 | String(64) | 优惠券名称 |
| type | 是 | String(32) | 类型（ALIPAY_FIX_VOUCHER/ALIPAY_DISCOUNT_VOUCHER/ALIPAY_ITEM_VOUCHER） |
| amount | 是 | Price | 优惠券面额 |
| merchant_contribute | 否 | Price | 商家出资 |
| other_contribute | 否 | Price | 其他出资方出资 |
| memo | 否 | String(256) | 优惠券备注信息 |
| template_id | 否 | String(64) | 券模板 id |

#### 2.6.3 银联优惠信息（couponInfo）

银联通道交易有优惠活动时返回的 JSON 对象。

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| type | 是 | String(4) | 项目类型（DD01/CP01/CP02） |
| spnsrId | 是 | String(20) | 出资方 |
| offstAmt | 是 | String(12) | 抵消交易金额，单位分 |
| id | 是 | String(40) | 项目编号 |
| desc | 否 | String(60) | 优惠活动简称 |
| addnInfo | 否 | String(100) | 附加信息 |

#### 2.6.4 微信退款详情（refundDetail）

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| promotion_id | 是 | String(32) | 券或立减优惠 id |
| scope | 否 | String(32) | 优惠范围 |
| type | 否 | String(32) | 优惠类型 |
| amount | 否 | int | 优惠券面额 |
| refund_amount | 否 | int | 按比例退款的优惠券金额 |

#### 2.6.5 支付宝商品列表（goodsDetail）

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| goods_id | 是 | String(32) | 商品编号 |
| alipay_goods_id | 否 | String(32) | 支付宝统一商品编号 |
| goods_name | 是 | String(256) | 商品名称 |
| quantity | 是 | int | 商品数量 |
| price | 是 | int | 商品单价，单位元 |
| goods_category | 否 | String(24) | 商品类目 |
| categories_tree | 否 | String(128) | 商品类目树 |
| body | 否 | String(1000) | 商品描述信息 |
| show_url | 否 | String(400) | 展示地址 |

#### 2.6.6 数字人民币优惠详情（ecnyPromotionDetail）

返回 Array 格式字符串。

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| promotionGenInstId | 是 | String(14) | 营销活动运营机构 |
| promotionId | 是 | String(32) | 营销活动编码 |
| promotionNm | 是 | String(60) | 营销活动名称 |
| promotionDescInfo | 否 | String(256) | 营销活动描述信息 |
| promotionType | 是 | String(4) | 营销活动类型（PT01-PT99） |
| couponId | 是 | String(64) | 权益 id |
| couponExpiry | 是 | String(19) | 权益有效期 |
| couponAmountCcy | 是 | String(3) | 权益金额币种 |
| couponAmount | 是 | String(18) | 权益金额 |
| couponRefundProperty | 是 | String(5) | 权益退款属性 |
| couponDescInfo | 否 | String(256) | 权益描述信息 |
| remarkInfomation | 否 | String(128) | 备注信息 |

### 2.7 字段说明

#### 2.7.1 终端信息（terminalInfo）

| 变量名 | 必填 | 类型 | 描述 |
|--------|------|------|------|
| location | 否 | String(32) | 终端实时经纬度信息，格式为纬度/经度 |
| network_license | 否 | String(5) | 银行卡受理终端产品应用认证编号 |
| device_type | 是 | String(2) | 设备类型（04:智能POS, 09:刷脸付终端, 10:条码支付受理终端, 11:条码支付辅助受理终端） |
| serial_num | 否 | String(50) | 终端序列号 |
| device_id | 是 | String(8) | 终端编号 |
| encrypt_rand_num | 否 | String(10) | 加密随机因子 |
| secret_text | 否 | String(16) | 密文数据 |
| app_version | 否 | String(8) | 应用程序版本号 |
| device_ip | 否 | String(40) | 商户端终端设备 IP 地址 |
| mobile_country_cd | 否 | String(3) | 移动国家代码（中国为460） |
| mobile_net_num | 否 | String(2) | 移动网络号码 |
| icc_id | 否 | String(20) | SIM 卡卡号 |
| country_no | 否 | String(2) | 终端位置国家编码 |
| area_no | 否 | String(6) | 终端位置地区编码 |

#### 2.7.2 智能合约请求信息

**合约收款请求内容（contractReq）：**

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| ctrOrderContent | CtrOrderContent | 是 | 合约请求内容 |
| ctrExtra | ContractExtra | 是 | 合约附加内容 |

**ContractExtra：**

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| ctrCode | String(128) | 是 | 合约模板名称 |
| expiredTime | String(19) | 是 | 合约过期时间，格式 yyyy-MM-dd HH:mm:ss |

**合约退款请求内容（contractRefundReq）：**

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| ctrRefundContent | CtrRefundContent | 是 | 合约退款请求内容 |
| ctrExtra | ContractExtra | 是 | 合约附加内容 |

**合约分账请求内容（contractBenefitReq）：**

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| ctrBenefitContent | CtrBenefitContent | 是 | 合约分账请求内容 |
| ctrExtra | ContractExtra | 是 | 合约附加内容 |
| contractId | String(64) | 是 | 合约 ID |

#### 2.7.3 数币支付参数（ecnyPayment）

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| transactionType | String(4) | 是 | 交易类型（TT01:扫码支付） |
| terminalNo | String(32) | 是 | 受理终端编号（禁止中文） |
| terminalIp | String(64) | 是 | 受理终端 IP（禁止中文） |
| goodsName | String(40) | 是 | 商品名称（不可使用特殊字符） |
| orderDetails | String(1024) | 否 | 订单详情 |
| platformName | String(40) | 否 | 网络交易平台名称 |
| tradePlace | String(128) | 是 | 交易地点 |
| orderTimeExpire | String(19) | 是 | 订单失效时间，格式 yyyy-mm-ddTHH:MM:SS |

#### 2.7.4 交易地址信息（tradeAddressInfo）

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| phone | String(11) | 否 | 手机号 |
| ip | String(40) | 否 | IP 地址 |
| mac | String(17) | 否 | MAC 地址 |
| device | String(128) | 否 | 设备信息（JSON 格式） |
| terminal | String(64) | 否 | 终端号 |
| location | String(32) | 否 | 经纬度 |

#### 2.7.5 商户营销信息（PayChannelPromoInfo）

| 变量名 | 类型 | 必填 | 备注 |
|--------|------|------|------|
| channel_name | String(512) | 否 | 渠道名称 |
| channel_enable | Boolean | 否 | 渠道可用性标识 |
| channel_operation_info | String(1024) | 否 | 渠道信息 |

#### 2.7.6 运营展示数据（viewData）

（根据实际业务需求配置）

#### 2.7.7 支付宝退款资金渠道（refundDetailItemList）

支付宝退款时返回的退款资金渠道明细。

---

## 3. 业务流程

### 3.1 场景介绍

聚合收单平台支持多种支付场景，包括主扫支付、被扫支付、微信公众号/小程序/APP 支付、支付宝服务窗/APP/H5 支付、银联云闪付、数字人民币支付等。

### 3.2 案例介绍

典型业务流程：商户系统下单 → 调用聚合收单 API → 用户完成支付 → 平台异步通知商户 → 商户查询确认 → 完成交易。

### 3.3 业务流程

1. 商户后台系统调用聚合收单平台 API 创建订单
2. 用户通过微信/支付宝/银联/数币完成支付
3. 聚合收单平台通过 notifyUrl 异步通知商户支付结果
4. 商户后台系统返回 `SUCCESS` 确认收到通知
5. 商户可通过查询接口主动查询支付/退款结果

### 3.4 状态机

订单状态包括：待支付、支付成功、已关闭、已退款等。商户需根据状态机正确管理订单生命周期。

---

## 4. 订单接口

### 接口列表

| 序号 | 接口名称 | 说明 |
|------|----------|------|
| 4.1 | 收款码申请 | 生成动态聚合银标码 |
| 4.2 | 支付结果查询 | 查询订单支付状态 |
| 4.3 | 支付结果通知 | 平台异步通知商户支付结果 |
| 4.4 | 退款申请 | 发起退款 |
| 4.5 | 退款结果查询 | 查询退款状态 |
| 4.6 | 退款结果通知 | 平台异步通知商户退款结果 |
| 4.7 | 关闭订单 | 关闭未支付订单 |
| 4.8 | 付款码收款 | 被扫用户付款码收款 |
| 4.9 | 付款码支付撤销 | 撤销被扫支付交易 |
| 4.10 | 微信统一下单 | 微信 JSAPI/APP/小程序支付 |
| 4.11 | 服务窗支付 | 支付宝服务窗支付 |
| 4.12 | 支付宝 native 码支付 | 支付宝扫码支付 |
| 4.13 | 对账单下载地址获取 | 获取对账单下载 URL |
| 4.14 | 订单二维码申请 | 商户申请订单二维码 |
| 4.15 | 微信小程序下单 | 微信小程序支付下单 |
| 4.16 | 银联云闪付 | 银联云闪付下单 |
| 4.17 | 数字人民币统一下单 | 数字人民币预下单 |
| 4.18 | 数字人民币统一支付 | 数字人民币支付 |
| 4.19 | 数字人民币子钱包支付 | 数字人民币子钱包支付 |
| 4.20 | 数字人民币子钱包支付-带合约 | 带智能合约的子钱包支付 |
| 4.21 | 数字人民币统一下单-带合约 | 带智能合约的统一下单 |
| 4.22 | 微信委托代扣 | 微信委托代扣 |
| 4.23 | 微信委托代扣查询 | 查询委托代扣结果 |
| 4.24 | 支付宝 APP 支付 | 支付宝 APP 内支付 |
| 4.25 | 支付宝手机网站支付 | 支付宝 H5 支付 |
| 4.26 | 微信支付分预授权 | 微信支付分预授权 |
| 4.27 | 微信支付分预授权查询 | 查询预授权状态 |
| 4.28 | 微信支付分解除授权 | 解除微信支付分授权 |
| 4.29 | 微信支付分预授权通知 | 预授权结果通知 |
| 4.30 | 微信支付分创建订单 | 创建微信支付分订单 |
| 4.31 | 微信支付分完结订单 | 完结微信支付分订单 |
| 4.32 | 微信支付分查询订单 | 查询微信支付分订单 |
| 4.33 | 微信支付分撤销订单 | 撤销微信支付分订单 |
| 4.34 | 微信支付分修改订单金额 | 修改微信支付分订单金额 |
| 4.35 | 微信支付分确认订单通知 | 确认订单通知 |
| 4.36 | 智能合约交易分账（新） | 智能合约分账 |
| 4.37 | 微信授权码查询 openid | 通过授权码查询 openid |
| 4.38 | 支付宝先享后付 | 支付宝先享后付统一收单 |
| 4.39 | 微信刷脸获取调用凭证 | 获取刷脸调用凭证 |
| 4.40 | 支付宝商户前置内容咨询 | 支付宝营销内容咨询 |
| 4.41 | 支付宝吱口令获取 | 获取支付宝吱口令 |
| 4.42 | 订单码关闭订单 | 关闭订单码订单 |
| 4.43 | 智能合约交易分账结果查询 | 查询分账结果 |

### 通用请求/响应结构

所有接口遵循统一的请求和响应报文结构。

#### 通用请求参数

| 参数名 | 必填 | 说明 |
|--------|------|------|
| version | 是 | 版本号 |
| charset | 是 | 字符编码（UTF-8） |
| sign | 是 | SM2 签名值 |
| signType | 是 | 签名类型 |
| biz_content | 是 | 业务参数（JSON 字符串） |

#### 通用响应参数

| 参数名 | 说明 |
|--------|------|
| returnCode | 返回码（SUCCESS/FAIL） |
| respCode | 应答码 |
| errCode | 错误码 |
| respMsg | 应答信息 |
| sign | 签名值 |

各接口具体的请求参数和返回参数请参考原始接口文档或联系招行业务人员获取。

---

## 5. 错误代码说明

### 5.1 接口返回的 returnCode 说明

| 值 | 说明 |
|----|------|
| SUCCESS | 请求成功 |
| FAIL | 请求失败 |

### 5.2 接口返回的 respCode 说明

respCode 表示业务处理结果，具体取值请参考附录中的错误码列表。

### 5.3 接口返回的 errCode 说明

| 错误码 | 说明 |
|--------|------|
| SYSTERM_ERROR | 平台内部未知错误，需发起查询确认交易状态 |

---

## 附录

### 附录 1：接入参数说明

测试环境接入时需联系招行业务人员获取 APP ID、APP SECRET、SM2 公私钥等接入参数。

### 附录 2：报文样例

各接口的报文样例请参考原始接口文档。

### 附录 3：错误码列表

完整的错误码列表请参考原始接口文档。

### 附录 4：错误码处理机制参考

当收到错误响应时，商户应根据 returnCode、respCode、errCode 组合判断处理方式：
- returnCode=SUCCESS：请求处理成功，按正常业务流程处理
- returnCode=FAIL + errCode=SYSTERM_ERROR：平台内部错误，需发起查询
- 其他错误：根据具体错误码进行处理

### 附录 5：微信支付分对象结构

微信支付分相关接口的对象结构定义请参考原始接口文档。

---

## 文档变更记录

| 序号 | 版本号 | 变更内容 | 影响分析 | 变更时间 |
|------|--------|----------|----------|----------|
| 1 | V2.1 | 收款码申请字段增加 tradeScene 字段；所有接口里的 tradeScene 字段改为必填 | 所有商户需要确认是否有按要求上送此字段，若无则需改造 | 2019.10 |
| 2 | V2.2 | 删除附录1中的测试商户账号信息；修改聚合码描述，聚合码支持扫多次；支付宝 native 支付支持实名支付；微信统一下单返回 prepayId 字段长度调整为36；微信统一下单、支付宝 native 支付、退款请求接口增加订单原始金额和订单优惠金额字段；用户扫聚合码失败后可使用相同支付方式再次扫码支付；支付宝 native 支付请求接口增加实名支付字段；商户可通过微信统一下单和支付宝 native 接口上送自己的优惠信息 | 商户需评估招行增加返回字段是否有影响 | 2019.11 |
| 3 | V2.3 | 支持支付宝免充值优惠券；联机报文增加返回付款银行、第三方订单号、支付宝账户；微信统一下单增加返回用户唯一标识；报文签名支持 SM2 算法；支持行内业务网下载对账单，支持下载集团账单 | 商户需评估招行增加返回字段是否有影响。增量商户需采用 SM2 进行报文签名处理 | 2020.02 |
| 4 | V2.4 | 微信统一下单接口 subOpenId 字段改为必填；付款码收款增加 itemDiscount 和 promotionDetail 支持微信支付宝单品优惠 | 微信、支付宝单品优惠功能 | 2020.07 |
| 5 | V2.5 | 新增订单二维码申请接口 | 商户申请订单二维码，在用户未扫码支付前，允许商户再次调用 API 修改订单码的收款金额；用户扫描订单码未支付情况下，可选择再次使用相同或更换其它支付方式扫码支付 | 2020.09 |
| 6 | V2.6 | 新增微信小程序下单接口；删除微信 Native 支付接口 | 用户通过在商户 APP 完成下单，然后商户跳转到招行微信小程序，用户在微信小程序中完成支付 | 2020.10 |
| 7 | V2.7 | 新增银联云闪付下单接口 | 银联云闪付功能 | 2020.11 |
| 8 | V2.7.1 | 新增商品优惠信息字段；付款码收款新增上送 itemDiscount；支付结果查询返回 issAddnData；退款申请新增上送 acqAddnData，增加返回 issAddnData, goodsDetail, refundDetail；退款结果查询/通知新增返回 issAddnData, refundDetail | — | 2020.12 |
| 9 | V2.8 | 修改签名方式，支持国密签名 | 修改支持国密签名 | 2020.04 |
| 10 | V2.8.1 | 去除文档中关于 RSA 签名算法相关描述；删除附录中秘钥设置相关报文 | 无 | 2020.07 |
| 11 | V2.8.2 | 修改附录1测试环境主扫支付地址；tradeScene 增加枚举值 | 无 | 2021.08 |
| 12 | V2.8.3 | 服务窗支付和支付宝 native 码支付新增 alipayExtendParams | 无 | 2021.10 |
| 13 | V2.9.0 | 收款码申请、付款码收款、微信统一下单、服务窗支付、支付宝 native 码支付、订单二维码申请、微信小程序下单等接口请求参数新增终端信息 terminalInfo；付款码收款增加微信设备号 deviceInfo | 无 | 2021.12 |
| 14 | V2.9.1 | 新增数字人民币统一下单接口；新增数字人民币统一支付接口；支付结果查询/通知返回参数 payType 增加枚举值 EC 数字人民币；退款结果通知请求参数 payType 增加枚举值 EC 数字人民币 | 无 | 2022.01 |
| 15 | V2.9.2 | 新增数字人民币子钱包支付接口 | 无 | 2022.02 |
| 16 | V2.9.3 | 收款码申请、付款码收款、微信统一下单、服务窗支付、支付宝 native 支付、订单二维码申请、微信小程序下单接口删除请求参数中的 tradeScene | 支持已上送 tradeScene 字段继续上送 | 2022.03 |
| 17 | V2.9.4 | 新增带合约的数字人民币子钱包支付接口；支付结果查询响应参数增加合约收款响应内容字段；退款申请请求/响应参数增加合约退款请求/响应内容字段；退款结果查询/通知增加合约退款响应/请求内容字段 | 无 | 2022.09 |
| 18 | V2.9.5 | 付款码收款请求参数增加数币支付参数 ecnyPayment；响应参数支付方式 payType 枚举值增加 EC：数字人民币 | — | 2022.09 |
| 19 | V2.9.6 | 收款码申请、付款码收款、服务窗支付、支付宝 native 码支付请求参数增加 businessParams 支付宝-商户传入业务信息 | — | 2022.11 |
| 20 | V2.9.7 | 新增数字人民币统一下单-带合约接口；支付结果通知接口请求参数增加合约响应内容 contractResp；数字人民币子钱包支付响应参数增加备注 remark | — | 2023.01 |
| 21 | V2.9.8 | 微信统一下单、微信小程序下单请求参数增加指定支付者 limitPayer；新增微信委托代扣接口；新增微信委托代扣查询接口 | — | 2023.01 |
| 22 | V2.9.9 | 收款码申请、退款申请、付款码收款、微信统一下单、服务窗支付、支付宝 native 码支付、订单二维码申请、微信小程序下单、银联云闪付、数字人民币统一下单、数字人民币统一支付、数字人民币子钱包支付、数币人民币子钱包支付-带合约、数字人民币统一下单-带合约、微信委托代扣请求参数增加交易地址信息 | — | 2023.01 |
| 23 | V3.0.0 | 新增微信委托代扣-支付分接口；数字人民币统一下单接口请求参数新增 policyNo 和 region，支持 APP 或小程序拉起数币支付；退款查询接口增加返回失败原因 failureReason | — | 2023.02 |
| 24 | V3.0.1 | 支付结果查询响应参数、支付结果通知请求参数、数字人民币子钱包支付响应参数增加付款运营机构编码 debtorAgentId，付款运营机构名称 debtorAgentName | — | 2023.03 |
| 25 | V3.0.2 | 数字人民币统一下单响应参数 context 里面增加加密证书序列号 ncrptnSN | — | 2023.04 |
| 26 | V3.0.3 | 服务窗支付请求参数 buyerId 字段长度改成28；退款申请响应参数增加失败原因 failureReason | — | 2023.05 |
| 27 | V3.1.0 | 新增支付宝 APP 支付接口；新增支付宝手机网站支付接口 | — | 2023.06 |
| 28 | V3.1.1 | 支付宝 APP 支付、支付宝手机网站支付接口请求参数新增实名信息 identity，收银员 userId 改成10位 | — | 2023.07 |
| 29 | V3.1.2 | 数字人民币子钱包支付、数字人民币子钱包支付-带合约接口请求参数新增订单失效时间 orderTimeExpire | — | 2023.08 |
| 30 | V3.1.3 | 支付结果查询/退款结果查询响应参数增加返回数字人民币优惠详情 ecnyPromotionDetail；支付结果通知/退款结果通知请求参数新增 ecnyPromotionDetail；数字人民币子钱包支付响应参数增加返回优惠金额 dscAmt 和 ecnyPromotionDetail | — | 2023.09 |
| 31 | V3.1.4 | 服务窗支付、支付宝 native 支付、支付宝 APP 支付、支付宝手机网页支付新增同步跳转地址 returnUrl | — | 2023.10 |
| 32 | V3.1.5 | 服务窗支付、支付宝 native 码支付、支付宝手机网站支付、支付宝 APP 支付请求参数实名支付信息 identity 字段中的 type 支持身份证 IDCARD、护照 PASSPORT、军官证 OFFICER_CARD、士兵证 SOLDIER_CARD、户口本 HOKOU | — | 2023.11 |
| 33 | V3.1.6 | 终端信息支持上送终端位置国家编码和终端位置地区编码，并随 device_type 和 device_id 描述进行调整；付款码收款接口 termId 字段描述调整 | — | 2023.12 |
| 34 | V3.1.7 | 新增微信支付分预授权、预授权查询、解除授权、预授权通知、创建订单、完结订单、查询订单、取消订单、修改订单金额、确认订单通知接口 | — | 2024.02 |
| 35 | V3.1.8 | 新增智能合约交易分账接口 | — | 2024.04 |
| 36 | V3.1.9 | 新增微信授权码查询 openid；订单码申请接口请求参数删除 mchStoreId | — | 2024.05 |
| 37 | V3.1.10 | 收款码申请、退款申请、付款码收款、微信统一下单、服务窗支付、支付宝 native 码支付、订单二维码申请、微信小程序下单、银联云闪付、微信委托代扣、支付宝 APP 支付、支付宝手机网站支付、微信支付分创建订单、微信支付分完结订单新增附加经营商户 subMerId 和经营商户门店号 subStoreId | — | 2024.05 |
| 38 | V3.2.0 | 新增支付宝先享后付-统一收单交易支付；支付结果查询请求参数和响应参数增加外部商户订单号 outOrderId；退款申请请求参数增加原外部商户订单号 origOutOrderId；退款结果查询请求参数增加原外部商户订单号 origOutOrderId；关闭订单请求参数和响应参数增加外部商户订单号 origOutOrderId | — | 2024.05 |
| 39 | V3.2.1 | 收款码申请、退款申请、付款码收款、微信统一下单、服务窗支付、支付宝 native 码支付、订单二维码申请、微信小程序下单、银联云闪付、数字人民币统一下单、数字人民币统一支付、数字人民币子钱包支付、数字人民币子钱包支付-带合约、数字人民币统一下单-带合约、微信委托代扣、支付宝 APP 支付、支付宝手机网站支付修改涉及敏感信息字段 encryptIdentity、encryptTerminalInfo、encryptTradeAddressInfo | — | 2024.07 |
| 40 | V3.3.0 | 微信支付订单创建通知地址描述修改；微信支付分订单确认通知新增字段 notifyType；银联二维码仿真测试地址更新 | — | 2024.08 |
| 41 | V3.3.1 | 新增微信刷脸获取调用凭证接口 | — | 2024.08 |
| 42 | V3.3.2 | 增加安全防护要求章节 | — | 2024.09 |
| 43 | V3.3.4 | 新增支付宝商户前置内容咨询接口；新增支付宝吱口令获取接口 | — | 2024.11 |
| 44 | V3.3.5 | 支付宝先享后付-统一收单交易支付请求参数 timeoutExpress 要求必填 | — | 2024.12 |
| 45 | V3.3.7 | 支付结果查询返回参数新增字段 hbFqPayInfo；支付结果通知请求参数新增字段 hbFqPayInfo | — | 2025.03 |
| 46 | V3.3.8 | 退款申请返回参数新增支付宝退款资金渠道 refundDetailItemList；退款结果查询返回参数新增支付宝退款资金渠道 refundDetailItemList | — | 2025.04 |
| 47 | V3.3.9 | 关闭订单增加无需重复调用关单的错误码 | — | 2025.06 |
| 48 | V3.4.0 | 支付结果查询增加返回订单码状态 qrCodeState；新增订单码关单接口 | — | 2025.08 |
| 49 | V3.4.1 | 新增智能合约交易分账(新)接口；新增智能合约分账结果查询接口；数字人民币子钱包支付–带合约、数字人民币统一下单–带合约、退款申请修改合约请求参数，详见智能合约请求信息 | — | 2025.09 |

---

> **免责声明**：本文档内容提取自招商银行商户聚合支付接口 V3.4.1 官方技术文档，仅供参考。实际开发请以招商银行官方提供的最新文档为准。
