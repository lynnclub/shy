<?php

/**
 * test Method
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\business;

class testBusiness
{
    static private $_instance;

    private $isMobile;

    private function __construct()
    {
        // not allow new outside
    }

    private function __clone()
    {
        // not allow clone outside
    }

    public static function instance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function isMobile()
    {
        if ($this->isMobile) {
            return true;
        }
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $client_keywords = [
                //common
                'mobile',
                'wap',
                //os
                'android',
                'blackberry',
                'ios',
                'openwave',
                'palm',
                'symbian',
                'windows ce',
                //browser
                'mqqbrowser',
                'operamobi',
                'operamini',
                'ucweb',
                'fennec',
                'netfront',
                //device
                'ipad',
                'iphone',
                'ipod',
                'acer',
                'asus',
                'coolpad',
                'etouch',
                'nokia',
                'sony',
                'ericsson',
                'huawei',
                'mi',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'meizu',
                'nexus'
            ];
            if (preg_match("/(" . implode('|', $client_keywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $this->isMobile = true;
                return true;
            }
        }
        return false;
    }
}