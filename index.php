#!/bin/env php
<?php
if(PHP_SAPI !== 'cli'){
    exit("ONLY CLI\n");
}
date_default_timezone_set('Asia/Shanghai');

//查看php版本号
if(version_compare(PHP_VERSION, '7.1', '<')){
    exit("PHP VERSION < 7.1+");
}

const EXTENSIONS  = ['swoole'];
const APP_NAME    = 'Karthus';
const LOGGER_PATH = '/data/logs/'. APP_NAME;
const LOGGER_DIR  = LOGGER_PATH .'/logs';
const __ROOT__    = __DIR__;

//启动前，进行检查必须的扩展！！！！
if(is_array(EXTENSIONS)){
    foreach (EXTENSIONS as $item){
        if(extension_loaded($item) === false){
            echo "Error: missing [$item] extension", "\n";
            exit();
        }
    }
}

//注册一个自动载入
spl_autoload_register(function($name){
    $name   = str_replace("\\", DIRECTORY_SEPARATOR, $name);
    $filename   = __ROOT__ . "/$name.php";
    if(file_exists($filename)){
        include_once($filename);
    }
});

try {
    $service = new Service\Karthus('http://0.0.0.0:8000');
    $service
        ->setRouter(Config\Router::$Routers)
        ->setLogFile(LOGGER_PATH . '/http.log')
        ->setLogLevel(Service\Karthus::LEVER_DEBUG)
        ->setProcessName(APP_NAME)
        ->setCompression(true)
        ->setPidFile()
        ->setWorkerNum(16)
        ->responseJSON(true)
        ->run();
}catch (Exception $exception){
    (new Tools\Logger())->error("{$exception->getMessage()}");
}