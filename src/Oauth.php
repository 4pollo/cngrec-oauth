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
namespace Gdk\Oauth;

class Oauth
{

    /**
     * 操作句柄
     * @var object
     * @access protected
     */
    protected static $handler = null;

    /**
     * 连接oauth
     * @access public
     * @param array $options  配置数组
     * @return object
     */
    public static function connect($options = [])
    {
        $type  = $options['oauth_type'];
        $class = 'Gdk\\Oauth\\Driver\\' . ucwords($type);
        if (class_exists($class)) {
            unset($options['oauth_type']);
            self::$handler = new $class($options);
            return self::$handler;
        }
        return false;
    }

    // 跳转到授权登录页面
    public static function login($callback = '')
    {
        self::$handler->login($callback);
    }

    // 设置TOKEN信息
    public static function setToken($token = [])
    {
        self::$handler->setToken($token);
    }

    // 获取access_token
    public static function getAccessToken($code = '')
    {
        return self::$handler->getAccessToken($code);
    }

    // 获取oauth用户信息
    public static function getOauthInfo()
    {
        return self::$handler->getOauthInfo();
    }

    // 获取oauth用户信息
    public static function getOpenId()
    {
        return self::$handler->getOpenId();
    }

    // 获取错误信息
    public static function getError()
    {
        return self::$handler->getError();
    }

    // 调用oauth接口API
    public static function call($api, $param = '', $method = 'GET')
    {
        return self::$handler->call($api, $param, $method);
    }

}
