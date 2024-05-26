<?php

namespace Transport;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Sichikawa\LaravelSendgridDriver\SendGrid;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SendgridTransportTest extends \TestCase
{
    use SendGrid;

    protected SendgridTransport $transport;
    private \ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $client = new Client();
        $this->transport = new SendgridTransport($client, $this->api_key);
        $this->reflection = new \ReflectionClass($this->transport);
    }

    public function testGetPersonalizations()
    {
        $email = (new Email())
            ->to(
                (new Address('to1@sink.sendgrid.net', 'test_to1')),
                (new Address('to2@sink.sendgrid.net', 'test_to2')),
            )
            ->cc(
                (new Address('cc1@sink.sendgrid.net', 'test_cc1')),
                (new Address('cc2@sink.sendgrid.net', 'test_cc2')),
            )
            ->bcc(
                (new Address('bcc1@sink.sendgrid.net', 'test_bcc1')),
                (new Address('bcc2@sink.sendgrid.net', 'test_bcc2')),
            );

        $method = $this->reflection->getMethod('getPersonalizations');
        $method->setAccessible(true);

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            [
                'to' => [
                    ['email' => 'to1@sink.sendgrid.net', 'name' => 'test_to1'],
                    ['email' => 'to2@sink.sendgrid.net', 'name' => 'test_to2'],
                ],
                'cc' => [
                    ['email' => 'cc1@sink.sendgrid.net', 'name' => 'test_cc1'],
                    ['email' => 'cc2@sink.sendgrid.net', 'name' => 'test_cc2'],
                ],
                'bcc' => [
                    ['email' => 'bcc1@sink.sendgrid.net', 'name' => 'test_bcc1'],
                    ['email' => 'bcc2@sink.sendgrid.net', 'name' => 'test_bcc2'],
                ],
            ]
        ], $result);
    }

    public function testGetFrom()
    {
        $email = (new Email())
            ->from(
                (new Address('from1@sink.sendgrid.net', 'test_from1')),
            );

        $method = $this->reflection->getMethod('getFrom');
        $method->setAccessible(true);

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            'email' => 'from1@sink.sendgrid.net',
            'name' => 'test_from1',
        ], $result);
    }

    public function testGetContent()
    {
        $email = (new Email())
            ->text('test body')
            ->html('<body>test body</body>');

        $method = $this->reflection->getMethod('getContents');
        $method->setAccessible(true);

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            [
                'type' => 'text/plain',
                'value' => 'test body'
            ],
            [
                'type' => 'text/html',
                'value' => '<body>test body</body>'
            ],
        ], $result);
    }

    public function testXMessageID()
    {
        $client = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->onlyMethods(['request'])
            ->getMock();

        $client->expects($this->once())
            ->method('request')
            ->willReturn(new \GuzzleHttp\Psr7\Response(200, [
                'X-Message-ID' => $messageId = Str::random(32),
            ]));

        $transport = new SendgridTransport($client, $this->api_key);
        $reflection = new \ReflectionClass($this->transport);

        $email = (new Email())
            ->text('test body')
            ->html('<body>test body</body>')
            ->to(new Address('to@sink.sendgrid.net', 'test_to'))
            ->from(new Address('from@sink.sendgrid.net', 'test_from'));

        $send = new SentMessage($email, Envelope::create($email));

        $method = $reflection->getMethod('doSend');
        $method->setAccessible(true);

        $method->invoke($transport, $send);

        $this->assertEquals($messageId, $send->getMessageId());

        $this->assertEquals($messageId, $email->getHeaders()->getHeaderBody('X-Sendgrid-Message-ID'));
    }

    public function testGetReplyTo()
    {
        $email = (new Email())
            ->replyTo((new Address('from1@sink.sendgrid.net', 'test_from1')));

        $method = $this->reflection->getMethod('getReplyTo');
        $method->setAccessible(true);

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            'email' => 'from1@sink.sendgrid.net',
            'name' => 'test_from1',
        ], $result);
    }

    public function testGetAttachments()
    {
        $file = file_get_contents(__DIR__ . '/test.png');
        $email = (new Email())
            ->attach($file, 'test.png', 'image/png')
            ->embed(self::sgEncode([
                'personalizations' => [
                    [
                        'to' => [
                            'email' => 'to1@sink.sendgrid.net',
                            'name' => 'test_to1',
                        ],
                    ],
                ],
                'categories' => ['test_category']
            ]), SendgridTransport::REQUEST_BODY_PARAMETER);

        $method = $this->reflection->getMethod('getAttachments');
        $method->setAccessible(true);

        $result = $method->invoke($this->transport, $email);
        unset($result[0]['content_id']);
        self::assertEquals([
            [
                'content' => base64_encode($file),
                'filename' => 'test.png',
                'type' => 'image/png',
                'disposition' => null,
            ]
        ], $result);
    }

    public function testSetParameters()
    {
        $email = (new Email())
            ->embed(self::sgEncode([
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => 'to1@sink.sendgrid.net', 'name' => 'test_to1'],
                            ['email' => 'to2@sink.sendgrid.net', 'name' => 'test_to2'],
                        ],
                        'cc' => [
                            ['email' => 'cc1@sink.sendgrid.net', 'name' => 'test_cc1'],
                            ['email' => 'cc2@sink.sendgrid.net', 'name' => 'test_cc2'],
                        ],
                        'bcc' => [
                            ['email' => 'bcc1@sink.sendgrid.net', 'name' => 'test_bcc1'],
                            ['email' => 'bcc2@sink.sendgrid.net', 'name' => 'test_bcc2'],
                        ],
                    ],
                ],
                'categories' => ['test_category']
            ]), SendgridTransport::REQUEST_BODY_PARAMETER);

        $method = $this->reflection->getMethod('setParameters');
        $method->setAccessible(true);

        $data = [];
        $result = $method->invoke($this->transport, $email, $data);
        unset($result[0]['content_id']);
        self::assertEquals([
            'personalizations' => [
                [
                    'to' => [
                        ['email' => 'to1@sink.sendgrid.net', 'name' => 'test_to1'],
                        ['email' => 'to2@sink.sendgrid.net', 'name' => 'test_to2'],
                    ],
                    'cc' => [
                        ['email' => 'cc1@sink.sendgrid.net', 'name' => 'test_cc1'],
                        ['email' => 'cc2@sink.sendgrid.net', 'name' => 'test_cc2'],
                    ],
                    'bcc' => [
                        ['email' => 'bcc1@sink.sendgrid.net', 'name' => 'test_bcc1'],
                        ['email' => 'bcc2@sink.sendgrid.net', 'name' => 'test_bcc2'],
                    ],
                ],
            ],
            'categories' => ['test_category']
        ], $result);
    }

    public function testSetParameters_with_SMTP_API_NAME()
    {
        $email = (new Email())
            ->embed(self::sgEncode([
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => 'to1@sink.sendgrid.net', 'name' => 'test_to1'],
                            ['email' => 'to2@sink.sendgrid.net', 'name' => 'test_to2'],
                        ],
                        'cc' => [
                            ['email' => 'cc1@sink.sendgrid.net', 'name' => 'test_cc1'],
                            ['email' => 'cc2@sink.sendgrid.net', 'name' => 'test_cc2'],
                        ],
                        'bcc' => [
                            ['email' => 'bcc1@sink.sendgrid.net', 'name' => 'test_bcc1'],
                            ['email' => 'bcc2@sink.sendgrid.net', 'name' => 'test_bcc2'],
                        ],
                    ],
                ],
                'categories' => ['test_category']
            ]), SendgridTransport::SMTP_API_NAME);

        $method = $this->reflection->getMethod('setParameters');
        $method->setAccessible(true);

        $data = [];
        $result = $method->invoke($this->transport, $email, $data);
        unset($result[0]['content_id']);
        self::assertEquals([
            'personalizations' => [
                [
                    'to' => [
                        ['email' => 'to1@sink.sendgrid.net', 'name' => 'test_to1'],
                        ['email' => 'to2@sink.sendgrid.net', 'name' => 'test_to2'],
                    ],
                    'cc' => [
                        ['email' => 'cc1@sink.sendgrid.net', 'name' => 'test_cc1'],
                        ['email' => 'cc2@sink.sendgrid.net', 'name' => 'test_cc2'],
                    ],
                    'bcc' => [
                        ['email' => 'bcc1@sink.sendgrid.net', 'name' => 'test_bcc1'],
                        ['email' => 'bcc2@sink.sendgrid.net', 'name' => 'test_bcc2'],
                    ],
                ],
            ],
            'categories' => ['test_category']
        ], $result);
    }
}