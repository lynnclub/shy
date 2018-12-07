# Shy


## 1. 概要

### 1.1 简介

她是一个娇小、却能顶半边天的框架。

### 1.2 特性列表

1. 容器
2. 流水线
3. 门面
4. 路由
5. 控制器
6. 中间件
7. 请求
8. 响应
9. 数据库
10. 模版
11. 异常、错误处理
12. 命令模式

### 1.3 用法

##### 第一步

```bash
git clone https://github.com/lynncho/shy.git
```

##### 第二步

```bash
composer install
```

### 2. 容器

本框架几乎所有实例都会加入到容器里面，包括框架核心类的实例。你可以对容器内的实例随意绑定、加入、取用、替换或清除。

##### 2.1 绑定实例

###### 2.1.1 用法模式

bind(抽象名称, 实例或闭包)

###### 2.1.2 代码示例

```php
/**
 * 绑定实例
 * 
 * 返回 容器实例
 */
bind('web', new shy\web());

/**
 * 绑定实例、加入到容器、执行实例的方法（链式调用）
 */
bind('web', new shy\web())->shy('web')->run();

/**
 * 绑定闭包（可用于加入容器时延迟传参，支持任意个参数）
 * 
 * 返回 容器实例
 */
bind('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
});

```

bind函数是对容器的封装，会返回容器实例以便链式调用。它的功能只是绑定实例或闭包，并没有将实例加入到容器，所以也无法从容器中获取绑定的实例。

##### 2.2 加入、取用实例

###### 2.2.1 用法模式

1. shy(抽象名称, 任意个其它参数、即0到N个)
2. shy(抽象名称, 实例或闭包, 任意个其它参数)
3. shy(命名空间类名, 任意个其它参数)
4. shy(抽象名称, 命名空间类名, 任意个其它参数)

###### 2.2.2 代码示例

```php
/**
 * 运行绑定的闭包
 * 
 * 返回 web实例
 */
shy('web');

/**
 * 带参运行绑定的闭包
 * 
 * 返回 web实例
 */
shy('web', $parma1, $param2);

/**
 * 直接将实例加入容器
 * 
 * 返回 web实例
 */
shy('web', new shy\web());

/**
 * 直接带参运行闭包
 * 
 * 返回 web实例
 */
shy('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
}, $param1, $param2);

/**
 * 命名空间作为抽象名称，带参实例化命名空间类
 *
 * 返回 web实例
 */
shy('shy\web', $param1, $param2);

/**
 * 设置抽象名称并实例化带命名空间的类
 * 
 * 返回 web实例
 */
shy('web','shy\web');

```

shy函数是框架的核心函数之一。如果待加入的抽象名称不存在于容器内，该函数可以将实例加入到容器中，并返回被加入的实例。如果已经存在，则返回该抽象名称对应的旧实例。

该函数将实例加入到容器之前，会执行绑定的闭包实例化对象。如果没有绑定实例或者闭包，该函数会根据上述2.2.1的用法模式、尝试各种可能实例化对象。实例加入容器之后，会清除绑定以免占用内存。

对于如何知道抽象名称是否已存在？你可以通过`shy_list()`函数获取容器内的所有抽象名称，或者使用`in_shy_list()`函数判断是不是已存在。

##### 2.3 替换实例

###### 2.3.1 用法模式

1. makeNew(抽象名称, 任意个其它参数、即0到N个)
2. makeNew(抽象名称, 实例或闭包, 任意个其它参数)
3. makeNew(命名空间类名, 任意个其它参数)
4. makeNew(抽象名称, 命名空间类名, 任意个其它参数)

```php
/**
 * 如果名称被占用，替换成新实例
 * 
 * 返回 web实例
 */
makeNew('web', new shy\web());

/**
 * 如果名称被占用，替换成新实例
 * 
 * 返回 web实例
 */
makeNew('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
});

```

如果需要替换容器中的实例，不应该使用shy函数，应该用makeNew函数。这两个函数的用法是一样的，所以这里没有给出完整的代码示例。

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


**由上述内容可见，本框架为开发者提供了开放容器、并且包括框架核心在内的几乎所有实例都在容器里面。这提升了开发自由度，但是，开发者在操作框架核心类的时候，一定要仔细梳理运行流程，以免产生问题。**

### 3. 门面

门面提供了便捷的静态调用方法，通过魔术方法`__callStatic()`调用**被代理类**中的方法。

实现**门面代理类**需要继承框架的**门面类**，代码示例如下：

```php
namespace shy\http\facade;

use shy\core\facade;

class request extends facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'request';
    }
}

```

**门面类**通过shy函数获取**被代理类**的实例：

```php
/**
 * Get facade instance in container
 *
 * @return object
 */
 public static function getInstance()
 {
     return shy(static::getFacadeAccessor());
 }

```

由此可见，**门面代理类**的`getFacadeAccessor()`方法需要返回**被代理类**在容器中的抽象名称。这样**门面类**就可以通过这个抽象名称找到需要被代理的实例。

如果被代理的实例没有加入到容器中，那就需要保证，`shy($abstract)`函数只通过一个"抽象名称"参数，可以将实例加入到容器。可以参考**2.2加入、取出实例**章节。推荐使用命名空间加入容器内不存在的实例。

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

路由支持配置文件模式和路径模式，可以在`app.php`中关闭、启用。配置文件模式根据请求路径查找配置文件，得到控制器及其方法，支持中间件、路径前缀。路径模式直接把请求路径解析出控制器及其方法，属于传统方法。

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

控制器的方法应该返回数据、交由框架输出，不应该直接在控制器内输出。

在控制器内使用实例需要注意该用单例还是新实例，容器内的实例、包括门面都是单例。

### 8. 数据库

本框架暂时只提供了pdo、redis和mysqli的封装类。

### 9. 模版

框架自带模版没有采用字符解析这种复杂的设计，因为这种方式不仅实现复杂、还制定了一套模版规则需要用户学习。

本框架的模版需要使用原生PHP语法开发，并且只提供了必须的一小部分函数给开发者使用，学习、调试成本较低。

但是，为了提升开发者体验，框架计划支持第三方模版系统。

##### 9.1 模版类

1. view方法设置模版文件
2. layout方法设置布局页文件
3. with方法向模版传入参数
4. render方法渲染模版，框架会自动执行渲染

##### 9.2 模版函数

1. view：模版类的封装。用于便捷地在控制器中使用模版，可传参、也可链式调用。每次使用都会在容器中新建模版类。
2. include_sub_view：在布局页中输出模版。
3. include_view：在布局页、模版中引入组件模版。
4. param：在模版中输出变量或常量，不使用该函数输出报错无法被框架正常处理。

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