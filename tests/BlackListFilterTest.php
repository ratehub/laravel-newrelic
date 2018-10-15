<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use RateHub\NewRelic\Exceptions\BlacklistExceptionFilter;

class BlackListFilterTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBlacklistWorks()
    {
        $blacklistFilter = new BlacklistExceptionFilter([\InvalidArgumentException::class]);

        $this->assertTrue($blacklistFilter->shouldReport(new \Exception('Should report InvalidArgumentException')));
        $this->assertFalse($blacklistFilter->shouldReport(new \InvalidArgumentException('Just for a test')), 'Should not report InvalidArgumentException');
    }
}
