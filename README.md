# Shy Framework

**简洁却强大的高性能框架**

**框架还需要不断的优化、完善，欢迎提交问题、建议。**

[English Document](https://github.com/lynncho/shy/blob/master/README-EN.md)

Shy框架交流一群：963908345 加群暗号：lynncho

## 一、 纲要

### 1.1 简介

框架遵守PHP-FIG组织制定的PSR（PHP Standards Recommendations）规范。

框架以容器（Container）为核心，提供了便捷的实例（Instance）复用池。框架的所有服务组件都在容器里面，通过操作容器可以非常自由地定制框架。

框架以非常简洁的实现，提供了众多服务。核心服务包括：异常处理、配置文件、日志、缓存、门面、流水线等。基于核心服务，提供了面向使用场景的Web框架、命令框架、Socket框架，并且彼此可独立运行、互不干扰。

此外，**框架还提供一种颠覆PHP语言传统Web框架运行方式的模式：常驻内存模式（PHP-CLI）。在常驻内存模式下，由于避免了重复加载、重复编译的开销，本框架的Web服务性能相对于传统方式大幅度提升！！！**

整个框架实现简洁，但是十分强大。正如她的名字：**纤细身形、不漏内秀**。

### 1.2 特性列表

* 契约（Contracts）
* 容器与依赖注入（Container and Dependency Injection）
* 门面（Facade）
* 缓存（Cache）
* 配置（Config）
* 异常及错误的捕获处理（Exceptions Handler）
* 流水线（Pipeline）
* 请求（Request）
* 中间件（Middleware）
* 响应（Response）
* 路由（Router）
* 控制器（Controllers）
* 数据库（DataBase）
* 模版（View）
* 命令模式（Command Mode）
* 常驻内存模式（PHP-CLI Mode）
* Socket模式（Socket Mode）
* Api（未完成）
* 单元测试（Unit Test）
* 常量（Constants）

### 1.3 用法

##### 第一步：克隆项目

```bash
git clone https://github.com/lynncho/shy.git
```

##### 第二步：安装依赖包

```bash
#进入项目目录
cd shy

#执行composer
composer install
```

恭喜！！！

接下来，只需要配置Web站点、或者执行常驻内存模式，就可以运行框架了。

##### 第三步（可选）：常驻内存模式执行

```bash
php console http start
```

### 1.4 目录结构

```
shy 框架根目录
|
|   phpunit.php 单元测试入口
|   console 命令模式入口
|   server.php Http服务调试入口（整个项目暴露在web服务下，建议调试使用）
|   gulpfile.js Gulp前端构建服务
|
|___app 开发目录
|    |
|    |___Console 命令服务开发目录
|    |___Http Http服务开发目录
|    |___Socket Socket服务开发目录
|
|___bootstrap 服务启动目录
|    |
|    |___console.php 命令服务启动程序
|    |___http.php Http服务启动程序
|    |___http-workerman.php 基于workerman的常驻内存服务启动程序
|
|___cache 缓存目录
|    |
|    |___app 系统缓存目录
|    |
|    |___log 日志目录
|    |
|    |___instances_record 实例运行数据目录（常驻内存模式、容器实例智能调度）
|
|___config 配置目录
|    |
|    |___ develop 开发环境配置目录
|    |___ testing 测试环境配置目录
|    |___ production 生产环境配置目录
|
|___public Http服务开放目录
|   |
|   |   index.php Http服务入口
|   |
|   |___vendor 前端第三方资源包目录
|
|___shy 框架目录
|   |
|   |   Console.php 命令服务
|   |   Http.php Http服务
|   |   HttpInWorkerMan.php 基于workerman的常驻内存Http服务
|   |   SocketInWorkerMan.php 基于workerman的Socket服务
|   |
|   |___Console 命令模式目录
|   |___Core 核心服务目录
|   |___Http Http服务目录
|   |___Socket Socket服务目录
|
|
|___tests 单元测试目录
|
|___vendor composer依赖包安装目录
```

### 1.5 术语

* 实例："类"或者说"对象"，在实例化之后叫做一个"实例"。类只是代码，而实例占有内存、是运行中的状态。
* 链式调用：对象的方法返回对象，以便继续调用返回对象的方法。
* 回调：一种函数或类方法，被传入其它函数或类方法中执行。
* 闭包：即匿名函数。

## 二、 契约（Contracts）

契约是接口（interface）的广义概念，意义相同，但是不局限于接口的形式。契约可以是接口、抽象类、甚至非硬性约束的惯例。

框架的各种组件都按照契约的规范实现，比如容器、缓存、配置、日志、门面等。部分组件，契约中包含了PSR规范。

**契约在容器中的使用方式：容器中的类名为契约类名，容器中对应的实例是实现了契约的实体类。遵守相同契约的实体类可以在容器中随意替换。**

## 三、 容器与依赖注入（Container and Dependency Injection）

本框架的容器类，遵守PSR（PHP Standards Recommendations）中的《PSR-11: Container interface》接口规范；并且，实现了PHP的ArrayAccess、Countable接口，可以当作数组使用。

容器是对实例集中管理的实例池，可以创建、绑定、使用、替换或者移除实例。此外，框架拓展了容器的概念，支持把字符、数组等任意内容当作实例管理。即容器是实例与数据的集中管理池。

使用容器需要注意：

1. 需要复用的、贯穿框架生命周期的实例或者数据，应该加入到容器；
2. **框架的核心服务及配置，可以被自由访问，但是需要对框架设计有足够的了解，才能操作。**

### 3.1 绑定实例

#### 3.1.1 用法模式

支持绑定实例或者数据，绑定结果为闭包、供加入容器时（调用shy函数）执行。

1. bind(类名)：类名是需要绑定的类
2. bind(数据名)：数据名是需要绑定的数据
3. bind(类名, 实例或回调)：类名对应传入的实例或回调执行的实例
4. bind(数据名, 数据)：数据名对应传入的数据

#### 3.1.2 代码示例

```php
/**
 * 绑定类名
 */
bind(Shy\Http::class);

/**
 * 直接绑定实例
 */
bind(Shy\Http::class, new Shy\Http());
bind('Shy\Http', new Shy\Http());

/**
 * 绑定回调（可用于延迟传参、即加入容器时传参，支持任意个参数、即0到N个参数）
 */
bind(Shy\Http::class, function ($param1, $param2) {
    return new Shy\Http($param1, $param2);
});

/**
 * 链式调用：绑定实例、加入到容器并取用实例、执行实例的run方法
 */
bind(Shy\Http::class, new Shy\Http())->shy(Shy\Http::class)->run();

/**
 * 链式调用：绑定回调、带参执行回调并加入容器然后返回实例、执行实例的run方法
 */
bind(Shy\Http::class, function ($param1, $param2) {
    return new Shy\Http($param1, $param2);
})->shy(Shy\Http::class, $param1, $param2)->run();
```

如上所述，bind函数是对容器类的封装，会返回容器实例以便链式调用。**它的功能只是绑定实例，并没有将实例加入到容器**。将实例加入容器、使用容器中实例，是shy函数的功能。

### 3.2 加入与使用实例

#### 3.2.1 用法模式

1. shy(类名, null, 任意个实例化参数)：类名作为实体类
2. shy(类名, 实体类名, 任意个实例化参数)：类名绑定实体类
2. shy(类名, 实例或回调, 任意个实例化参数)：类名绑定实例或者回调

#### 3.2.2 代码示例

```php
/**
 * 实例化Http类
 */
shy(Shy\Http::class);

/**
 * 带参数实例化
 */
shy(Shy\Http::class, null, $param1, $param2);

/**
 * 带参数实例化File类，契约为Logger
 */
shy(Shy\Core\Contracts\Logger::class, Shy\Core\Logger\File::class, $param1, $param2);

/**
 * 使用契约为Logger的File实例
 */
shy(Shy\Core\Contracts\Logger::class);

/**
 * 直接将实例加入容器
 */
shy(Shy\Http::class, new Shy\Http());

/**
 * 可以在bootstrap目录的服务对应启动文件中，设置类名的别名
 */
$container->alias('pipeline', Shy\Core\Contracts\Pipeline::class);

/**
 * 使用别名
 */
shy('pipeline');
```

**shy函数是框架的核心函数，代表对容器的操作**。

使用shy函数时，如果容器内已经存在指定类名的实例，则直接返回该实例。如果不存在，会将实例加入到容器中，并返回被加入的实例。

该函数会尝试上述用法模式来获取实例。比如，从绑定的实例中获取、执行绑定的回调获取实例、通过反射实例化类。

实例加入容器之后，会清除绑定以免占用内存。

### 3.3 更多操作

更多操作请直接使用容器类。通过使用shy函数、不传参数，可以获取到容器实例。

```bash
/**
 * 无参数、返回容器本身
 */
shy();

/**
 * 是否存在实例
 */
shy()->has(Shy\Http::class);

/**
 * 创建实例、已存在实例会替换实例
 */
shy()->make(Shy\Http::class);

/**
 * 移除实例
 */
shy()->remove(Shy\Http::class);
```

### 3.4 依赖注入

容器执行闭包、或者通过反射实例化类的时候，会自动注入构造方法的依赖，无需手动实例化。

```php
use Shy\Http\Contracts\Request;
use Shy\Core\Contracts\Config;

/**
 * Logger constructor.
 *
 * @param Request $request
 * @param Config $config
 */
 public function __construct(Config $config, Request $request = null)
 {
     $this->config = $config;

     $this->request = $request;
 }
```

如上所述，Logger类的构造方法依赖Config和Request契约类参数。容器通过契约类型的变量，可以自动注入契约绑定的实体类。

## 四、 门面（Facade）

门面提供了便捷的静态调用方式。**门面类**的父类——**门面抽象类**，通过魔术方法`__callStatic()`调用**实体类**中的方法。

实现**门面类**需要继承框架的**门面抽象类**，并且重写父类的`getInstance()`方法，以便向父类传递**实体类**。实现代码示例如下：

```php
namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\Cache as CacheContract;

class Cache extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(CacheContract::class);
    }
}
```

可参考容器章节，以便理解如何获取**实体类**的实例。

## 五、 缓存（Cache）

本框架的缓存类，遵守PSR（PHP Standards Recommendations）中的《PSR-16: Common Interface for Caching Libraries》接口规范；并且，实现了PHP的ArrayAccess接口，可以当作数组使用。（由于PHP的Redis拓展不兼容PSR-16，所以框架的PSR规范非硬性要求）

框架提供了基于PHP的redis拓展实现的缓存`Shy\Core\Cache\Redis::class`，推荐优先使用。

此外，还实现了无依赖的内存缓存`Shy\Core\Cache\Memory::class`，其生命周期与框架相同。常驻内存模式下可以替代Redis使用。

可以在bootstrap目录下的服务启动文件中，替换缓存契约绑定的实体类：

```php
$container->bind(Shy\Core\Contracts\Cache::class, Shy\Core\Cache\Redis::class);
```

调用缓存门面的方法：

```php
use Shy\Core\Facades\Cache;

Cache::set('test', 123);

Cache::get('test');
```

## 六、 配置（Config）

配置继承了内存缓存类`Shy\Core\Cache\Memory::class`。因为该缓存无依赖，并且生命周期与框架相同，非常符合配置文件的需要。

使用方式：

```php
/**
 * 读取配置文件path.php的配置
 */
$configFile = config('path');

/**
 * 读取配置文件path.php中的，view配置
 */
$viewPath = config_key('view', 'path');

/**
 * 读取配置文件app.php中的，debug配置
 *
 * app.php是默认读取的
 */
$viewPath = config_key('debug');
```

## 七、 异常及错误的捕获处理（Exceptions Handler）

**框架在各个服务的入口处，注册了异常（Exception）及错误（Error）捕获，能够捕获并处理所有未被捕获的异常、错误、甚至是Shut Down。错误及Shut Down会被转化成异常，统一按异常处理。**

框架为每个服务提供了异常处理类（Handler）。你也可以实现Handler接口来自定义异常处理，再通过替换服务入口的Handler使用。

可以在bootstrap目录下的服务启动文件中，替换异常处理契约绑定的实体类：

```php
$container->bind(Shy\Core\Contracts\ExceptionHandler::class, Shy\Http\Exceptions\Handler::class);
```

对于需要返回Http Code的错误，可以抛出HttpException。该错误的响应会使用`errors/common.php`模版:

```php
use Shy\Http\Exceptions\HttpException;

throw new HttpException(403, lang(5000));
```

## 八、 流水线（Pipeline）

流水线是框架的调度工具，连通了包括路由、中间件、控制器在内的Web框架的运行流程。

**通过流水线调度，也可以享受容器的依赖注入服务**。

Pipeline类的方法讲解：

1. send：设置传入参数，参数数量没有限制；
2. through：设置流水线的处理对象；
3. via：设置传入对象执行的方法，默认执行handle方法；
4. then：流水线的执行方法，同时会设置传入回调，传入的第一个参数为回调；
5. run：流水线的不需要回调时的执行方法，不可与then方法链式调用。

开发者可使用流水线来执行自己的调度，使用代码实例如下：

```php
/**
 * 路由，带回调执行
 */
$response = shy('pipeline')
    ->send($request)
    ->through(RouterContract::class)
    ->then(function ($response) {
        if (!empty($response)) {
            shy('response')->send($response);
        }

        return $response;
    });

/**
 * 控制器，不带回调执行
 */
$response = shy(Pipeline::class)
    ->through($this->controller)
    ->via($this->method)
    ->run();
```

## 九、 请求（Request）

调用请求门面的方法：

```php
use Shy\Http\Facades\Request;

/**
 * 是否初始化
 */
Request::isInitialized();

/**
 * 全部请求
 */
Request::all();

/**
 * 数据流 php://input
 */
Request::content();

/**
 * 获取请求
 */
Request::get('key');
```

## 十、 中间件（Middleware）

中间件是请求与响应的中间步骤。如果，中间件在执行控制器之前执行，则称为“前置中间件”；否则，称“后置中间件”。

中间件是流水线（Pipeline）的一种特例，传入的第一个参数`$next`，是用于运行控制器方法的闭包。

如下，是有IP挡板功能的前置中间件：

```php
namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Exceptions\HttpException;
use Shy\Http\Facades\Request;
use Shy\Core\Facades\Logger;

class IpBaffle implements Middleware
{
    /**
     * Handle
     *
     * @param Closure $next
     * @param array ...$passable
     * @return mixed|string
     */
    public function handle(Closure $next, ...$passable)
    {
        Logger::info('Baffle block request', Request::all());

        $hit = false;

        $userIps = Request::getClientIps();

        foreach ($userIps as $userIp) {
            if (in_array($userIp, config('ip_baffle'))) {
                $hit = true;
            }
        }

        if (!$hit) {
            if (Request::ajax()) {
                return get_response_json(5000);
            } else {
                throw new HttpException(403, lang(5000));
            }
        }

        // run controller
        return $next();
    }

}
```

后置中间件的实例：

```php
use Shy\Core\Contracts\Middleware;
use Closure;

class Test implements Middleware
{
    /**
     * Handle
     *
     * @param Closure $next
     * @param array ...$passable
     * @return mixed|string
     */
    public function handle(Closure $next, ...$passable)
    {
        // run controller
        $response = $next();
        
        // do something
        $response .= 'Test';
        
        return $response;
    }

}
```

中间件需要在配置文件`middleware.php`中定义别名或者别名组，然后在路由中填写别名使用。

## 十一、 响应（Response）

对于控制器，只需要return数据或者模版，Response组件就会输出响应。建议交给框架处理响应，不要手动输出。

```php
/**
 * 返回字符串
 */
return 'controller echo';

/**
 * 返回模版
 */
return view('home', compact('title', 'info'))->layout('main');

/**
 * 返回Smarty模版
 */
return smarty('smarty.tpl', $params);
```

## 十二、 路由（Router）

路由通过请求（Request）获取请求路径，然后解析出对应控制器和方法，最终通过流水线调度控制器。

路由支持"配置模式"和"路径模式"，可以在配置文件`app.php`中关闭或启用。配置模式根据请求路径查找配置，得到控制器及其方法，支持中间件、路径前缀。路径模式是直接把请求路径当成控制器及其方法。**两种模式同时启用时，配置模式优先。推荐使用配置模式，以便使用中间件等功能**。

配置模式的路由配置文件`router.php`示例：

```php
<?php

return [
    'group' => [
        ['middleware' => ['example', 'group_example'], 'path' => [
            '/test2' => 'test2@test2',//echo string with middleware
            '/test3' => 'test2@test3'//return string with middleware
        ]],
        ['prefix' => 'route', 'path' => [
            '/home' => 'home@index',//view home
            '/test' => 'home@test'//return string
        ]],
        ['prefix' => 'controller_2', 'namespace' => 'App\\Http\\Controllers_2', 'path' => [
            '/home' => 'home@index',//view home
            '/test' => 'home@test',//return string
            '/smarty' => 'home@smarty'
        ]]
    ],
    'path' => [
        '/' => 'home@index',//view home
        '/home2' => 'home@test2',//return string
        '/smarty' => 'home@smarty',
        '/home3' => 'home@home3',//404
        '/home/path/test' => 'home@index',//view home
        '/testLang' => 'test2@testLang',//zh-CN
        '/testLang2' => 'test2@testLang2'//en-US
    ]
];
```

1. path配置了路径对应的控制器及其方法；
2. middleware是路径使用的中间件，可以配置多个中间件；
3. prefix是路径的前缀，可以将相同前缀的路径配置在一起；
4. middleware和prefix必须配置在group中。

路由配置文件在debug关闭的时候（一般是生产环境），会自动缓存路由的索引数据，导致对路由的修改不生效。可以手动删除`cache/app/router.cache`清除缓存。

## 十三、 控制器（Controllers）

1. 控制器中的方法应该返回（return）数据、以便交由框架响应组件输出，不应该直接在控制器内手动输出。
2. 在控制器内使用实例，建议优先使用门面或者依赖注入。

## 十四、 数据库（DataBase）

可以在bootstrap目录下的服务启动文件中，替换数据库契约绑定的实体类：

```php
/**
 * 默认使用Pdo
 */
$container->bind(Shy\Core\Contracts\DataBase::class, Shy\Core\DataBase\Pdo::class);
```

### 14.1 laravel的DB包

框架兼容laravel的DB包，你可以通过下面的命令安装此包：

```bash
composer require illuminate/database 5.5.44
```

在配置文件`database.php`中配置数据库，然后在启动文件中替换DataBase实体类，便可使用了。

使用方式如下：

```php
use Shy/Core/Facades/DB;

DB::table('users')->where('id', 2)->get();
```

Illuminate Database的更多用法，可以查看[该项目的文档](https://github.com/illuminate/database)

## 十五、 模版（View）

框架自带模版没有采用字符解析这种复杂的设计，因为这种方式不仅实现复杂、还制定了一套模版规则需要用户学习。

本框架的模版需要使用原生PHP语法开发，并且只提供了必须少量函数，学习成本较低。

但是，要求开发者做好`isset()`、`empty()`、`is_array()`等预防报错处理。

此外，为了满足开发者的需求，框架支持了Smarty模版系统。

### 15.1 自带模版的辅助函数

1. view：模版类的封装。用于便捷地在控制器中使用模版，可传参、也可链式调用。每次使用本函数都会在容器中新建或替换模版实例。
2. include\_sub\_view：在布局页中输出模版。
3. include\_view：在布局页、模版中引入组件模版。
4. param：在模版中输出变量或常量，不使用该函数输出报错无法被框架正常处理。

在控制器方法中使用view函数：

```php
public function index()
{
    $info = 'Hello World';
    $title = 'Shy Framework';

    return view('home', compact('title', 'info'))->layout('main');
    return view('home', compact('title', 'info'), 'main');//等价方法
}
```

include\_sub\_view函数，用于在布局页中输出子模版；include\_view函数引入模版组件；param函数输出变量和常量：

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title><?php param('title') ?></title>
    <link type="text/css" rel="stylesheet" href="<?php param('BASE_URL') ?>css/app.css">
    <?php push_resource('header-css') ?>
</head>
<body>
<?php include_sub_view() ?>
<?php include_view('component/footer') ?>
<?php push_resource('footer-js') ?>
</body>
</html>
```

布局页的子页：

```php
<?php

push_resource('footer-js', [get_param('BASE_URL') . 'vendor/jquery/dist/jquery.js', ''], 'js');

?>

<div id="hello-world">
    <?php param('info') ?>
    <?php param('not_exist_param', true) ?>
</div>
<div id="system">
    <p>Container Start Id: <?php echo shy()->startId(); ?></p>
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Running Time: <?php echo microtime(true) - shy()->startTime(); ?> second</p>
    <?php
    if (shy()->has('SHY_CYCLE_START_TIME')) { ?>
        <p>Recycling Time: <?php echo microtime(true) - shy()->get('SHY_CYCLE_START_TIME'); ?> second</p>
        <?php
    }
    ?>
    <br>
    <p>Loaded instances memory used: </p>
    <ul>
        <?php
        $instanceCount = 0;
        foreach (shy()->memoryUsed() as $abstract => $instances) {
            foreach ($instances as $key => $memoryUsed) {
                $instanceCount++;
                ?>
                <li><?php echo '[' . $instanceCount . '] ' . $abstract . '(' . ($key + 1) . ') ' . $memoryUsed / 1024 . ' kb'; ?></li>
            <?php }
        } ?>
    </ul>
</div>
```

### 15.2 Smarty模版

框架提供了对Smarty模版的支持，需要先安装Smarty包。

```bash
composer require smarty/smarty
```

#### 15.2.1 在控制器中调用Smarty模版实例

```php
return smarty('smarty.tpl', $params);
```

#### 15.2.2 Smarty模版实例

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="renderer" content="webkit">
    <title>{$title}</title>
    <link type="text/css" rel="stylesheet" href="{BASE_URL}css/app.css">
</head>
<body>
<div id="hello-world">
    {$info}
</div>
<div id="system">
    <p>Container Start Id: {$shy->startId()}</p>
    <p>Memory Peak:{memory_get_peak_usage()/1024} kb</p>
    <p>Running Time: {microtime(true) - $shy->startTime()} second</p>
    {if $shy->has('SHY_CYCLE_START_TIME')}
        <p>Recycling Time: {microtime(true) - $shy->get('SHY_CYCLE_START_TIME')} second</p>
    {/if}
    <br>
    <p>Loaded instance's abstract: </p>
    <ol>
        {foreach $shy->memoryUsed() as $abstract => $instances}
            {foreach $instances as $key => $memoryUsed}
                <li>{$abstract}({$key + 1}) {$memoryUsed/1024} kb</li>
            {/foreach}
        {/foreach}
    </ol>
</div>
{include file='component/footer.php'}
</body>
</html>
```

## 十六、 命令模式（Command Mode）

框架支持命令模式。在项目根目录执行下述命令可以查看所有命令：

```php
php console list
```

如果你需要拓展命令，可以在app\Console目录下编写命令，并在配置文件`console.php`中配置命令名称、对象和方法。

拓展命令的代码示例：

```php
namespace App\Console\Command;

class Example
{
    public function test()
    {
        return 'Just for fun';
    }

}
```

## 十七、 常驻内存模式（PHP-CLI Mode）

### 17.1 简介

常驻内存模式是以PHP-CLI方式执行Web框架。

该模式相对与PHP传统的Web服务运行方式，做到了进程复用不销毁，避免了代码重复加载、重复编译导致的消耗，所以框架性能得到大幅度提升。

框架基于WorkerMan提供的socket服务，实现了通过命令模式运行的、常驻内存的Web服务框架。传统Web方式与常驻内存模式可以同时运行。

### 17.2 使用

安装WorkerMan包：

```bash
composer require workerman/workerman
```

你可以在配置文件`workerman.php`中，配置服务端口、工作进程数：

```php
/*
 | http in socket
 */

 'http' => [
     'port' => 2348,
     'worker' => 2
 ],
```

由于常驻内存模式不依赖nginx、apache等服务器程序，框架本身可作为服务器，port端口可以直接占用80端口。

如果你希望本框架配合nginx、apache等使用，也可以使用其它端口运行本框架、然后配置服务器程序的端口代理。

nginx转发配置：

```nginx
location / {
    root /usr/share/nginx/shy/pulic;

    if (!-e $request_filename) {
        proxy_pass http://127.0.0.1:2348;
    }
}
```

在项目根目录执行下面的命令即可管理服务：

```bash
/**
 * 启动
 */
php console http start

/**
 * 后台运行模式启动
 */
php console http start -d

/**
 * 关闭|重启|平滑重启|查看状态|查看链接
 */
php console http stop|restart|reload|status|connections

```

### 17.3 开发者注意事项

PHP-CLI + Socket运行环境，相对于传统Web运行环境有根本差异，所以有很多需要注意的地方。

**最需要注意的是，实例循环复用积累的运行状态。比如：某实例带着 上一个请求的状态 执行新的请求。在实例复用之前必须将实例恢复初始状态，否则难以保证不会出现复用混乱的情况**。

**框架各部分已经做好了初始化处理，但是框架无法顾及到开发者实现的业务逻辑部分**。

开发者注意事项：

1. 业务逻辑实例如果想要复用，必须做好初始化，否则会出现混乱。如果不需要复用，在常驻内存模式下运行请销毁实例。
2. 需要改变的值不能用常量，否则复用的时候无法重新赋值。
3. header函数在Socket环境下不能正常使用，可以使用WorkerMan的http对象的方法。echo、var_dump或者页面等可以正常输出，因为框架做了ob缓冲区、会自动把缓冲区内容放进WorkerMan的通道里面输出。

### 17.4 容器实例智能调度（未完成）

目前只是简单的复用框架实例，计划实现"基于使用历史统计的容器实例智能调度系统"。

系统会自动判断实例是否可复用、并且基于统计数据动态判断是否自动销毁实例或者预先载入实例。本功能可以简化开发者的操作，同时可以平衡内存与时间占用、提升运行效率。

## 十八、 Socket模式（Socket Mode）

框架封装了基于WorkerMan的Socket服务。你可以在配置文件`workerman.php`中配置服务端口、工作进程数。支持配置多组服务，并且支持同时运行多组服务。

启动对应服务：

```bash
/**
 * 启动
 */
php console workerman chat start
```

上述命令中，chat是服务名。更多操作请查看常驻内存模式章节。

## 十九、 单元测试（Unit Test）

框架可使用phpunit做单元测试。在tests文件夹中编写测试代码，在框架根目录执行下面的命令即可执行测试。

```bash
phpunit tests/containerTest
```

## 二十、 常量（Constants）

框架提供了一些常量可供使用：

1. BASE_PATH：项目根目录
2. APP_PATH：app开发目录
3. VIEW_PATH：模版目录
4. CACHE_PATH：缓存文件目录
5. BASE_URL：项目URL

## 二十一、 杂项函数

1. is_cli：是否处于CLI模式下
2. dd：调试输出
3. is\_valid\_ip：IP是否合法

## 二十二、 特别鸣谢（排名不分先后）

1. @JamFun
2. @WeakChickenPeng
3. @CrazyNing98

感谢以上GitHub用户为框架提供的支持、帮助，意见、建议。有你们的陪伴，Shy框架能走的更远！
