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

        //GET /1111
        'get:/:number'     => array(
            'class'     => Index::class,
            'action'    => 'execute',
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
