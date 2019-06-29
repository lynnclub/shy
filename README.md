# 害羞框架 Shy Framework

她是一个娇小，却能顶半边天的框架；她灵动的眼眸中，透露出包罗万象的智慧；她向你敞开了怀抱，希望彼此在快乐的氛围中共育一片天地。她会是你梦寐以求的框架吗？快来了解一下她吧！

**本框架目前是初始版本，在不断的优化、完善之中，而且后续更新可能涉及大范围改动，目前仅供学习参考。**

[View English Documents](https://github.com/lynncho/shy/blob/master/README-EN.md)

Shy框架交流一群：963908345

加群暗号：lynncho

## 1. 概要

### 1.1 简介

本框架以容器为核心，就连框架本身都在容器里面，由此为开发者提供了一个自由度极高的实例复用池。在容器的基础上，框架提供了全局异常处理、门面、流水线等实用工具。并且，基于这些工具，提供了web服务、终端服务、socket服务等。

本框架除了支持传统的php web服务运行方式，还支持**命令行运行（CLI）模式**。

**在CLI模式下，由于避免了重复编译导致的消耗，本web框架的性能相对于传统方式大幅度提升。**

### 1.2 特性列表

* 容器与配置
* 异常、错误处理
* 组件式设计、适配器模式（暂未实现）
* 流水线（pipeline）
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
* 常驻内存模式
* socket
* api（暂未实现）
* 常量

### 1.3 用法

##### 第一步：克隆项目

```bash
git clone https://github.com/lynncho/shy.git
```

##### 第二步：安装依赖包

```bash
composer install
```

恭喜，接下来只需要配置web站点、或者执行CLI模式就可以运行框架了。

##### 第三步：CLI执行(可选)

```bash
php console http start
```

### 1.4 目录结构

```
shy 框架根目录
|
|   phpunit.php 单元测试入口
|   console 命令模式入口
|   server.php http服务调试入口
|
|___app 开发目录
|    |
|    |___console 终端服务开发目录
|    |___http http服务开发目录
|    |___socket socket服务开发目录
|
|___cache 缓存目录
|    |
|    |___app 系统缓存目录
|    |
|    |___log 日志目录
|    |
|    |___smarty smarty模版缓存目录
|
|___config 配置目录
|
|___public http服务开放目录
|   |
|   |   index.php http服务入口
|   |
|   |___vendor 前端第三方资源包目录
|
|___shy 框架目录
|   |
|   |   api.php api服务
|   |   console.php 终端服务
|   |   http.php http服务
|   |   webInWorkerMan.php 常驻内存http服务
|   |
|   |___console 终端服务目录
|   |___core 核心服务目录
|   |___http http服务目录
|   |___socket socket服务目录
|
|
|___tests 单元测试目录
|
|
|___vendor composer依赖包安装目录
```

### 1.5 术语

* 实例："类"或"对象"在实例化之后叫做一个"实例"。 "类"是静态的，不占进程内存，而"实例"拥有动态进程内存。
* 抽象名称：容器内实例的唯一标识字符。
* 链式调用：对象的方法返回对象，以便继续调用返回对象的方法。
* 回调：被传入其它函数或方法中执行的函数或方法。

## 2. 容器与配置

容器本身是一个实例概念，该实例可以集中生成、储存、管理其它实例，故名容器。本框架拓展了容器的概念，在容器中额外提供了配置或者数据管理的功能。

所有贯穿整个生命周期，或者需要复用的实例、配置，都应该加入到容器里面。**在本框架中，包括容器、核心实例、核心配置，都是开放给开发者访问的。如果需要操作核心，需要开发者对框架有足够的了解，否则不建议操作**。

容器类提供了绑定、加入、取用、替换或清除实例的方法，并且提供了如下便捷的封装函数。

##### 2.1 绑定实例

###### 2.1.1 用法模式

1. bind(类名)
2. bind(抽象名称, 实例或回调)

###### 2.1.2 代码示例

```php
/**
 * 绑定类名
 */
bind(shy\web::class);

/**
 * 直接绑定实例
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

1. shy(类名, 任意个其它参数)
2. shy(抽象名称, 实例或回调, 任意个其它参数)
3. shy(抽象名称, 任意个其它参数、即0到N个)
4. shy(抽象名称, 命名空间类名, 任意个其它参数)

###### 2.2.2 代码示例

```php

/**
 * 命名空间作为抽象名称，无参实例化命名空间类
 */
shy('shy\web');

/**
 * 命名空间作为抽象名称，无参实例化命名空间类
 */
shy(shy\web::class);

/**
 * 命名空间作为抽象名称，带参实例化命名空间类
 */
shy(shy\web::class, $param1, $param2);

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

**shy函数是本框架的核心函数之一，代表对容器的操作**。

该函数运行时，如果容器内已经存在待加入的抽象名称，则不会做加入操作，而是直接返回该抽象名称对应的旧实例、即只有取用功能。如果不存在该抽象名称，才会将抽象名称及其对应的实例加入到容器中，并返回被加入的实例。

该函数会尝试上述2.2.1的用法模式来获取实例。比如，从绑定的实例中获取、执行绑定的回调获取实例、通过反射实例化类。实例加入容器之后，会清除绑定以免占用内存。

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

如果抽象名称对应的实例已经存在，需要替换容器中的实例，不应该使用shy函数，应该用make_new函数。这两个函数的用法是一样的，所以这里没有给出完整的代码示例。

##### 2.4 清除实例

```php
/**
 * 清除抽象名称为web的绑定和实例
 */
shy_clear('web');

/**
 * 批量清除抽象名称的绑定和实例
 */
shy_clear(['web','test']);

```

##### 2.5 配置操作

容器的配置，包含配置文件中的配置、框架运行时配置。在CLI模式下，开发者也可以将其当成缓存服务用。

```php
//设置配置
config_set(string $abstract, $config);

//配置是否存在
config_exist(string $abstract);

//删除配置
config_del(string $abstract);

//获取配置
config(string $abstract = 'app', $default = '');

//根据key获取数组配置
config_key(string $key, string $abstract = 'app');

//获取全部配置
config_all();

//数值加减
config_int_calc(string $abstract, int $int = 1);

//数组配置push
config_array_push(string $abstract, $config);
```

**综上所述，本框架为开发者提供了非常自由的开放容器，包括框架核心都在容器里面，可以很方便地操作。这提升了开发自由度，但是，开发者在操作框架核心的时候，一定要仔细梳理逻辑，以免产生问题。**

## 3. 异常、错误处理

**本框架在终端服务、http服务等入口处注册了异常或错误处理，能够捕获到全局的异常或错误。Exception、Error、ErrorException、甚至是shutdown，都会被捕捉到。捕获到的所有错误，都会被转成预设的异常。**

根据服务的不同，需要不同的处理方法，框架为每个服务提供了handler。你也可以实现handler接口来自定义错误处理，再通过修改服务入口的handler使其生效。

## 4. 组件化设计、适配器模式（暂未实现）

本框架计划将容器之外的、所有能以最小粒度独立出来的部分，分离成可被替代的组件，并且作为composer包发布。为了能够将组件组合成一个整体，需要制定一套标准，开发者可按照该标准随意替换框架本身的组件、或者通过编写适配器将遵循其它标准的组件放在本框架使用。

## 5. 流水线（pipeline）

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
shy(pipeline::class)
    ->send(shy(request::class))
    ->through(router::class)
    ->then(function ($response) {
        if (!empty($response)) {
            shy(response::class)->send($response);
        }

        $this->end();
    });

```

**开发者使用流水线自定义调度流程时，应当仔细梳理运行流程。**

## 6. 中间件

中间件是流水线then方法传入"运行控制器的回调"时的特例，传入的第一个参数`$next`即是用于运行控制器方法的回调函数。

```php

namespace app\http\middleware;

use shy\core\middleware;
use Closure;
use shy\http\facade\request;

class example implements middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = request::all();

        // run controller
        $response = $next();

        // response handle
        $response = 'request: ' . json_encode($request) . ', example middleware, response: ' . json_encode($response);
        return $response;
    }
}

```

## 7. 路由

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

1. path配置了路径对应的控制器及其方法；
2. middleware是路径使用的中间件，可以配置多个中间件；
3. prefix是路径的前缀，可以将相同前缀的路径配置在一起；
4. middleware和prefix必须配置在group中。

## 8. 控制器

控制器中的方法应该返回数据、以便交由框架输出，开发者不应该直接在控制器内输出。在控制器内使用实例，需要注意该用单例还是新建实例，取容器内的实例、包括门面都是单例。

## 9. 门面

门面提供了便捷的静态调用方法，通过魔术方法`__callStatic()`调用**被代理类**中的方法。

实现**门面代理类**需要继承框架的**门面类**，代码示例如下：

```php
namespace app\http\facade;

use shy\core\facade;
use app\http\business\testBusiness as realTestBusiness;

class testBusiness extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realTestBusiness::class);
    }
}

```

**门面类**的`getInstance()`方法，可见它没有被子类重写时会导致抛出异常：

```php
/**
 * Get Instance.
 *
 * @return mixed
 * @throws RuntimeException
 */
protected static function getInstance()
{
    throw new RuntimeException('Facade does not implement getInstance method.');
}

```

由此可见，**门面代理类**的`getInstance()`方法重写了**门面类**中的该方法，将**被代理类**的实例传给了**门面类**，由此实现了门面代理功能。参考**2.2加入、取用实例**章节，以便创建、获取正确的实例。

## 10. 数据库

##### 10.1 SQL数据库

本框架兼容laravel的db包，你可以通过下面的命令安装并使用：

```bash
composer require illuminate/database
```

然后在配置文件`database.php`中配置数据库。最后在配置文件`app.php`中，配置illuminate_database启动即可使用。

使用方式如下：

```php
use shy\http\facade\capsule;

capsule::table('users')->where('id', 2)->get();
```

Illuminate Database的更多用法，可以查看[该项目的文档](https://github.com/illuminate/database)

##### 10.2 Redis数据库

对于redis数据库，本框架暂时只提供了PhpRedis的封装类，需要安装PhpRedis拓展，并通过下面的方式使用：

```php
/**
 * 使用redis的门面类
 */
use shy\core\facade\redis;

$redis = redis::instance('redis1');

```

## 11. 模版

框架自带模版没有采用字符解析这种复杂的设计，因为这种方式不仅实现复杂、还制定了一套模版规则需要用户学习。本框架的模版需要使用原生PHP语法开发，并且只提供了必须的一小部分函数给开发者使用，学习、调试成本较低。但是，要求开发者做好`isset()`、`empty()`、`is_array()`等预防报错处理。

此外，为了满足开发者的需求，框架支持了Smarty模版系统。

##### 11.1 自带模版类方法

1. view：设置模版文件
2. layout：设置布局页文件
3. with：向模版传入参数
4. render：渲染模版，框架会自动执行渲染

##### 11.2 自带模版函数

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
    //return view('home', compact('title', 'info'), 'main');等价方法
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

布局页的子页：

```php
<div id="hello-world">
    <?php param('info') ?>
</div>
<div id="system">
    <p>Memory Peak: <?php echo memory_get_peak_usage() / 1024; ?> kb</p>
    <p>Used Time: <?php echo microtime(true) - (IS_CLI ? $GLOBALS['_SHY_START'] : SHY_START); ?> second</p>
    <br>
    <p>Loaded instance's abstract: </p>
    <ol>
        <?php foreach (shy_list_memory_used() as $abstract => $memoryUsed) { ?>
            <li><?php echo $abstract . '  ' . $memoryUsed . ' kb'; ?></li>
        <?php } ?>
    </ol>
</div>

```

##### 11.3 Smarty模版

本框架提供了对smarty模版的支持，需要通过composer安装smarty。

```bash
composer require smarty/smarty
```

###### 11.3.1 在控制器中调用smarty模版代码示例

```php
return smarty('smarty.tpl', $params);
```

###### 11.3.2 smarty模版代码示例

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
    {if IS_CLI}
        <p>Used Time: {microtime(true) - $GLOBALS['_SHY_START']} second</p>
    {else}
        <p>Used Time: {microtime(true) - SHY_START} second</p>
    {/if}
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

## 12. 单元测试

本框架可使用phpunit做单元测试。在tests文件夹中编写测试代码，在框架根目录执行下面的命令即可执行测试。

```bash
phpunit tests/containerTest
```

## 13. 命令模式

本框架支持命令模式。在项目根目录执行下述命令可以查看所有命令：

```php
php console list
```

如果你需要拓展命令，可以在app\console目录下编写命令代码，并在配置文件`console.php`中配置命令名称、对象和方法。

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

## 14. 常驻内存模式

##### 14.1 简介

常驻内存模式是以CLI方式执行web框架。该模式相对与PHP传统的web服务执行方式，做到了进程复用不销毁，避免了代码重复编译导致的消耗，所以框架性能得到大幅度提升。

本框架基于WorkerMan提供的socket服务，实现了兼容命令模式运行的、常驻内存的web服务框架。即传统web模式与常驻内存模式可以同时运行。

##### 14.2 使用

安装WorkerMan包：

```bash
composer require workerman/workerman
```

你可以在配置文件`app.php`中，配置服务端口、工作进程数：

```php
/*
| WorkerMan Http
*/

'worker_man_http' => [
    'port' => 2348,
    'worker' => 4//填写服务器CPU核心数1-2倍的值最佳
],
```

由于常驻内存模式不依赖nginx、apache等服务器程序，框架本身可作为服务器，port端口可以占用80端口。如果你希望本框架配合nginx、apache等使用，也可以使用其它端口运行本框架、然后配置服务器程序的端口代理。

在项目根目录执行下面的命令即可启动服务：

```bash
/**
 * 调试模式启动
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

##### 14.3 开发者注意事项

cli + socket运行环境，相对于传统web运行环境有根本差异，所以有很多需要注意的地方。**其中，最需要注意的就是实例循环复用累积出的运行状态，比如：实例带着 上一个请求的状态 执行新的请求。在实例复用之前必须恢复初始状态，否则难以保证不会出现这种混乱的情况**。框架本身已经做好了初始化处理，但是框架无法顾及到开发者实现的、业务逻辑的部分。

开发者注意事项：

1. 业务逻辑实例如果想要复用，必须做好初始化，否则会出现混乱。如果不需要复用，在常驻内存模式下运行请销毁实例。
2. 需要改变的值不能用常量，否则复用的时候无法重新赋值。
3. header函数在socket环境下不能正常使用，可以使用WorkerMan的http对象的方法。echo、var_dump或者页面等可以正常输出，因为框架做了ob缓冲区、会自动把缓冲区内容放在WorkerMan的通道里面输出。

##### 14.4 智能调度实例（暂未实现）

目前只是简单的复用框架实例，计划实现"基于使用时间频率统计的实例智能调度系统"，自动判断实例是否可复用、并且动态处理是否自动销毁实例以及预先载入实例。本功能可以简化开发者的操作，同时可以节省内存、提升运行效率。

## 15. socket

本框架还封装了基于workerman的socket服务，你可以在配置文件`app.php`配置`worker_man_socket`，支持配置多组服务。

启动对应服务：

```base
/**
 * 调试模式启动
 */
php console workerman chat start
```

上述命令中，chat是服务组名。更多内容请参照常驻内存模式的14.2章节。

## 16. 常量

框架提供了一些常量可供使用：

1. BASE_PATH：项目根目录
2. APP_PATH：app开发目录
3. CACHE_PATH：缓存目录
4. PUBLIC_PATH：web开放目录
5. BASE_URL：项目URL

## 17. 杂项函数

1. logger：记录日志
2. dd：调试输出

## 18. 特别鸣谢（排名不分先后）

1. @JamFun
2. @WeakChickenPeng
3. @CrazyNing98

感谢以上GitHub用户为本框架提供的支持、帮助，意见、建议。有你们的陪伴，Shy框架能走的更远！
