<?php
namespace Sichikawa\LaravelSendgridDriver;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Arr;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridV3Transport;

class TransportManager extends \Illuminate\Mail\TransportManager
{
    /**
     * Create an instance of the SendGrid Swift Transport driver.
     *
     * @return SendGridTransport|SendgridV3Transport
     */
    protected function createSendgridDriver()
    {
        $config = $this->app['config']->get('services.sendgrid', array());
        $client = new HttpClient(Arr::get($config, 'guzzle', []));
        if (Arr::get($config, 'version') === 'v3') {
            $pretend = isset($config['pretend']) ? $config['pretend'] : false;
            return new SendgridV3Transport($client, $config['api_key'], $pretend);
        }
        return new SendgridTransport($client, $config['api_key']);
    }
}
