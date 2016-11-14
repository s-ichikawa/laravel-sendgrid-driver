<?php
namespace Sichikawa\LaravelSendgridDriver;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\TransportManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridV3Transport;

class SendgridTransportServiceProvider extends ServiceProvider
{
    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(TransportManager::class, function(TransportManager $manager) {
            $this->extendTransportManager($manager);
        });
    }

    public function extendTransportManager(TransportManager $manager)
    {
        $manager->extend('sendgrid', function() {
            $config = $this->app['config']->get('services.sendgrid', array());
            $client = new HttpClient(Arr::get($config, 'guzzle', []));

            if (Arr::get($config, 'version') === 'v3') {
                return new SendgridV3Transport($client, $config['api_key']);
            }

            return new SendgridTransport($client, $config['api_key']);
        });
    }
}
