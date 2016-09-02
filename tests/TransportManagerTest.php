<?php

use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridV3Transport;
use Sichikawa\LaravelSendgridDriver\TransportManager;

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
        $app = $this->getMockForAbstractClass(\Illuminate\Container\Container::class);
        $app['config'] = new MockConfig();
        $app['config']->set('services.sendgrid.version', 'v3');
        $transportManager = new TransportManager($app);
        $transportManager->setDefaultDriver('sendgrid');

        $createSendgridDriver = \Closure::bind(function () {
            return $this->createSendgridDriver();
        }, $transportManager, 'Sichikawa\LaravelSendgridDriver\TransportManager');
        $this->assertInstanceOf(SendgridV3Transport::class, $createSendgridDriver());
    }
}
