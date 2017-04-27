oauth登录接口，代码来自TP官方库

## 安装
> composer require 4pollo/cngrec-oauth

## 使用
```
// 链接QQ登录
Oauth::connect(['oauth_type'=>'qq','app_key'=>'','app_secret'=>'','callback'=>'','authorize'=>'']);

// 跳转到授权登录页面 或者 Oauth::login($callbackUrl);
Oauth::login();

// 调用API接口
Oauth::call('api','params');
```
