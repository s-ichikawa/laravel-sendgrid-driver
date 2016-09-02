<?php

use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridV3Transport;

class TransportManagerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }


    public function testCreateSendgridDriver()
    {
        $createSendgridDriver = \Closure::bind(function () {
            return $this->createSendgridDriver();
        }, $this->transportManager, 'Sichikawa\LaravelSendgridDriver\TransportManager');
        $this->assertInstanceOf(SendgridTransport::class, $createSendgridDriver());
    }

    public function testCreateSendgridV3Driver()
    {
        $createSendgridV3Driver = \Closure::bind(function () {
            return $this->createSendgridv3Driver();
        }, $this->transportManager, 'Sichikawa\LaravelSendgridDriver\TransportManager');
        $this->assertInstanceOf(SendgridV3Transport::class, $createSendgridV3Driver());
    }
}
