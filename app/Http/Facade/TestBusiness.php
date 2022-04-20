<?php

namespace App\Http\Facade;

use Shy\Facade;
use App\Http\Business\TestBusiness as realTestBusiness;

class TestBusiness extends Facade
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
