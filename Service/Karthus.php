<?php
namespace Service;

use Service\Http\Message\RequestMethodInterface;
use Service\Http\Router\Router;

/**
 * Class Karthus
 *
 * @package Service
 */
class Karthus extends Core{
    private $router = [];

    /**
     * tokens替换规则
     *
     * @var array
     */
    public static $tokens      = [
        ':string'   => '([a-zA-Z0-9-_]+)',
        ':number'   => '(\d+)',
        ':alpha'    => '([a-zA-Z0-9-_\%\-\.]+)', //支持数字英文_ % - . 等特殊字符
    ];

    /**
     * 设置路由
     *
     * @param array $router
     * @return Core
     */
    public function setRouter(array $router): Core{
        $routes = $router['routes'] ?? [];
        if(empty($routes)){
            echo "Set Routers !!!\n";
            exit();
        }
        foreach ($routes as $pattern => $route){
            $pattern      = strtr($pattern, \Service\Karthus::$tokens);
            $routerMethod = $router['allowed_method'] ?? RequestMethodInterface::METHOD_GET;
            $routerParams = $router['params'] ?? '';
            $routerClass  = $router['class'] ?? '';

            if($routerClass === ''){
                continue;
            }

            $this->R->$$routerMethod($pattern, $routerClass, $routerParams);
        }
        return $this;
    }

    /***
     * @throws \Exception
     */
    public function requestDo(): void {
        $path   = strval($this->request->getPathInfo());
        $method = strval($this->request->getMethod());
        $method = strtolower($method);
        if($path === ''){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => 'Api Not Found',
            ));
            return;
        }
        //进行匹配
        $match  = $this->R->find($method, $path);
        if(empty($match)){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => 'Api Not Found',
            ));
            return;
        }


        $_path  = "$method:$path";

        //开始遍历
        $routers = $this->router['routes'] ?? [];
        if(empty($routers)){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => 'Api Not Found',
            ));
            return;
        }

        $R     = new Router($this->request);
        $matched = false;
        foreach ($routers as $pattern => $router) {
            $pattern      = strtr($pattern, \Service\Karthus::$tokens);
            $routerMethod = $router['allowed_method'] ?? RequestMethodInterface::METHOD_GET;
            $routerParams = $router['params'] ?? '';
            $routerClass  = $router['class'] ?? '';

            if($routerClass === ''){
                continue;
            }

            $R->$$routerMethod($pattern, $routerClass);
        }
        $R->run();

        if ($matched === false) {
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => 'Api Not Found',
            ));
            return;
        }

        if(!isset($handlerName['class'])){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => 'Api Not Found',
            ));
            return;
        }

        $matches_data   = [];
        if (!empty($handlerName['map_var']) && !empty($matches)) {
            foreach ($handlerName['map_var'] as $idx => $varname) {
                if (!isset($matches[$idx])) {
                    continue;
                }

                $value = intval($matches[$idx]);
                $matches_data[$varname] = $value;
            }
        }

        $this->request->setParams($matches_data);

        $targetModel    = $handlerName['class'];
        if(class_exists($targetModel) === false){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => "Class[$targetModel] Not found",
            ));
            return;
        }

        $targetClass    = new $targetModel();
        $method_action  = isset($handlerName['action']) && $handlerName['action']
            ? strval($handlerName['action']) : 'execute';

        //先看有没有前置init方法，有我就直接运行
        if(method_exists($targetClass, 'init')){
            call_user_func_array(array($targetClass, 'init'), []);
        }

        //先看有没有authentication方法，有我就直接运行
        if(method_exists($targetClass, 'authentication')){
            call_user_func_array(array($targetClass, 'authentication'), []);
        }

        if(method_exists($targetClass, $method_action) === false){
            $this->httpResponse(HttpCode::API_CODE_NOT_FOUND, array(
                'code'      => HttpCode::API_CODE_NOT_FOUND,
                'message'   => "Class[$targetModel] Action[$method_action] Not found",
            ));
            return;
        }

        $sendData = call_user_func_array(array($targetClass, $method_action), []);

        //先看有没有后置done方法，有我就直接运行
        if(method_exists($targetClass, 'done')){
            call_user_func_array(array($targetClass, 'done'), []);
        }

        if(empty($sendData)){
            $this->httpResponse(HttpCode::API_CODE_INTERNAL_SERVER_ERROR, array(
                'code'      => HttpCode::API_CODE_INTERNAL_SERVER_ERROR,
                'message'   => 'sendData Empty',
            ));
            return;
        }

        $this->httpResponse($sendData['code'], $sendData);
    }
    /**
     * 初始化worker进程时执行的callback
     *
     * @param \Swoole\Server $server
     * @param integer $workerId
     * @return void
     */
    public function initWorker(\Swoole\Server $server, int $workerId) {
        // TODO: Implement initWorker() method.
    }

    /**
     * 获取路由配置
     *
     * @return array
     */
    public function getRouter():array {
        return $this->router;
    }
}
