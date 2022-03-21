<?php

return [
    /**
     * 基础地址，等同于BASE_URL
     * Equivalent to BASE_URL
     */

    'base_url' => '',

    /**
     * 调试开关，开启时输出错误
     * Debug switch, output error when turned on.
     */

    'debug' => true,

    /**
     * 缓存开关，影响配置、路由等
     * Cache switch, affecting config, router, etc.
     */

    'cache' => false,

    /**
     * 设置时区
     * Time zone
     *
     * PRC, Asia/Shanghai, Asia/Tokyo...
     */

    'timezone' => 'PRC',

    /**
     * 默认语言
     * Default language
     */

    'default_lang' => 'zh-CN',

    /**
     * 通过配置解析路由
     * Parse route by config
     */

    'route_by_config' => true,

    /**
     * 通过路径解析路由
     * Parse route by path
     */

    'route_by_path' => true,

    /**
     * 默认控制器，通过路径解析根目录时使用
     * Default controller, used when parse the root directory by path.
     */

    'default_controller' => 'home',
];
