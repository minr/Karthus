<?php
namespace Service;

/***
 * Class Api
 *
 * @package Service
 */
class Api{

    /**
     * @var \Service\Request;
     */
    private $request = null;

    public function __construct(\Service\Request $request) {
        $this->request = $request;
    }

    /***
     * @param string $instance_name
     * @param bool   $is_master
     * @param string $database
     * @param string $charset
     * @return \mysqli
     */
    public function mysqliInstance(string $instance_name = 'default',
                                   bool $is_master = false, string $database = 'blued',
                                   string $charset = 'utf8mb4'): \mysqli{
        return InstanceHolder::mysqlInstanceHolder($instance_name, $is_master, $database, $charset);
    }

    /***
     * @param string $instance_name
     * @return \Redis
     */
    public function redisInstance($instance_name = 'users'): \Redis{
        return InstanceHolder::redisInstanceHolder($instance_name);
    }
}