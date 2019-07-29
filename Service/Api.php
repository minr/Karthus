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
}