# Shy Framework

She is a petite, but can hold up half the sky; she supports many convenient features and usages while maintaining a dexterous figure. Powerful but no complex implementation, come check out her!

## 1. Summary

### 1.1. Introduce

This framework is container-based, even the framework itself is in the container, which improves a high degree of freedom instance reuse pool for developers.On the basis of the container, the framework core provides practical features such as exception handling, facades, and pipelines. Based on the framework core, web services, terminal services, api services, etc. are provided.

### 1.2. Features

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

### 1.3. Usage

##### First Step
```bash
git clone https://github.com/lynncho/shy.git
```

##### Second Step
```bash
composer install
```

### 1.4 Directory Structure

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

### 1.5 The Term

* Instance: "Class" is called an "instance" after instantiation. "Class" is static, does not occupy process memory, and "instance" has dynamic memory.
* Abstract Name: A unique name for an instance within a container.
* Chained call: The object's method returns the object in order to continue calling the next method.
* Callback: A function or method that is passed into another function or method.

### 2. Container

The container itself is an instance concept, this instance can store other instances centrally, hence the name container. Containers are the foundation of this framework, and all instances that span the entire lifecycle or need to be reused should be added to the container, including instances of the framework core class. Container classes provide a convenient way to bind, join, fetch, replace, or clean up instances, and provide the appropriate wrapper functions. The following mainly talk about these wrapper functions.

##### 2.1 Binding Instance

###### 2.1.1 Usage Mode

bind(Abstract name, Instance or Callback)

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
 * Add instances directly to the container
 */
shy('web', new shy\web());

/**
 * Direct run callback with parameters
 */
shy('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
}, $param1, $param2);

/**
 * Namespace as an abstract name, with a parameter instantiation namespace class
 */
shy('shy\web', $param1, $param2);

/**
 * Set the abstract name and instantiate the class with the namespace
 */
shy('web','shy\web');

/**
 * Set the abstract name to pdo and instantiate the class with the namespace
 *
 * This approach is wrong. Because pdo itself is a real php extension class, it can't be used as an abstract name for other instances.
 */
shy('pdo', 'shy\core\library\pdo');

```

The shy function is one of the core functions of the framework and represents the operation of the container.

When the function is running, if there is an abstract name to be added to the container, the join operation will not be performed at this time, and the old instance corresponding to the abstract name will be directly returned. If it does not exist, the abstract name and its corresponding instance are added to the container and the joined instance is returned.

Before the function adds an instance to the container, it tries to get the instance, such as getting it from the bound instance, or executing the bound callback to instantiate the object. If there is no previous binding instance or callback, the function will try to instantiate the object according to the usage pattern of 2.2.1 above. After the instance is added to the container, the binding is cleared to avoid taking up memory.

How do I know if an abstract name already exists? You can get all the abstract names in the container via the `shy_list()` function, or use the `in_shy_list()` function to determine if it already exists.

##### 2.3 Replacement Instance

###### 2.3.1 Usage Mode

1. make_new(abstract name, Any other parameter)
2. make_new(abstract name, instance or callback, Any other parameter)
3. make_new(namespace class name, Any other parameter)
4. make_new(abstract name, namespace class name, Any other parameter)

```php

make_new('web', new shy\web());

make_new('web', function ($param1, $param2) {
    return new shy\web($param1, $param2);
});

```

If you need to replace an instance in a container, you should not use the shy function, you should use the make_new function. The usage of these two functions is the same, so the complete code example is not given here.

##### 2.4 Clear Instance

```php
/**
 * Clear bindings and instances with abstract names for the web
 */
shy_clear('web');

```


**As can be seen from the above, this framework provides developers with open containers, and the instances including the framework core are inside the container, which can be easily operated. This improves the freedom of development, but developers must carefully comb the logic when operating the core classes of the framework to avoid problems.**

### 3. Facade

The facade provides a convenient way to call statically, via the magic method `__callStatic()` to call the method in **the delegate class**.

Implementation **facade proxy class** needs to extends the framework's **facade class**, the code example is as follows:

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

Thus, the `getInstance()` method of **the facade proxy class** overrides the method in **the facade class** and passes the instance of **the delegate class** to **the facade class**. Refer to **2.2 Join and Fetch Instances** to create and get the correct instance.

### 4. pipeline

The pipeline is an important scheduling tool for this framework, connecting the entire framework's operational processes including routing, middleware, and controllers.

The pipeline class method explains:

1. Send: set the incoming parameters, there is no limit to the number of parameters;
2. Through: sets the processing object of the pipeline;
3. Via: set the method to be executed by the incoming object. By default, the handle method is executed.
4. Then: the execution method of the pipeline, at the same time will set the incoming callback, the first parameter passed in is the callback;
5. Run: the execution method of the pipeline that does not require a callback. It cannot be chained call with the then method.

Developers can use the pipeline to build their own schedules, using code examples as follows:

```php
/**
 * Framework web module running, with callback execution
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

**When developers use the pipeline to customize the scheduling process, you should carefully sort through the running process.**

### 5. Middleware

The middleware is a special case when the pipeline then method is passed to the "callback of running the controller". The first parameter passed in is `$next`, which is the callback function for running the controller method.

```php
namespace app\http\middleware;

use shy\core\middleware;

class example implements middleware
{
    public function handle($next, ...$passable)
    {
        // Request processing
        $request = null;

        // Running the controller method
        $response = $next();

        // Response processing
        $response = ', example middleware, ' . json_encode($response);
        return $response;
    }
}

```

### 6. Router

The route obtains the request path through the request class, parses out the corresponding controller and method, and then dispatches the controller through the pipeline.

Routing support "configuration mode" and "path mode" can be turned off and enabled in the configuration file `app.php`. The configuration mode finds the configuration file according to the request path, obtains the controller and its method, and supports the middleware and path prefix. The path mode is to directly treat the request path as a controller and its method, which is a traditional method. When both modes are enabled at the same time, the configuration mode takes precedence.

Configuration mode file `router.php` example:

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

### 7. Controller

The method in the controller should return the data for the output of the framework, and the developer should not output directly in the controller. To use an instance in the controller, you need to pay attention to the use of a singleton or a new instance. The instances in the container, including the facade, are singletons.

### 8. Database

For the time being, this framework only provides encapsulation classes of pdo, redis and mysqli.

### 9. View

The framework's own template does not use the complex design of character parsing, because this way is not only complex to achieve, but also develops a set of template rules that require user learning. The template of this framework needs to be developed using native PHP syntax, and only provides a small amount of functions necessary for developers to use, learning and debugging costs are lower. However, developers are required to do a good job of preventing errors, such as `isset()`, `empty()`, `is_array()`.

In addition, to meet the needs of developers, the framework supports the Smarty template system.

##### 9.1 View class method

1. View: set view file
2. Layout: set layout file
3. With: Pass parameters to the view
4. Render: Render the view, the framework will automatically perform the rendering

##### 9.2 View Functions

1. View: The encapsulation of the view class. It is used to conveniently use the view in the controller, which can be passed in parameters or chained call. Each time you use this function, you will either create or replace a template instance in the container.
2. include_sub_view: Output view in layout page.
3. include_view: Introducing component view in layout or view。
4. param: Output variables or constants in the view, output error without using this function can not be processed by the framework.

using the view function in the controller method:

```php
public function index()
{
    $info = 'Hello World';
    $title = 'Shy Framework';

    return view('home', compact('title', 'info'))->layout('main');
}

```

The include_sub_view function sets the view output location in the layout, the include_view function to import the component view, the param function output variables and constants:

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

##### 9.3 Smarty

This framework provides support for smarty templates, which need to be installed via composer and enabled in the configuration file `app.php`.

```bash
composer require smarty/smarty
```

###### 9.3.1 Call the smarty template code example in the controller

```php
return smarty('smarty.tpl', $params);
```

###### 9.3.2 Smarty template code example

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

### 10. Miscellaneous Functions

1. config: Get the configuration of the configuration file
2. config_all: Get all configuration of the configuration file
3. logger: Logging
4. dd: Debug output

### 11. Command Mode

This framework supports command mode.

Execute the following command in the project root directory to view all commands:

```php
php console list
```

If you need an extension command, you can configure the command name, object, and method in the configuration file `console.php`.

Code example for make new command：

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
