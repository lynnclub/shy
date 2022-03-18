<?php

use PHPUnit\Framework\TestCase;
use Shy\Facade\Cache;

class redisTest extends TestCase
{
    protected function setUp(): void
    {
        require __DIR__ . '/../bootstrap/command.php';
    }

    public function testConnection()
    {
        Cache::set('shy_test_redis', 1);
        $this->assertEquals(1, Cache::get('shy_test_redis'));

        $redisTest = Cache::connection('test');
        $redisTest->set('shy_test_redis', 2);
        $this->assertEquals(1, Cache::get('shy_test_redis'));
        $this->assertEquals(2, $redisTest->get('shy_test_redis'));
    }
}
