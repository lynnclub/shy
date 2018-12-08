# Shy Framework

She is a petite, but can hold up half the sky; she supports many convenient features and usages while maintaining a dexterous figure. Powerful doesn't necessarily require complex implementations, come check out her!

## 1. Summary

### 1.1. Features

* Container
* Exception, error handling
* Pipeline
* Middleware
* Request
* Response
* Router
* Controller
* Facade
* Database
* View
* Unit test
* Command mode
* Api (Not yet implemented)
* Socket (Not yet implemented)

### 1.2. Usage

##### First Step
```bash
git clone https://github.com/lynncho/shy.git
```

##### Second Step
```bash
composer install
```

### 1.3 Directory Structure

```
shy  Framework root directory
|
|   phpunit.php  Unit test entry
|   console  Command mode entry
|   server.php  Web service local debugging entry
|
|___app  Development directory
|    |
|    |___console  Terminal program development directory
|    |___http  Web application development directory
|
|___cache
|    |
|    |___app  System cache directory
|    |___log  Log cache directory
|
|___config
|
|___public  Web service opening directory
|   |
|   |___vendor  Front-end third-party resource bundle directory
|
|___shy  Framework directory
|   |
|   |   api.php  Api services
|   |   console.php  Terminal services
|   |   web.php  Web services
|   |
|   |___console  Terminal services directory
|   |___core  Core services directory
|   |___http  Web services directory
|
|
|___tests  Unit test directory
```

### 1.4 The Term

* Instance: "Class" is called an "instance" after instantiation. "Class" is static, does not occupy process memory, and "instance" has dynamic memory.
* Abstract Name: A unique name for an instance within a container.
* Chained call: The object's method returns the object in order to continue calling the next method.
* Callback: A function or method that is passed into another function or method.

### 2. Container

The container itself is an instance concept, this instance can store other instances centrally, hence the name container. Containers are the foundation of this framework, and all instances that span the entire lifecycle or need to be reused should be added to the container, including instances of the framework core class. Container classes provide a convenient way to bind, join, fetch, replace, or clean up instances, and provide the appropriate wrapper functions. The following mainly talk about these wrapper functions.

##### 2.1 Binding Instance

###### 2.1.1 Usage Mode

bind(Abstract name, Instance or callback)

###### 2.1.2 Code Example

```php
/**
 * Binding instance
 */
bind('web', new shy\web());

/**
 * Binding callback (Can be used to delay the transfer of parameters, that is, when the container is added. Support any number of parameters, ie 0 to N parameters)
 */
bind('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
});

/**
 * Chained call: bind the instance, join the container and take the instance, execute the instance's run method
 */
bind('web', new shy\web())->shy('web')->run();

/**
 * Chained call: bind callback, execute the callback with parameters, join the container and then return the instance, execute the run method of the instance
 */
bind('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
})->shy('web', $param1, $param2)->run();

```

As mentioned above, the bind function is a wrapper around the container and returns a container instance for chained calls. Its function is only to bind the instance or callback corresponding to the abstract name, and does not add the instance to the container in the true sense, so it can not get the bound instance from the container. Adding an instance to a container and taking an instance from a container is a function of the shy function.

##### 2.2 Join and Fetch Instance

###### 2.2.1 Usage Mode

1. shy(Abstract name, Any other parameter 0 to N)
2. shy(Abstract name, Instance or Callback, Any other parameter)
3. shy(Namespace class name, Any other parameter)
4. shy(Abstract name, Namespace class name, Any other parameter)

###### 2.2.2 Code Example

```php
/**
 * Run the binding callback
 */
shy('web');

/**
 * Run the binding callback with parameters
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
/**
 * 如果名称被占用，替换成新实例
 * 
 * 返回 web实例
 */
make_new('web', new shy\web());

/**
 * 如果名称被占用，替换成新实例
 * 
 * 返回 web实例
 */
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
2. through：设置传入对象；
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

中间件是流水线then方法传入"执行控制器回调"时的特例，传入的第一个参数`$next`即是用于执行控制器方法的。

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

路由支持"配置文件模式"和"路径模式"，可以在配置文件`app.php`中关闭、启用。配置文件模式根据请求路径查找配置文件，得到控制器及其方法，支持中间件、路径前缀。路径模式是直接把请求路径当成控制器及其方法，属于传统方法。两种模式同时启用时，配置文件模式优先。

配置文件示例：

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

框架自带模版没有采用字符解析这种复杂的设计，因为这种方式不仅实现复杂、还制定了一套模版规则需要用户学习。本框架的模版需要使用原生PHP语法开发，并且只提供了必须的一小部分函数给开发者使用，学习、调试成本较低。但是，要求开发做好`isset()`、`empty()`、`is_array()`等预防报错处理。

为了满足开发者的需求，框架计划未来支持第三方模版系统。

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

需要拓展命令可以在配置文件`console.php`中配置命令名称、对象和方法。

新建命令代码示例：
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