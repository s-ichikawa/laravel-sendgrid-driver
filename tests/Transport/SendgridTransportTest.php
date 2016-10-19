<?php

use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\Message;

class SendgridTransportTest extends TestCase
{
    /**
     * @var SendgridTransport
     */
    protected $transport;

    protected function setUp()
    {
        parent::setUp();
        $client = new HttpClient();
        $this->transport = new SendgridTransport($client, $this->api_key);
    }


    public function testSend()
    {
        $message = new Message($this->getMessage());
        $message->from('from@sink.sendgrid.net', 'test_from')
            ->to('to@sink.sendgrid.net', 'test_to');
        $res = $this->transport->send($message->getSwiftMessage());
        $this->assertEquals(200, $res->getStatusCode());
    }

    public function testMultipleSend()
    {
        $message = new Message($this->getMessage());
        $message->from('from@sink.sendgrid.net', 'test_from')
            ->to('foo@sink.sendgrid.net', 'foo');
        $res = $this->transport->send($message->getSwiftMessage());

        $this->assertEquals(200, $res->getStatusCode());

        $message = new Message($this->getMessage());
        $message->from('from@sink.sendgrid.net', 'test_from')
            ->to('bar@sink.sendgrid.net', 'bar');
        $res = $this->transport->send($message->getSwiftMessage());

        $this->assertEquals(200, $res->getStatusCode());
    }

    public function testSetTo()
    {
        $to = 'test@exsample.com';
        $to_name = 'test_user';
        $setTo = \Closure::bind(function (&$data, $message) {
            $this->setTo($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $data = [];
        $message = $this->getMessage();
        $message->setTo($to, $to_name);
        $setTo($data, $message);
        $this->assertEquals($to, array_pop($data['to']));
        $this->assertEquals($to_name, array_pop($data['toname']));
    }

    public function testSetCc()
    {
        $cc = 'test@exsample.com';
        $cc_name = 'test_user';
        $setCc = \Closure::bind(function (&$data, $message) {
            $this->setCc($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $data = [];
        $message = $this->getMessage();
        $message->setCc($cc, $cc_name);
        $setCc($data, $message);
        $this->assertEquals($cc, array_pop($data['cc']));
        $this->assertEquals($cc_name, array_pop($data['ccname']));
    }

    public function testSetBcc()
    {
        $bcc = 'test@exsample.com';
        $bcc_name = 'test_user';
        $setBcc = \Closure::bind(function (&$data, $message) {
            $this->setBcc($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $data = [];
        $message = $this->getMessage();
        $message->setBcc($bcc, $bcc_name);
        $setBcc($data, $message);
        $this->assertEquals($bcc, array_pop($data['bcc']));
        $this->assertEquals($bcc_name, array_pop($data['bccname']));
    }

    public function testSetText()
    {
        $setText = \Closure::bind(function (&$data, $message) {
            return $this->setText($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');

        $data = [];
        $message = new Message($this->getMessage());
        $message->getSwiftMessage()->setChildren([Swift_MimePart::newInstance(
            'This is a test.'
        )]);
        $setText($data, $message->getSwiftMessage());

        $this->assertEquals('This is a test.', $data['text']);
    }

    public function testSetAttachment()
    {
        $setAttachment = \Closure::bind(function (&$data, $message) {
            return $this->setAttachment($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $data = [];
        $message = new Message($this->getMessage());
        $message->attach(__DIR__ . '/test.png');
        $setAttachment($data, $message->getSwiftMessage());
        $this->assertEquals('stream', get_resource_type($data['files[test.png]']));
    }

    public function testSetSmtpApi()
    {
        $setSmtpApi = \Closure::bind(function (&$data, $message) {
            return $this->setSmtpApi($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $data = [];
        $message = new Message($this->getMessage());
        $message->embedData([
            'category' => 'foo'
        ], 'sendgrid/x-smtpapi');
        $setSmtpApi($data, $message->getSwiftMessage());
        $this->assertEquals(json_encode(['category' => 'foo']), $data['x-smtpapi']);
    }

    public function testGetFromAddresses()
    {
        $from = 'test@exsample.com';
        $from_name = 'test_user';
        $getFromAddresses = \Closure::bind(function ($message) {
            return $this->getFromAddresses($message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $message = $this->getMessage();
        $message->setFrom($from, $from_name);
        $this->assertEquals([$from, $from_name], $getFromAddresses($message));
    }


    public function testSetReplyTo()
    {
        $reply_to = 'test@exsample.com';
        $reply_to_name = 'test_user';
        $setReplyTo = \Closure::bind(function (&$data, $message) {
            $this->setReplyTo($data, $message);
        }, $this->transport, 'Sichikawa\LaravelSendgridDriver\Transport\SendGridTransport');
        $data = [];
        $message = $this->getMessage();
        $message->setReplyTo($reply_to, $reply_to_name);
        $setReplyTo($data, $message);
        $this->assertEquals($reply_to, $data['replyto']);
    }

    /**
     * @return Swift_Message
     */
    private function getMessage()
    {
        return new Swift_Message('Test subject', 'Test body.');
    }
}
