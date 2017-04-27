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

class Qq extends Driver
{
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $getRequestCodeURL = 'https://graph.qq.com/oauth2.0/authorize';

    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $getAccessTokenURL = 'https://graph.qq.com/oauth2.0/token';

    /**
     * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
     * @var srting
     */
    protected $authorize = 'state=cngrec&scope=get_user_info,add_share';

    /**
     * API根路径
     * @var string
     */
    protected $apiBase = 'https://graph.qq.com/';

    /**
     * 组装接口调用参数 并调用接口
     * @param  string $api    微博API
     * @param  string $param  调用API的额外参数
     * @param  string $method HTTP请求方法 默认为GET
     * @return json
     */
    public function call($api, $param = '', $method = 'GET')
    {
        /* 腾讯QQ调用公共参数 */
        $params = array(
            'oauth_consumer_key' => $this->appKey,
            'access_token'       => $this->token['access_token'],
            'openid'             => $this->token['openid'],
            'format'             => 'json',
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
        parse_str($result, $data);
        if (isset($data['access_token']) && isset($data['expires_in'])) {
            $this->token = $data;
            $data['openid'] = $this->getOpenId();

            return $data;
        } else {
            $this->errMsg = "获取腾讯QQ ACCESS_TOKEN 出错：{$result}";
        }
        return false;
    }

    /**
     * 获取当前授权应用的openid
     * @return string
     */
    public function getOpenId()
    {
        $data = $this->token;
        if (!empty($data['openid'])) {
            return $data['openid'];
        }

        if ($data['access_token']) {
            $data = $this->http($this->url('oauth2.0/me'), array('access_token' => $data['access_token']));
            $data = json_decode(trim(substr($data, 9), " );\n"), true);
            if (isset($data['openid'])) {
                return $data['openid'];
            }
        }
        return null;
    }

    public function getOauthInfo()
    {
        $data = $this->call('user/get_user_info');

        if ($data['ret'] == 0) {
            $userInfo['usertype'] = 'QQ';
            $userInfo['username'] = $data['nickname'];
            $userInfo['headimg']  = str_replace('http://', 'https://', $data['figureurl_2']);
            return $userInfo;
        } else {
            $this->errMsg = "获取腾讯QQ用户信息失败：{$data['msg']}";
        }
        return false;
    }
}
