<?php

use Illuminate\Mail\TransportManager;
use Sichikawa\LaravelSendgridDriver\SendgridTransportServiceProvider;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridV3Transport;

class SendgridTransportServiceProviderTest extends TestCase
{
    public function registerServiceProvider($version = null)
    {
        $app = $this->getMockForAbstractClass(\Illuminate\Container\Container::class);

        $app['config'] = new \MockConfig();
        $app['config']->set('mail.driver', 'sendgrid');

        if ($version) {
            $app['config']->set('services.sendgrid.version', $version);
        }

        $serviceProvider = new SendgridTransportServiceProvider($app);
        $transportManager = new TransportManager($app);
        $serviceProvider->extendTransportManager($transportManager);

        return $transportManager;
    }

    public function testCreateSendgridDriver()
    {
        $transportManager = $this->registerServiceProvider('v2');
        $sendgridDriver = $transportManager->driver();
        $this->assertInstanceOf(SendgridTransport::class, $sendgridDriver);
    }

    public function testCreateSendgridV3Driver()
    {
        $transportManager = $this->registerServiceProvider('v3');

        $sendgridDriver = $transportManager->driver();

        $this->assertInstanceOf(SendgridV3Transport::class, $sendgridDriver);
    }
}
