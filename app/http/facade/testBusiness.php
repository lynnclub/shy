<?php
namespace app\http\facade;

use shy\core\facade;
use app\http\business\testBusiness as realTestBusiness;

class testBusiness extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realTestBusiness::class);
    }
}
