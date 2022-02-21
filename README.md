# Shy Framework

### 简洁却强大的高性能框架

框架实现简洁、功能强大、覆盖全面，正如她的名字：**Shy——纤细身形、不漏内秀**。

## 一、概述

框架内置了丰富的组件（Component），例如配置、日志、门面、缓存、流水线、进程管理等等。各个组件按照拼积木的方式灵活组合，形成了面向不同使用场景的 Web框架、CLI框架、Socket框架、API框架，并且可以多入口独立运行。

所有内置组件都抽象出了契约（Contract），拥有规范约束、可以自由替换。容器（Container）作为其中最核心的组件，提供了操作便捷的实例（Instance）复用池。需要复用的实例，包括内置组件的实例，都可以在容器中获取、销毁或替换。

此外，**框架提供了一种革新 PHP传统Web框架 的运行模式——常驻内存模式（PHP-CLI）。** 在该模式下，由于避免了代码重复编译导致的性能开销，框架的Web服务性能得以大幅提升！！！

### 1.1 框架兼容

框架全力遵守 [PHP-FIG组织](https://www.php-fig.org/) 制定的PSR（PHP Standards Recommendations 系列规范，与遵守相同规范的框架互相兼容。

1. 基础编码：遵守 [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/) ；
2. 日志：遵守 [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)；
3. 自动加载：遵守 [PSR-4: Autoloader](https://www.php-fig.org/psr/psr-4/)；
4. 容器：遵守 [PSR-11: Container interface](https://www.php-fig.org/psr/psr-11/) ;
5. 缓存：遵守 [PSR-16: Common Interface for Caching Libraries](https://www.php-fig.org/psr/psr-16/)。

### 1.2 特性列表

* 启动器（Bootstrap）
* 内置常量（Constant）
* 契约（Contract）
* 容器与依赖注入（Container and Dependency Injection）
* 配置与环境（Config、SHY_ENV）
* 异常与错误的捕获处理（Exception Handler）
* 日志（Logger）
* 流水线（Pipeline）
* 进程管理（Process）
* 钩子（Hook）
* 门面（Facade）
* 缓存（Cache）
* 数据库（DataBase）
* 请求（Request）
* 中间件（Middleware）
* 控制器（Controller）
* 响应（Response）
* 会话（Session）
* 路由（Router）
* 模版（View）
* 命令模式（Command Mode）
* 常驻内存模式（PHP-CLI Http Mode）
* Socket模式（Socket Mode）
* 单元测试（Unit Test）

### 1.3 目录结构

```
shy 框架根目录
|
|   phpunit.php 单元测试入口
|   command 命令模式入口
|   server.php Http服务调试入口（仅供调试使用，不建议将整个项目目录暴露在web服务器下）
|   gulpfile.js Gulp前端构建服务
|
|___app 开发目录
|    |
|    |___Command 命令服务开发目录
|    |___Function 函数开发目录
|    |___Http Http服务开发目录
|    |___Socket Socket服务开发目录
|
|___bootstrap 服务启动目录
|    |
|    |___command.php 命令服务启动程序
|    |___http.php Http服务启动程序
|    |___http_workerman.php 基于workerman的常驻内存Http服务启动程序
|
|___cache 缓存目录
|    |
|    |___app 系统缓存目录
|    |___log 日志目录
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
|   |___uploads 上传文件目录
|   |___vendor 前端第三方资源包目录
|
|___shy 框架目录
|   |
|   |   Command.php 命令服务
|   |   HttpInWorkerMan.php 基于workerman的常驻内存Http服务
|   |   SocketInWorkerMan.php 基于workerman的Socket服务
|   |
|   |___Command 命令模式目录
|   |___Core 核心服务目录
|   |___Http Http服务目录
|   |___Library 类库目录
|   |___Socket Socket服务目录
|
|___tests 单元测试目录
|
|___vendor composer依赖包安装目录
```

### 1.4 运行框架

#### 第一步：克隆或下载项目

```bash
git clone https://github.com/lynncho/shy.git MyProjectName
```

默认克隆master分支，包含最新改动。建议下载稳定的 [发行版本](https://github.com/lynncho/shy/releases)。

#### 第二步：安装依赖包

```bash
#进入项目目录
cd MyProjectName

#执行composer依赖包安装
composer install
```

恭喜！！！接下来，只需要配置Web站点，或者启动常驻内存模式，就可以运行框架了。

#### 第三步（可选）：启动常驻内存模式

```bash
php command http_workerman start
```

### 1.5 Http服务的生命周期

1. 启动器运行（Bootstrap）
2. 启动自动加载（Composer）
3. 读取并设置运行环境（SHY_ENV）
4. 定义内置常量（Constant）
5. 启动容器，注册组件（Container）
6. 启动配置组件（Config）
7. 设置时区（Timezone）
8. 注册全局异常处理（ExceptionHandler）
9. 引入模版函数文件（View）
10. 引入自定义文件
11. 装载当前请求（Request）
12. 开启会话（Session）
13. 路由解析（Router）
14. 执行中间件、控制器（Router、Middleware、Controller）
15. 输出响应（Response）
16. 请求组件恢复初始化状态（Request）

### 1.6 远期规划

1. 容器实例调度
2. 单元测试覆盖率100%
3. 会话重构
4. Swoole socket 
5. Api便捷开发框架

## 二、 契约（Contract）

契约是比接口（interface）更广义的概念，意义相似，但是不局限于接口的形式。契约可以是接口、抽象类，甚至是非硬性约束的惯例。

各种组件都按照契约的规范实现，比如容器、缓存、配置、日志、门面等，部分组件的契约还兼容PSR规范。

契约在容器中的使用方式：**在容器中注册契约类名，绑定实现了该契约的对应实体类。遵守同一契约的实体类，可以在容器中替换。**

bootstrap程序中，契约类与实体类的绑定：

```php
$container->binds([
    ConfigContract::class => Config::class,
    LoggerContract::class => File::class,
    ExceptionHandlerContract::class => Handler::class,
    PipelineContract::class => Pipeline::class,
    CacheContract::class => Memory::class,
    DataBaseContract::class => Pdo::class,
    RequestContract::class => Request::class,
    ResponseContract::class => Response::class,
    SessionContract::class => Session::class,
    RouterContract::class => Router::class,
    ViewContract::class => View::class,
]);
```

## 三、 容器与依赖注入（Container and Dependency Injection）

本框架的容器类，遵守PSR（PHP Standards Recommendations）中的《PSR-11: Container interface》接口规范。并且，实现了PHP的ArrayAccess、Countable接口，可以当作数组使用。

容器是对实例集中管理的实例池，可以创建、绑定、使用、替换或者移除实例。此外，框架拓展了容器的概念，支持把字符、数组等任意内容当作实例管理。即**容器是实例与数据的集中管理池**。

注意事项：

1. 需要复用的、贯穿框架生命周期的实例或者数据，应该加入到容器；
2. **框架的核心服务及配置，可以被自由访问，建议对框架设计有足够的了解再操作。**

### 3.1 绑定实例

#### 3.1.1 用法模式

支持绑定实例或者数据，绑定内容为闭包，将在 加入与获取实例 时执行该闭包。

1. bind(类名, 类名、实例或匿名函数)：类名为键，对应的值可以是类名、实例或匿名函数
2. bind(数据名, 数据)：数据名为键，对应传入的数据
3. bind(类名)：等于bind(类名, 类名)
4. bind(数据名)：等于bind(数据名, 数据名)

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
 * 绑定匿名函数（可用于延迟传参、即执行闭包时再传参，支持任意个参数）
 */
bind(Shy\Http::class, function ($param1, $param2) {
    return new Shy\Http($param1, $param2);
});

/**
 * 链式调用：绑定实例->加入容器然后获取实例->执行实例的run方法
 * 
 * getOrMake方法等同于shy函数
 */
bind(Shy\Http::class, new Shy\Http())->getOrMake(Shy\Http::class)->run();

/**
 * 链式调用：绑定匿名函数->带参执行，加入容器然后获取实例->执行实例的run方法
 */
bind(Shy\Http::class, function ($param1, $param2) {
    return new Shy\Http($param1, $param2);
})->getOrMake(Shy\Http::class, $param1, $param2)->run();
```

如上所述，bind函数是对容器类的封装，会返回容器实例以便链式调用。**它的功能只是绑定实例，并没有将实例加入到容器的实例池**。将实例加入容器、获取容器中实例，是shy函数的功能。

### 3.2 加入容器与获取实例

#### 3.2.1 用法模式

1. shy(类名, null, 任意个实例化参数)：类名作为实体类
2. shy(类名, 实体类名, 任意个实例化参数)：类名绑定实体类
2. shy(类名, 实例或匿名函数, 任意个实例化参数)：类名绑定实例或者匿名函数

#### 3.2.2 代码示例

```php
/**
 * 实例化Http类，并加入容器
 */
shy(Shy\Http::class);

/**
 * 带参数实例化（未绑定）
 */
shy(Shy\Http::class, null, $param1, $param2);

/**
 * 先绑定，再带参数实例化
 */
bind(Shy\Http::class);
shy(Shy\Http::class, $param1, $param2);

/**
 * 带参数实例化File类，契约为Logger
 */
shy(Shy\Core\Contract\Logger::class, Shy\Core\Logger\File::class, $param1, $param2);
// 获取契约为Logger的File实例（需要已绑定或已加入容器）
shy(Shy\Core\Contract\Logger::class);

/**
 * 直接将实例加入容器
 */
shy(Shy\Http::class, new Shy\Http());

/**
 * 设置类名的别名
 */
shy()->alias('pipeline', Shy\Core\Contract\Pipeline::class);
// 使用别名获取实例
shy('pipeline');
```

**shy函数是框架的核心函数，代表对容器的操作**。

使用shy函数时，如果容器内已经存在指定类名的实例，则直接返回该实例。如果不存在，会将实例加入到容器中，然后返回被加入的实例。

该函数会自动尝试上述所有用法模式来获取实例。比如，从绑定的实例中获取、执行绑定的匿名函数获取实例、或者通过反射实例化类。

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
use Shy\Http\Contract\Request;
use Shy\Core\Contract\Config;

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

如上所述，Logger类的构造方法依赖Config和Request契约类参数。容器通过反射感知变量类型，可以自动注入契约绑定的实体类。

## 四、 配置与环境（Config、SHY_ENV）

配置类继承了内存缓存类`Shy\Core\Cache\Memory::class`，因为该缓存满足需求且无外部依赖。在`cache`开启的时候，配置会被持久化缓存。

使用方式：

```php
/**
 * 读取配置文件app.php的配置
 */
$appConfig = config('app');

/**
 * 读取配置文件app.php中的，cache配置
 */
$isCache = config('app.cache');

/**
 * 读取配置文件workerman.php中的，socket配置
 */
$socketConfig = config('workerman.socket');
```

**配置组件将优先使用环境目录下的配置文件，当环境目录下不存在该文件时，将使用通用的配置文件**。

框架预设了三种环境，develop开发环境、testing测试环境、production生产环境。如果未设置环境值，默认为develop开发环境。

Nginx fastcgi配置环境值：

```nginx
fastcgi_param SHY_ENV 'testing';
```

Linux系统配置环境值：

```bash
// .bash_profile文件
export SHY_ENV=production
```

环境配置可以自由拓展，框架按照指定的环境值，读取config目录下的同名环境目录。

## 五、 异常与错误的捕获处理（Exception Handler）

**框架在各个服务的入口处，注册了异常（Exception）与错误（Error）捕获，能够捕获并处理所有未被捕获的异常、错误、甚至是Shut Down。错误及Shut Down会被转化成异常，统一按异常处理。**

框架为每个服务提供了异常处理类（Handler）。你也可以实现Handler接口来自定义异常处理，在启动器中替换服务入口的Handler使其生效。

可以在bootstrap目录下的服务启动文件中，替换异常处理契约绑定的实体类：

```php
$container->bind(Shy\Core\Contract\ExceptionHandler::class, Shy\Http\Exception\Handler::class);
```

对于需要返回Http Code的错误，可以抛出HttpException。该错误的响应会输出`errors/common.php`模版:

```php
use Shy\Http\Exception\HttpException;

throw new HttpException(403, lang(5000));
```

## 六、 门面（Facade）

门面提供了便捷的静态调用方式。实现**门面类**需要继承框架的**门面抽象类**，并且重写父类的`getInstance()`方法，以便向父类传递**实体类**。实现代码示例如下：

```php
namespace Shy\Core\Facade;

use Shy\Core\Facade;
use Shy\Core\Contract\Cache as CacheContract;

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

**门面类**的父类——**门面抽象类**，通过魔术方法`__callStatic()`调用**实体类**中的方法。可参考 容器与依赖注入 章节，以便理解框架如何获取**实体类**的实例。

## 七、 缓存（Cache）

本框架的缓存类，遵守PSR（PHP Standards Recommendations）中的《PSR-16: Common Interface for Caching Libraries》接口规范。并且，实现了PHP的ArrayAccess接口，可以当作数组使用。

由于phpredis拓展不完全兼容PSR-16，所以框架对缓存的PSR规范无硬性约束、仅建议遵守。

框架默认使用无依赖的Memory本地内存缓存，通过文件做持久化储存，每次GC最多回收10条。在常驻内存模式下，由于该缓存只在关闭、启动服务的时候执行文件持久化，所以性能开销较小。

框架还提供了基于phpredis拓展实现的Redis缓存，推荐有条件时优先使用。可以在bootstrap目录下的服务启动文件中，替换缓存契约绑定的实体类：

```php
$container->bind(Shy\Core\Contract\Cache::class, Shy\Core\Cache\Redis::class);
```

调用缓存门面的方法：

```php
use Shy\Core\Facade\Cache;

Cache::set('test', 123);

Cache::get('test');
```

## 八、 日志（Logger）

本框架的日志类，遵守PSR（PHP Standards Recommendations）中的《PSR-3: Logger Interface》接口规范。

### 8.1 简介

框架实现了本地文件日志`Shy\Core\Logger\File`，以及阿里云日志`Shy\Core\Logger\Aliyun`（阿里云日志类继承了本地文件日志类，使用的时候也会保存本地文件日志）。

如果不需要记录日志，日志契约可以更换绑定`Psr\Log\NullLogger`类。

可以在bootstrap目录下的服务启动文件中，替换日志契约绑定的实体类：

```php
$container->bind(Shy\Core\Contract\Logger::class, Shy\Core\Logger\Aliyun::class);
```

### 8.2 错误级别

1. emergency 紧急
2. alert 警报
3. critical 严重
4. error 错误
5. warning 警告
6. notice 注意
7. info 信息
8. debug 调试

### 8.3 自定义日志

自定义日志需要实现`Shy\Core\Contract\Logger`接口，并且继承PSR的`Psr\Log\AbstractLogger`。

## 九、 流水线（Pipeline）

流水线是一种连贯的流程调度工具，**使用流水线执行的对象或函数，可以享受容器的依赖注入服务**。流水线连通了包括中间件、控制器在内的运行流程。

Pipeline类的方法讲解：

1. send：设置传入参数，参数数量没有限制；
2. through：设置流水线的处理对象，可以是一个或者多个；
3. via：设置处理对象需要执行的方法，默认执行handle方法；
4. then：流水线的执行方法，需要设置回调函数；
5. run：流水线的执行方法，无需设置回调函数。

开发者可使用流水线来执行自己的调度，代码实例如下：

```php
/**
 * 执行一组中间件，带回调执行
 */
$response = shy(Pipeline::class)
    ->send(...$this->pathParam)
    ->through($this->middleware)
    ->then(function () {
        return $this->runController();
    });

/**
 * 执行单个控制器，不带回调执行
 */
$response = shy(Pipeline::class)
    ->through($this->controller)
    ->via($this->method)
    ->run();
```

## 十、 请求（Request）

通过门面类使用：

```php
// 引入门面类
use Shy\Http\Facade\Request;

// 是否初始化（常驻内存模式使用）
Request::initialized();

// 获取全部请求
Request::all();

// 获取数据流 php://input
Request::content();

// 获取GET请求参数
Request::get('key');

// 获取POST请求参数
Request::post('key');
```

通过契约的依赖注入使用：

```php

```

## 十一、 中间件（Middleware）

中间件是请求与响应的中间步骤，是流水线（Pipeline）的一种特例。即流水线传入的第一个参数`$next`，是用于运行控制器的闭包。

### 11.1 前置中间件

中间件在控制器之前执行，称为“前置中间件”。如下，是有IP白名单功能的前置中间件：

```php
namespace Shy\Http\Middleware;

use Shy\Core\Contract\Middleware;
use Closure;
use Shy\Http\Exception\HttpException;
use Shy\Http\Facade\Request;
use Shy\Core\Facade\Logger;

class IpWhitelist implements Middleware
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
        $hit = false;

        $userIps = Request::getClientIps();

        foreach ($userIps as $userIp) {
            if (in_array($userIp, config('ip_whitelist'))) {
                $hit = true;
            }
        }

        if (!$hit) {
            Logger::info('Ip whitelist block request', Request::all());

            if (Request::ajax()) {
                return get_response_json(5000);
            } else {
                throw new HttpException(403, lang(5000));
            }
        }

        return $next();
    }

}
```

### 11.2 后置中间件

```php
use Shy\Core\Contract\Middleware;
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
        $response = json_encode($response);
        
        return $response;
    }

}
```

### 11.3 使用中间件

中间件需要在配置文件`middleware.php`中定义别名或者别名组，然后在路由中填写别名使用。

```php
return [
    'IpWhitelist' => Shy\Http\Middleware\IpWhitelist::class,
    'Throttle' => Shy\Http\Middleware\Throttle::class,
    'Example' => App\Http\Middleware\Example::class,
    'GroupExample' => [
        Shy\Http\Middleware\CSRF::class,
    ]
];
```

内置中间件：

1. IpWhitelist：IP白名单，通过配置文件`ip_whitelist.php`管理白名单；
2. Throttle：限流阀，默认1分钟内限制单IP访问60次，可在路由中自定义设置。例如1分钟内限制10次、5分钟解禁：`Throttle:10,5`。

## 十二、 响应（Response）

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

## 十三、 路由（Router）

路由通过请求（Request）获取请求路径，然后解析出对应控制器和方法，最终通过流水线调度中间件与控制器。

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
        ['prefix' => 'controller_2', 'namespace' => 'App\\Http\\Controller_2', 'path' => [
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

## 十四、 控制器（Controller）

控制器方法中应该返回（return）数据、以便交由框架响应组件输出，不应该直接在控制器内手动输出。

在控制器内使用实例，建议通过门面，或者契约的依赖注入。

## 十五、 数据库（DataBase）

可以在bootstrap目录下的服务启动文件中，替换数据库契约绑定的实体类：

```php
/**
 * 默认使用Pdo
 */
$container->bind(Shy\Core\Contract\DataBase::class, Shy\Core\DataBase\Illuminate::class);
```

### 15.1 laravel的DB包

框架兼容laravel的DB包，你可以通过下面的命令安装此包：

```bash
composer require illuminate/database 5.8
```

在配置文件`database.php`中配置数据库，然后在启动文件中替换DataBase实体类，便可使用了。

使用方式如下：

```php
use Shy/Core/Facades/DB;

DB::table('users')->where('id', 2)->get();
```

Illuminate Database的更多用法，可以查看[该项目的文档](https://github.com/illuminate/database)

## 十六、 模版（View）

框架自带模版没有采用字符解析这种复杂的设计，因为这种方式不仅实现复杂、还制定了一套模版规则需要用户学习。

本框架的模版需要使用原生PHP语法开发，并且只提供了必须少量函数，学习成本较低。

但是，要求开发者做好`isset()`、`empty()`、`is_array()`等预防报错处理。

此外，为了满足开发者的需求，框架支持了Smarty模版系统。

### 16.1 自带模版的辅助函数

1. view：模版类的封装。用于便捷地在控制器中使用模版，可传参、也可链式调用。每次使用本函数都会在容器中新建或替换模版实例。
2. include\_view：在布局页中输出模版；在布局页、模版中引入组件模版。
3. param：在模版中输出变量或常量，不使用该函数输出报错无法被框架正常处理。

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

include\_view函数用于在布局页中输出子模版，或者引入模版组件；param函数输出变量和常量：

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
    <link type="text/css" rel="stylesheet" href="<?php url() ?>css/app.css">
    <?php push_resource('header-css') ?>
</head>
<body>
<?php include_view() ?>
<?php include_view('component/footer') ?>
<?php push_resource('footer-js') ?>
</body>
</html>
```

布局页的子页：

```php
<?php

push_resource('footer-js', [url() . 'vendor/jquery/dist/jquery.js', ''], 'js');

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

### 16.2 Smarty模版

框架提供了对Smarty模版的支持，需要先安装Smarty包。

```bash
composer require smarty/smarty
```

#### 16.2.1 在控制器中调用Smarty模版实例

```php
return smarty('smarty.tpl', $params);
```

#### 16.2.2 Smarty模版实例

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

## 十七、 命令模式（Command Mode）

框架支持命令模式。在项目根目录执行下述命令可以查看所有命令：

```php
php command list
```

如果你需要拓展命令，可以在app\Command目录下编写命令，并在配置文件`command.php`中配置命令名称、对象和方法。

拓展命令的代码示例：

```php
namespace App\Command;

class Example
{
    public function test()
    {
        return 'Just for fun';
    }

}
```

## 十八、 常驻内存模式（PHP-CLI Mode）

### 18.1 简介

常驻内存模式是以PHP-CLI方式执行Web框架。

该模式相对与PHP传统的Web服务运行方式，做到了进程复用不销毁，避免了代码重复加载、重复编译导致的消耗，所以框架性能得到大幅度提升。

框架基于WorkerMan提供的socket服务，实现了通过命令模式运行的、常驻内存的Web服务框架。传统Web方式与常驻内存模式可以同时运行。

### 18.2 使用

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
php command http_workerman start

/**
 * 后台运行模式启动
 */
php command http_workerman start -d

/**
 * 关闭|重启|平滑重启|查看状态|查看链接
 */
php command http_workerman stop|restart|reload|status|connections

```

### 18.3 开发者注意事项

PHP-CLI + Socket运行环境，相对于传统Web运行环境有根本差异，所以有很多需要注意的地方。

**最需要注意的是，实例循环复用积累的运行状态。比如：某实例带着 上一个请求的状态 执行新的请求。在实例复用之前必须将实例恢复初始状态，否则难以保证不会出现复用混乱的情况**。

**框架各部分已经做好了初始化处理，但是框架无法顾及到开发者实现的业务逻辑部分**。

开发者注意事项：

1. 业务逻辑实例如果想要复用，必须做好初始化，否则会出现混乱。如果不需要复用，在常驻内存模式下运行请销毁实例。
2. 需要改变的值不能用常量，否则复用的时候无法重新赋值。
3. header函数在Socket环境下不能正常使用，可以使用WorkerMan的http对象的方法。echo、var_dump或者页面等可以正常输出，因为框架做了ob缓冲区、会自动把缓冲区内容放进WorkerMan的通道里面输出。

### 18.4 容器实例智能调度（未完成）

目前只是简单的复用框架实例，计划实现"基于使用历史统计的容器实例智能调度系统"。

系统会自动判断实例是否可复用、并且基于统计数据动态判断是否自动销毁实例或者预先载入实例。本功能可以简化开发者的操作，同时可以平衡内存与时间占用、提升运行效率。

## 十九、 Socket模式（Socket Mode）

框架封装了基于WorkerMan的Socket服务。你可以在配置文件`workerman.php`中配置服务端口、工作进程数。支持配置多组服务，并且支持同时运行多组服务。

启动对应服务：

```bash
/**
 * 启动
 */
php command workerman chat start
```

上述命令中，chat是服务名。更多操作请查看常驻内存模式章节。

## 二十、 单元测试（Unit Test）

框架可使用phpunit做单元测试。在tests文件夹中编写测试代码，在框架根目录执行下面的命令即可执行测试。

由于框架支持php7.0及以上版本，适配phpunit版本为phpunit 6.x。

安装phpunit 6.5：

```bash
wget https://phar.phpunit.de/phpunit-6.5.phar

php phpunit-6.5.phar --version

chmod +x phpunit-6.5.phar

mv phpunit-6.5.phar /usr/local/bin/phpunit
```

执行单元测试：

```bash
phpunit tests/containerTest
```

## 二十一、 常量（Constants）

框架提供了一些常量可供使用：

1. SHY_ENV：运行环境，develop、testing、production。
2. BASE_PATH：项目根目录
3. APP_PATH：app开发目录
4. CACHE_PATH：缓存文件目录
5. VIEW_PATH：模版目录
6. PUBLIC_PATH：Http服务开放目录

## 二十二、 杂项函数

1. is_cli：是否处于CLI模式下
2. dd：调试输出
3. is\_valid\_ip：IP是否合法
4. random_code：生成随机码
5. mime：文件格式与MIME类型
