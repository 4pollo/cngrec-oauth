<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Gdk\Oauth\Driver;

use Gdk\Oauth\Driver\Driver;

class Wechat extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://open.weixin.qq.com/connect/qrconnect';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'scope=snsapi_login&state=cngrec#wechat_redirect';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://api.weixin.qq.com/';

    /**
     * 请求code
     */
    public function getRequestCodeURL()
    {
        //Oauth 标准参数
        $params = array(
            'appid'         => $this->appKey,
            'redirect_uri'  => $this->callback,
            'response_type' => $this->responseType,
        );

        //获取额外参数
        if ($this->authorize) {
            parse_str($this->authorize, $_param);
            if (is_array($_param)) {
                $params = array_merge($params, $_param);
            } else {
                throw new Exception('AUTHORIZE配置不正确！');
            }
        }
        return $this->getRequestCodeURL . '?' . http_build_query($params);
    }

    /**
     * 获取access_token
     * @param string $code 授权登录成功后得到的code信息
     */
    public function getAccessToken($code)
    {
        $params = array(
            'appid'      => $this->appKey,
            'secret'     => $this->appSecret,
            'grant_type' => $this->grantType,
            'code'       => $code,
        );
        // 获取token信息
        $data = $this->http($this->getAccessTokenURL, $params, 'POST');
        // 解析token
        $this->token = $this->parseToken($data);
        return $this->token;
    }

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 微信调用公共参数 */
        $params = array(
            'access_token' => $this->token['access_token'],
            'openid'       => $this->token['openid'],
        );

        $data = $this->http($this->url($api), $this->param($params, $param), $method);
        return json_decode($data, true);
    }

    /**
     * 解析access_token方法请求后的返回值
     * @param string $result 获取access_token的方法的返回值
     */
    protected function parseToken($result)
    {
        $json = json_decode($result,true);
        if (!$json || !empty($json['errcode'])) {
            $this->errMsg = $json['errmsg'];
            return false;
        }
        return $json;
    }

    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function getOpenId()
    {
        return $this->token['openid'];
    }

    public function getOauthInfo()
    {
        $data = $this->call('sns/userinfo', 'lang=zh_CN');

        if (isset($data['errcode'])) {
            $this->errMsg = "获取微信用户信息失败：{$data['errmsg']}";
            return false;
        } else {
            $userInfo['usertype'] = 'WECHAT';
            $userInfo['username'] = $data['nickname'];
            $userInfo['headimg'] = str_replace('http://', 'https://', $data['headimgurl']);
            return $userInfo;
        }
    }
}
