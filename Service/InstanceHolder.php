<?php
namespace Service;

class InstanceHolder{
    private static $_redis = [];
    /***
     * @var \mysqli
     */
    private static $_mysql = null;


    /**
     * Redis实例化
     *
     * @param string $instance_name
     * @return \Redis|bool
     */
    public static function redisInstanceHolder(string $instance_name = 'default'): \Redis{
        $server     = Yaconf::get("redis.$instance_name");
        if(empty($server)){
            return false;
        }

        if(isset(self::$_redis[$server]) && self::$_redis[$server] instanceof \Redis){
            return self::$_redis[$server];
        }

        $__         = explode(':', $server);
        $redis      = new \Redis();
        $redis->pconnect($__[0], $__[1]);

        self::$_redis[$server] = $redis;
        return $redis;
    }

    /**
     * Mysql实例化
     *
     * @param string $instance_name
     * @param boolean $is_master
     * @return \mysqli|bool
     */
    public static function mysqlInstanceHolder(string $instance_name = 'default',
                                               bool $is_master = false): \mysqli{
        $server     = Yaconf::get("mysql.$instance_name.$is_master");
        if(empty($server)){
            return false;
        }
        $db         = new \mysqli($server['server'], $server['username'], $server['password']);
        if($db === false){
            return false;
        }

        $database   = $server['database'] ?? 'default';
        $charset    = $server['charset'] ?? 'utf8mb4';
        
        $db->select_db($database);
        $db->set_charset($charset);

        return $db;
    }
}