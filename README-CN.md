# 害羞框架 Shy Framework

她是一个娇小，却能顶半边天的框架；她支持很多便捷的特性和用法，同时也保持着灵巧的身姿。功能强大却没有复杂的实现，快来了解一下她吧！

## 1. 概要

### 1.1 简介

本框架是以容器为基础的，就连框架本身都在容器里面，由此为开发者提高了一个自由度极高的实例复用池。在容器的基础上，框架核心提供了异常处理、门面、流水线等实用特性。在框架核心的基础之上，提供了web服务、终端服务、api服务等。

### 1.2 特性列表

* 容器
* 异常、错误处理
* 流水线
* 中间件
* 请求
* 响应
* 路由
* 控制器
* 门面
* 数据库
* 模版
* 单元测试
* 命令模式
* api（暂未实现）
* socket（暂未实现）

### 1.3 用法

##### 第一步

```bash
git clone https://github.com/lynncho/shy.git
```

##### 第二步

```bash
composer install
```

### 1.4 目录结构

```
shy 框架根目录
|
|   phpunit.php 单元测试入口
|   console 命令模式入口
|   server.php web服务本地调试入口
|
|___app 开发目录
|    |
|    |___console 终端程序开发目录
|    |___http web程序开发目录
|
|___cache 缓存
|    |
|    |___app 系统缓存目录
|    |___log 日志缓存目录
|
|___config 配置
|
|___public web服务开放目录
|   |
|   |___vendor 前端第三方资源包目录
|
|___shy 框架目录
|   |
|   |   api.php api服务
|   |   console.php 终端服务
|   |   web.php web服务
|   |
|   |___console 终端服务目录
|   |___core 核心服务目录
|   |___http web服务目录
|
|
|___tests 单元测试目录
```

### 1.5 术语

* 实例："类"在实例化之后叫做一个"实例"。 "类"是静态的，不占进程内存，而"实例"拥有动态内存。
* 抽象名称：容器内的实例对应的唯一名称。
* 链式调用：对象的方法返回对象，以便继续调用下一个方法。
* 回调：被传入其它函数或方法执行的函数或方法。

### 2. 容器

容器本身是一个实例概念，这个实例可以集中储存其它实例，故名容器。容器是本框架的基础，所有贯穿整个生命周期、或者需要复用的实例都应该加入到容器里面，包括框架核心类的实例。容器类提供了便捷的绑定、加入、取用、替换或清除实例的方法，并且提供了相应的封装函数。下面主要讲的是这些封装函数。

##### 2.1 绑定实例

###### 2.1.1 用法模式

bind(抽象名称, 实例或回调)

###### 2.1.2 代码示例

```php
/**
 * 绑定实例
 */
bind('web', new shy\web());

/**
 * 绑定回调（可用于延迟传参、即加入容器时传参，支持任意个参数、即0到N个参数）
 */
bind('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
});

/**
 * 链式调用：绑定实例、加入到容器并取用实例、执行实例的run方法
 */
bind('web', new shy\web())->shy('web')->run();

/**
 * 链式调用：绑定回调、带参执行回调并加入容器然后返回实例、执行实例的run方法
 */
bind('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
})->shy('web', $param1, $param2)->run();

```

如上所述，bind函数是对容器的封装，会返回容器实例以便链式调用。它的功能只是绑定抽象名称对应的实例或回调，并没有将实例加入到真正意义上的容器，所以也无法从容器中获取绑定的实例。将实例加入容器、从容器中取用实例，是shy函数的功能。

##### 2.2 加入、取用实例

###### 2.2.1 用法模式

1. shy(抽象名称, 任意个其它参数、即0到N个)
2. shy(抽象名称, 实例或回调, 任意个其它参数)
3. shy(命名空间类名, 任意个其它参数)
4. shy(抽象名称, 命名空间类名, 任意个其它参数)

###### 2.2.2 代码示例

```php
/**
 * 运行绑定的回调
 */
shy('web');

/**
 * 带参运行绑定的回调
 */
shy('web', $parma1, $param2);

/**
 * 直接将实例加入容器
 */
shy('web', new shy\web());

/**
 * 直接带参运行回调
 */
shy('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
}, $param1, $param2);

/**
 * 命名空间作为抽象名称，带参实例化命名空间类
 */
shy('shy\web', $param1, $param2);

/**
 * 设置抽象名称并实例化带命名空间的类
 */
shy('web','shy\web');

/**
 * 设置抽象名称为pdo，并实例化带命名空间的类
 *
 * 这种做法是错误的。因为pdo本身是实际存在的php拓展类，不可以作为其它实例的抽象名称。
 */
shy('pdo', 'shy\core\library\pdo');

```

shy函数是框架的核心函数之一，代表对容器的操作。

该函数运行时，如果容器内存在待加入的抽象名称，此时不会做加入操作，而且直接返回该抽象名称对应的旧实例。如果不存在，才会将该抽象名称及其对应的实例加入到容器中，并返回被加入的实例。

该函数将实例加入到容器之前，会尝试获取实例，比如从绑定的实例获取、或者执行绑定的回调来实例化对象。如果之前没有绑定实例或者回调，该函数会根据上述2.2.1的用法模式、尝试各种可能实例化对象。实例加入容器之后，会清除绑定以免占用内存。

如何知道抽象名称是否已存在？你可以通过`shy_list()`函数获取容器内的所有抽象名称，或者使用`in_shy_list()`函数判断是不是已存在。

##### 2.3 替换实例

###### 2.3.1 用法模式

1. make_new(抽象名称, 任意个其它参数、即0到N个)
2. make_new(抽象名称, 实例或回调, 任意个其它参数)
3. make_new(命名空间类名, 任意个其它参数)
4. make_new(抽象名称, 命名空间类名, 任意个其它参数)

```php

make_new('web', new shy\web());

make_new('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
});

```

如果需要替换容器中的实例，不应该使用shy函数，应该用make_new函数。这两个函数的用法是一样的，所以这里没有给出完整的代码示例。

##### 2.4 清除实例

```php
/**
 * 清除抽象名称为web的绑定和实例
 */
shy_clear('web');

/**
 * 清除所有绑定和实例
 */
shy_clear_all();

```


**由上述内容可见，本框架为开发者提供了开放容器、并且包括框架核心在内的实例都在容器里面，可以很方便地操作。这提升了开发自由度，但是，开发者在操作框架核心类的时候，一定要仔细梳理逻辑，以免产生问题。**

### 3. 门面

门面提供了便捷的静态调用方法，通过魔术方法`__callStatic()`调用**被代理类**中的方法。

实现**门面代理类**需要继承框架的**门面类**，代码示例如下：

```php
namespace app\http\facade;

use shy\core\facade;

class testBusiness extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('app\http\business\testBusiness');
    }
}

```

由此可见，**门面代理类**的`getInstance()`方法重写了**门面类**中的该方法，将**被代理类**的实例传给了**门面类**。参考**2.2加入、取出实例**章节，以便创建、获取正确的实例。

### 4. 流水线（pipeline）

流水线是本框架重要的调度工具，连通了包括路由、中间件、控制器在内的整个框架的运行流程。

pipeline方法讲解：

1. send：设置传入参数，参数数量没有限制；
2. through：设置流水线的处理对象；
3. via：设置传入对象执行的方法，默认执行handle方法；
4. then：流水线的执行方法，同时会设置传入回调，传入的第一个参数为回调；
5. run：流水线的不需要回调时的执行方法，不可与then方法链式调用。

开发者可使用流水线来构建自己的调度，使用代码实例如下：

```php
/**
 * 框架web模块的运行，带回调执行
 */
shy('pipeline')
    ->send(shy('request'))
    ->through('router')
    ->then(function ($response) {
        if (!empty($response)) {
            shy('response')->send($response);
        }

        $this->end($response);
    });

```

**开发者使用流水线自定义调度流程时，应当仔细梳理运行流程。**

### 5. 中间件

中间件是流水线then方法传入"运行控制器的回调"时的特例，传入的第一个参数`$next`即是用于运行控制器方法的回调函数。

```php
namespace app\http\middleware;

use shy\core\middleware;

class example implements middleware
{
    public function handle($next, ...$passable)
    {
        // 请求处理
        $request = null;

        // 执行控制器
        $response = $next();

        // 响应处理
        $response = ', example middleware, ' . json_encode($response);
        return $response;
    }
}

```

### 6. 路由

路由通过请求类获取请求路径、并解析出对应控制器和方法，然后通过流水线调度控制器。

路由支持"配置模式"和"路径模式"，可以在配置文件`app.php`中关闭、启用。配置模式根据请求路径查找配置文件，得到控制器及其方法，支持中间件、路径前缀。路径模式是直接把请求路径当成控制器及其方法，属于传统方法。两种模式同时启用时，配置模式优先。

配置模式的文件`router.php`示例：

```php
return [
    'group' => [
        ['middleware' => ['example'], 'path' => [
            '/test2' => 'test2@test2',//echo string with middleware
            '/test3' => 'test2@test3'//return string with middleware
        ]],
        ['prefix' => 'route', 'path' => [
            '/home' => 'home@index',//view home
            '/test' => 'home@test'//return string
        ]]
    ],
    'path' => [
        '/home2' => 'home@test2',//return string
        '/home3' => 'home@home3'//404
    ]
];

```

### 7. 控制器

控制器中的方法应该返回数据、以便交由框架输出，开发者不应该直接在控制器内输出。在控制器内使用实例，需要注意该用单例还是新建实例，取容器内的实例、包括门面都是单例。

### 8. 数据库

本框架暂时只提供了pdo、redis和mysqli的封装类。

### 9. 模版

框架自带模版没有采用字符解析这种复杂的设计，因为这种方式不仅实现复杂、还制定了一套模版规则需要用户学习。本框架的模版需要使用原生PHP语法开发，并且只提供了必须的一小部分函数给开发者使用，学习、调试成本较低。但是，要求开发者做好`isset()`、`empty()`、`is_array()`等预防报错处理。

此外，为了满足开发者的需求，框架支持了Smarty模版系统。

##### 9.1 模版类方法

1. view：设置模版文件
2. layout：设置布局页文件
3. with：向模版传入参数
4. render：渲染模版，框架会自动执行渲染

##### 9.2 模版函数

1. view：模版类的封装。用于便捷地在控制器中使用模版，可传参、也可链式调用。每次使用本函数都会在容器中新建或替换模版实例。
2. include_sub_view：在布局页中输出模版。
3. include_view：在布局页、模版中引入组件模版。
4. param：在模版中输出变量或常量，不使用该函数输出报错无法被框架正常处理。

在控制器方法中使用view函数：

```php
public function index()
{
    $info = 'Hello World';
    $title = 'Shy Framework';

    return view('home', compact('title', 'info'))->layout('main');
}

```

include_sub_view函数在布局页中设置模版输出位置、include_view函数引入模版组件、param函数输出变量和常量：
```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php param('title') ?></title>
    <link type="text/css" rel="stylesheet" href="<?php param('BASE_URL') ?>css/app.css">
</head>
<body>
<?php include_sub_view() ?>
<?php include_view('component/footer') ?>
</body>
</html>

```

##### 9.3 Smarty模版

本框架提供了对smarty模版的支持，需要通过composer安装smarty、并在配置文件`app.php`中启用。

```bash
composer require smarty/smarty
```

###### 9.3.1 在控制器中调用smarty模版代码示例

```php
return smarty('smarty.tpl', $params);
```

###### 9.3.2 smarty模版代码示例

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <link type="text/css" rel="stylesheet" href="{BASE_URL}css/app.css">
</head>
<body>
<div id="hello-world">
    {$info}
</div>
<div id="system">
    <p>Memory Peak:{memory_get_peak_usage()/1024} kb</p>
    <p>Used Time: {microtime(true) - SHY_START} second</p>
    <br>
    <p>Loaded instance's abstract: </p>
    <ol>
        {foreach shy_list_memory_used() as $abstract => $memoryUsed}
            <li>{$abstract}  {$memoryUsed} kb</li>
        {/foreach}
    </ol>
</div>
{include file='component/footer.php'}
</body>
</html>

```

### 10. 杂项函数

1. config：获取配置文件的配置
2. config_all：获取配置文件的所有配置
3. logger：记录日志
4. dd：调试输出

### 11. 命令模式

本框架支持命令模式

在项目根目录执行下述命令可以查看所有命令：

```php
php console list
```

如果你需要拓展命令，可以在配置文件`console.php`中配置命令名称、对象和方法。

拓展命令的代码示例：

```php
/**
 * Example command
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\console;

class example
{
    public function test()
    {
        return 'Just for fun';
    }

}

```
