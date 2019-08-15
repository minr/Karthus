<?php
namespace Service;

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
        $this->router = $router;
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
        $_path  = "$method:$path";

        //开始遍历
        $routers = $this->getRouter();
        $matched = false;
        foreach ($routers as $pattern => $handlerName) {
            $pattern = strtr($pattern, \Service\Karthus::$tokens);
            if (!preg_match("#^$pattern$#is", $_path, $matches)) {
                continue;
            }
            $matched = true;
            break;
        }

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
