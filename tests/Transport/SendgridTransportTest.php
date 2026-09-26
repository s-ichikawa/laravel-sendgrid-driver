<?php

namespace Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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

    /** @var \ReflectionClass<SendgridTransport> */
    private \ReflectionClass $reflection;

    private MockHandler $mockHandler;

    /** @var array<int, array{request: RequestInterface, response: ?ResponseInterface, error: mixed, options: array<mixed>}> */
    private array $history = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHandler = new MockHandler;
        $stack = HandlerStack::create($this->mockHandler);
        $stack->push(Middleware::history($this->history));
        $client = new Client(['handler' => $stack]);
        $this->transport = new SendgridTransport($client, $this->api_key);
        $this->reflection = new \ReflectionClass($this->transport);
    }

    public function testGetPersonalizations(): void
    {
        $email = (new Email)
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
            ],
        ], $result);
    }

    public function testGetFrom(): void
    {
        $email = (new Email)
            ->from(
                (new Address('from1@sink.sendgrid.net', 'test_from1')),
            );

        $method = $this->reflection->getMethod('getFrom');

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            'email' => 'from1@sink.sendgrid.net',
            'name' => 'test_from1',
        ], $result);
    }

    public function testGetContent(): void
    {
        $email = (new Email)
            ->text('test body')
            ->html('<body>test body</body>');

        $method = $this->reflection->getMethod('getContents');

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            [
                'type' => 'text/plain',
                'value' => 'test body',
            ],
            [
                'type' => 'text/html',
                'value' => '<body>test body</body>',
            ],
        ], $result);
    }

    public function testXMessageID(): void
    {
        $messageId = Str::random(32);
        $this->mockHandler->append(new Response(202, ['X-Message-Id' => $messageId]));

        $email = (new Email)
            ->subject('test subject')
            ->text('test body')
            ->html('<body>test body</body>')
            ->to(new Address('to@sink.sendgrid.net', 'test_to'))
            ->from(new Address('from@sink.sendgrid.net', 'test_from'));

        $send = new SentMessage($email, Envelope::create($email));

        $method = $this->reflection->getMethod('doSend');
        $method->invoke($this->transport, $send);

        self::assertSame($messageId, $send->getMessageId());
        self::assertSame($messageId, $email->getHeaders()->get('X-Sendgrid-Message-Id')->getBodyAsString());

        self::assertCount(1, $this->history);
        $request = $this->history[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame(SendgridTransport::BASE_URL, (string) $request->getUri());
        self::assertSame('Bearer '.self::API_KEY, $request->getHeaderLine('Authorization'));
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $body = json_decode((string) $request->getBody(), true);
        self::assertSame('test subject', $body['subject']);
        self::assertSame(['email' => 'from@sink.sendgrid.net', 'name' => 'test_from'], $body['from']);
        self::assertSame([['email' => 'to@sink.sendgrid.net', 'name' => 'test_to']], $body['personalizations'][0]['to']);
        self::assertSame([
            ['type' => 'text/plain', 'value' => 'test body'],
            ['type' => 'text/html', 'value' => '<body>test body</body>'],
        ], $body['content']);
    }

    public function testGetReplyTo(): void
    {
        $email = (new Email)
            ->replyTo((new Address('from1@sink.sendgrid.net', 'test_from1')));

        $method = $this->reflection->getMethod('getReplyTo');

        $result = $method->invoke($this->transport, $email);
        self::assertEquals([
            'email' => 'from1@sink.sendgrid.net',
            'name' => 'test_from1',
        ], $result);
    }

    public function testGetAttachments(): void
    {
        $file = file_get_contents(__DIR__.'/test.png');
        $email = (new Email)
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
                'categories' => ['test_category'],
            ]), SendgridTransport::REQUEST_BODY_PARAMETER);

        $method = $this->reflection->getMethod('getAttachments');

        $result = $method->invoke($this->transport, $email);
        unset($result[0]['content_id']);
        self::assertEquals([
            [
                'content' => base64_encode($file),
                'filename' => 'test.png',
                'type' => 'image/png',
                'disposition' => 'attachment',
            ],
        ], $result);
    }

    public function testSetParameters(): void
    {
        $email = (new Email)
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
                'categories' => ['test_category'],
            ]), SendgridTransport::REQUEST_BODY_PARAMETER);

        $method = $this->reflection->getMethod('setParameters');

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
            'categories' => ['test_category'],
        ], $result);
    }

    public function testSetParameters_with_SMTP_API_NAME(): void
    {
        $email = (new Email)
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
                'categories' => ['test_category'],
            ]), SendgridTransport::SMTP_API_NAME);

        $method = $this->reflection->getMethod('setParameters');

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
            'categories' => ['test_category'],
        ], $result);
    }
}
