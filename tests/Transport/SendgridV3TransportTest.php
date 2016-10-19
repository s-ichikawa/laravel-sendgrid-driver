<?php

use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\Message;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridV3Transport;

class SendgridV3TransportTest extends TestCase
{
    /**
     * @var SendgridV3Transport
     */
    protected $transport;

    protected function setUp()
    {
        parent::setUp();
        $client = new HttpClient();
        $this->transport = new SendgridV3Transport($client, $this->api_key);
    }


    public function testSend()
    {
        $message = new Message($this->getMessage());
        $message->from('from@sink.sendgrid.net', 'test_from')
            ->to('to@sink.sendgrid.net', 'test_to');
        $res = $this->transport->send($message->getSwiftMessage());
        $this->assertEquals(202, $res->getStatusCode());
    }

    public function testMultipleSend()
    {
        $message = new Message($this->getMessage());
        $message->from('from@sink.sendgrid.net', 'test_from')
            ->to('foo@sink.sendgrid.net', 'foo');
        $res = $this->transport->send($message->getSwiftMessage());

        $this->assertEquals(202, $res->getStatusCode());

        $message = new Message($this->getMessage());
        $message->from('from@sink.sendgrid.net', 'test_from')
            ->to('bar@sink.sendgrid.net', 'bar');
        $res = $this->transport->send($message->getSwiftMessage());

        $this->assertEquals(202, $res->getStatusCode());
    }

    public function testGetPersonalizations()
    {


        $getPersonalizations = \Closure::bind(function ($message) {
            return $this->getPersonalizations($message);
        }, $this->transport, SendgridV3Transport::class);

        $message = $this->getMessage();

        $to = 'to_user@exsample.com';
        $to_name = 'to_user';
        $message->setTo($to, $to_name);

        $cc = 'cc_user@exsample.com';
        $cc_name = 'cc_user';
        $message->setCc($cc, $cc_name);

        $bcc = 'bcc_user@exsample.com';
        $bcc_name = 'bcc_user';
        $message->setBcc($bcc, $bcc_name);

        $res = $getPersonalizations($message);

        $this->assertEquals($to, array_get($res, '0.to.0.email'));
        $this->assertEquals($to_name, array_get($res, '0.to.0.name'));
        $this->assertEquals($cc, array_get($res, '0.cc.0.email'));
        $this->assertEquals($cc_name, array_get($res, '0.cc.0.name'));
        $this->assertEquals($bcc, array_get($res, '0.bcc.0.email'));
        $this->assertEquals($bcc_name, array_get($res, '0.bcc.0.name'));
    }

    public function testGetContents()
    {
        $getContents = \Closure::bind(function ($message) {
            return $this->getContents($message);
        }, $this->transport, SendgridV3Transport::class);

        $message = new Message($this->getMessage());
        $message->getSwiftMessage()->setChildren([Swift_MimePart::newInstance(
            'This is a test.'
        )]);

        $res = $getContents($message->getSwiftMessage());
        $this->assertEquals('text/plain', array_get($res, '0.type'));
        $this->assertEquals('This is a test.', array_get($res, '0.value'));
        $this->assertEquals('text/html', array_get($res, '1.type'));
        $this->assertEquals('Test body.', array_get($res, '1.value'));
    }

    public function testGetAttachments()
    {
        $getAttachment = \Closure::bind(function ($message) {
            return $this->getAttachments($message);
        }, $this->transport, SendgridV3Transport::class);

        $message = new Message($this->getMessage());

        $file = __DIR__ . '/test.png';
        $message->attach($file);

        $res = $getAttachment($message->getSwiftMessage());
        $this->assertEquals(base64_encode(file_get_contents($file)), array_get($res, '0.content'));
        $this->assertEquals('test.png', array_get($res, '0.filename'));
    }

    public function testSetParameters()
    {
        $setParameters = \Closure::bind(function ($message, $data) {
            return $this->setParameters($message, $data);
        }, $this->transport, SendgridV3Transport::class);

        $parameters = [
            'categories' => 'category1'
        ];
        $message = new Message($this->getMessage());
        $message->embedData($parameters, 'sendgrid/x-smtpapi');
        $data = [];
        $data = $setParameters($message->getSwiftMessage(), $data);
        $this->assertEquals($parameters, $data);
    }

    public function testSetPersonalizations()
    {
        $setParameters = \Closure::bind(function ($message, $data) {
            return $this->setParameters($message, $data);
        }, $this->transport, SendgridV3Transport::class);

        $personalizations = [
            [
                'substitutions' => [
                    'substitutions_key' => 'substitutions_value',
                ],
                'custom_args' => [
                    'custom_args_key' => 'custom_args_value'
                ],
                'send_at' => time()
            ],
        ];

        $message = new Message($this->getMessage());
        $message->embedData([
            'personalizations' => $personalizations,
        ], 'sendgrid/x-smtpapi');
        $data = [];
        $data = $setParameters($message->getSwiftMessage(), $data);
        $this->assertEquals(['personalizations' => $personalizations], $data);
    }

    public function testGetFrom()
    {
        $getFrom = \Closure::bind(function ($message) {
            return $this->getFrom($message);
        }, $this->transport, SendgridV3Transport::class);

        $from = 'test@exsample.com';
        $from_name = 'test_user';

        $message = $this->getMessage();
        $message->setFrom($from, $from_name);

        $this->assertEquals(['email' => $from, 'name' => $from_name], $getFrom($message));
    }

    public function testReplyTo()
    {
        $getReplyTo = \Closure::bind(function ($message) {
            return $this->getReplyTo($message);
        }, $this->transport, SendgridV3Transport::class);

        $reply_to = 'test@exsample.com';
        $reply_to_name = 'test_user';

        $message = $this->getMessage();
        $message->setReplyTo($reply_to, $reply_to_name);

        $this->assertEquals(['email' => $reply_to, 'name' => $reply_to_name], $getReplyTo($message));
    }

    /**
     * @return Swift_Message
     */
    private function getMessage()
    {
        return new Swift_Message('Test subject', 'Test body.');
    }
}
