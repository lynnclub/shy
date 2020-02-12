<?php

/**
 * test
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace App\Http\Business;

class TestBusiness
{
    public function isMobile()
    {
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
                return true;
            }
        }
        return false;
    }
}