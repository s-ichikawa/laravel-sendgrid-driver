<?php
namespace Sichikawa\LaravelSendgridDriver;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Arr;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;

class TransportManager extends \Illuminate\Mail\TransportManager
{
    /**
     * Create an instance of the SendGrid Swift Transport driver.
     *
     * @return Transport\SendGridTransport
     */
    protected function createSendgridDriver()
    {
        $config = $this->app['config']->get('services.sendgrid', array());
        $client = new HttpClient(Arr::get($config, 'guzzle', []));
        return new SendgridTransport($client, $config['api_key']);
    }
}
