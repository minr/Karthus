<?php
namespace Service\Http\Router;
use Service\Http\Message\RequestMethodInterface;

/***
 * Class Router
 *
 * @package Service\Http\Router
 */
class Router implements RequestMethodInterface {
    /**
     * @var array The route patterns and their handling functions
     */
    private $routers    = [];

    /***
     *
     * Store a route and a handling function to be executed when accessed using one of the specified methods.
     *
     * @param string $methods   Allowed methods, | delimited
     * @param string $pattern   A route pattern such as /about/system
     * @param object|callable  $fn  The handling function to be executed
     * @param array  $params    The handling function Params
     */
    public function match(string $methods, string $pattern, $fn, array $params = array()){
        $methods = strtoupper($methods);

        foreach (explode('|', $methods) as $method){
            $this->routers[$method][] = [
                'pattern'   => $pattern,
                'fn'        => $fn,
                'params'    => $params,
            ];
        }
    }

    /***
     * @param string $pattern
     * @param        $fn
     * @param array  $params
     */
    public function get(string $pattern, $fn, $params = array()){
        $this->match(self::METHOD_GET, $pattern, $fn, $params);
    }

    /***
     * @param string $pattern
     * @param        $fn
     * @param array  $params
     */
    public function post(string $pattern, $fn, $params = array()){
        $this->match(self::METHOD_POST, $pattern, $fn, $params);
    }

    /***
     * @param string $pattern
     * @param        $fn
     * @param array  $params
     */
    public function delete(string $pattern, $fn, $params = array()){
        $this->match(self::METHOD_DELETE, $pattern, $fn, $params);
    }

    /***
     * @param string $pattern
     * @param        $fn
     * @param array  $params
     */
    public function put(string $pattern, $fn, $params = array()){
        $this->match(self::METHOD_PUT, $pattern, $fn, $params);
    }

    /**
     * @param string $method
     * @param string $pattern
     * @return array|null
     */
    public function find(string $method, string $pattern): ?array{
        $find   = $this->routers[$method] ?? [];
        if(empty($find)){
            return [];
        }

        $match  = [];
        //开始进行匹配了？
        foreach ($find as $item){
            $preg   = $item['pattern'] ?? '';
            if($preg === ''){
                continue;
            }
            if(preg_match("/$preg/", $pattern)){
                $match = $item;
                break;
            }
        }

        return $match;
    }
}
