<?php
namespace Config;

use Apps\Index;

/**
 * Class Router
 *
 * @package Config
 */
class Router{

    public static $Routers = [
        //GET
        'get:/'     => array(
            'class'     => Index::class,
            'action'    => 'execute',
            'map_var'   => [],
        ),

        //GET /log
        'get:/log'     => array(
            'class'     => Index::class,
            'action'    => 'log',
            'map_var'   => [],
        ),

        //GET /log
        'get:/http'     => array(
            'class'     => Index::class,
            'action'    => 'http',
            'map_var'   => [],
        ),

        //GET /log
        'get:/header'     => array(
            'class'     => Index::class,
            'action'    => 'header',
            'map_var'   => [],
        ),

        //POST
        'post:/'     => array(
            'class'     => Index::class,
            'action'    => 'post',
            'map_var'   => [],
        ),


    ];

}
