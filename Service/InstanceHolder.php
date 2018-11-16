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
    public static function redisInstanceHolder(string $instance_name = 'users'): \Redis{
        $server     = self::redisQconf($instance_name);
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
     * @param string $database
     * @param string $charset
     * @return \mysqli|bool
     */
    public static function mysqlInstanceHolder(string $instance_name = 'default',
                                               bool $is_master = false, string $database = 'blued',
                                               string $charset = 'utf8mb4'): \mysqli{
        $server     = self::mysqliQconf($instance_name, !!$is_master);
        if(empty($server)){
            return false;
        }
        $db         = new \mysqli($server['server'], $server['username'], $server['password']);
        if($db === false){
            return false;
        }
        $db->select_db($database);
        $db->set_charset($charset);

        return $db;
    }

    /**
     * qconf获取redis配置信息
     *
     * @param string $instance_name
     * @return string
     */
    private static function redisQconf(string $instance_name = 'users'):string{
        switch ($instance_name){
            case 'liveshow':
                $instance_name = "live";
                break;
        }
        $path   = "/blued/backend/umem/$instance_name";
        $qconf  = new \Qconf();
        return $qconf->getHost($path, '', 1);
    }

    /****
     * 获取MYSQL Qconf信息
     *
     * @param string $instance_name
     * @param bool   $is_master
     * @return array
     */
    private static function mysqliQconf(string $instance_name = 'default', bool $is_master = false): array{
        $instance_name = !in_array($instance_name, [
            'default',
            'bluedmis',
            'pay',
            'adm',
            'bluedmis_oversea',
            'pay_oversea',
            'pay_expenses',
            'ticktocks',
            'api',
        ]) ? 'default' : strval($instance_name);
        if($is_master === true){
            $path = "/blued/backend/udb/$instance_name/master";
        }else{
            $path = "/blued/backend/udb/$instance_name/slave";
        }
        $qconf    = new \Qconf();
        $server   = $qconf->getHost($path, '', 1);
        $username = $qconf->getConf("/blued/backend/udb/$instance_name/username", "", 1);
        $password = $qconf->getConf("/blued/backend/udb/$instance_name/password", "", 1);

        return array(
            'server'    => $server,
            'username'  => $username,
            'password'  => $password,
        );
    }
}