<?php

use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;

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
}
