<?php

namespace App\Http\Facades;

use Shy\Core\Facade;
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
