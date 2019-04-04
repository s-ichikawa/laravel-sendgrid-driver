<?php
namespace Sichikawa\LaravelSendgridDriver;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\TransportManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;

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
            $endpoint = isset($config['endpoint']) ? $config['endpoint'] : null;

            return new SendgridTransport($client, $config['api_key'], $endpoint);
        });
    }
}
