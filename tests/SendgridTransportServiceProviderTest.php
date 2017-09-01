<?php

use Illuminate\Mail\TransportManager;
use Sichikawa\LaravelSendgridDriver\SendgridTransportServiceProvider;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;

class SendgridTransportServiceProviderTest extends TestCase
{
    public function registerServiceProvider()
    {
        $app = $this->getMockForAbstractClass(\Illuminate\Container\Container::class);

        $app['config'] = new \MockConfig();
        $app['config']->set('mail.driver', 'sendgrid');

        $serviceProvider = new SendgridTransportServiceProvider($app);
        $transportManager = new TransportManager($app);
        $serviceProvider->extendTransportManager($transportManager);

        return $transportManager;
    }

    public function testCreateSendgridDriver()
    {
        $transportManager = $this->registerServiceProvider();

        $sendgridDriver = $transportManager->driver();

        $this->assertInstanceOf(SendgridTransport::class, $sendgridDriver);
    }
}
