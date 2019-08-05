# Karthus

![avatar](https://gss0.bdstatic.com/94o3dSag_xI4khGkpoWK1HF6hhy/baike/w%3D268%3Bg%3D0/sign=ba42e05313d5ad6eaaf963ecb9f05ee6/b7003af33a87e9505d7328fb18385343faf2b4c5.jpg)

A simple Framework For Swoole

#使用说明

```php 
try {
    $service = new Service\Karthus('http://0.0.0.0:8000');   //设置服务的IP和端口
    $service
        ->setRouter(Config\Router::$Routers) //载入路由
        ->setLogFile(LOGGER_PATH . '/http.log') //设置日志路径
        ->setLogLevel(Service\Karthus::LEVER_DEBUG) //设置日志等级
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