<?php

namespace App\Http\Facade;

use App\Http\Business\TestBusiness as realTestBusiness;
use Shy\Facade;

class TestBusiness extends Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realTestBusiness::class);
    }
}
