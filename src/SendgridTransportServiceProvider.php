<?php

namespace Sichikawa\LaravelSendgridDriver;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\MailManager;
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
        $this->app->afterResolving(MailManager::class, function (MailManager $mail_manager) {
            $mail_manager->extend("sendgrid", function ($config) {
                if (! isset($config['api_key'])) {
                    $config = $this->app['config']->get('services.sendgrid', []);
                }
                $client = new HttpClient(Arr::get($config, 'guzzle', []));
                $endpoint = isset($config['endpoint']) ? $config['endpoint'] : null;

                return new SendgridTransport($client, $config['api_key'], $endpoint);
            });

        });
    }
}
