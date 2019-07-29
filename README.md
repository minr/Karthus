# Karthus
A simple Framework For Swoole

#使用说明

```php 
try {
    $service = new Service\Pandora('http://0.0.0.0:8000');   //设置服务的IP和端口
    $service
        ->setRouter(Config\Router::$Routers) //载入路由
        ->setLogFile(LOGGER_PATH . '/http.log') //设置日志路径
        ->setLogLevel(Service\Pandora::LEVER_DEBUG) //设置日志等级
        ->setProcessName(APP_NAME) //服务名
        ->setCompression(true) //启用HTTP压缩输出
        ->setPidFile() //设置PID文件
        ->setWorkerNum(16) //设置进程数
        ->responseJSON(true) //是否开启JSON输出，默认开启
        ->run();
}catch (Exception $exception){
    (new Tools\Logger())->error("{$exception->getMessage()}");
}
```

## 路由说明

```php 
//GET
        'get:/'     => array(
            'class'     => Index::class,
            'action'    => 'execute',
            'map_var'   => [],
        )
```

路由处于 `Config/Router` 中 


## 其他请参考Apps/*.php 中