<?php
namespace Sichikawa\LaravelSendgridDriver\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Arr;
use Sichikawa\LaravelSendgridDriver\SendGrid;
use Swift_Attachment;
use Swift_Image;
use Swift_Mime_SimpleMessage;
use Swift_MimePart;

class SendgridTransport extends Transport
{
    use SendGrid {
        sgDecode as decode;
    }

    const SMTP_API_NAME = 'sendgrid/x-smtpapi';
    const BASE_URL = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * @var Client
     */
    private $client;
    private $attachments;
    private $numberOfRecipients;
    private $apiKey;
    private $endpoint;

    public function __construct(ClientInterface $client, $api_key, $endpoint = null)
    {
        $this->client = $client;
        $this->apiKey = $api_key;
        $this->endpoint = isset($endpoint) ? $endpoint : self::BASE_URL;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $data = [
            'personalizations' => $this->getPersonalizations($message),
            'from'             => $this->getFrom($message),
            'subject'          => $message->getSubject(),
        ];

        if ($contents = $this->getContents($message)) {
            $data['content'] = $contents;
        }

        if ($reply_to = $this->getReplyTo($message)) {
            $data['reply_to'] = $reply_to;
        }

        $attachments = $this->getAttachments($message);
        if (count($attachments) > 0) {
            $data['attachments'] = $attachments;
        }

        $data = $this->setParameters($message, $data);

        $payload = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];

        $response = $this->post($payload);

        if (method_exists($response, 'getHeaderLine')) {
            $message->getHeaders()->addTextHeader('X-Message-Id', $response->getHeaderLine('X-Message-Id'));
        }

        if (is_callable([$this, "sendPerformed"])) {
            $this->sendPerformed($message);
        }

        if (is_callable([$this, "numberOfRecipients"])) {
            return $this->numberOfRecipients ?: $this->numberOfRecipients($message);
        }
        return $response;
    }

    /**
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    private function getPersonalizations(Swift_Mime_SimpleMessage $message)
    {
        $setter = function (array $addresses) {
            $recipients = [];
            foreach ($addresses as $email => $name) {
                $address = [];
                $address['email'] = $email;
                if ($name) {
                    $address['name'] = $name;
                }
                $recipients[] = $address;
            }
            return $recipients;
        };

        $personalization['to'] = $setter($message->getTo());

        if ($cc = $message->getCc()) {
            $personalization['cc'] = $setter($cc);
        }

        if ($bcc = $message->getBcc()) {
            $personalization['bcc'] = $setter($bcc);
        }

        return [$personalization];
    }

    /**
     * Get From Addresses.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    private function getFrom(Swift_Mime_SimpleMessage $message)
    {
        if ($message->getFrom()) {
            foreach ($message->getFrom() as $email => $name) {
                return ['email' => $email, 'name' => $name];
            }
        }
        return [];
    }

    /**
     * Get ReplyTo Addresses.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    private function getReplyTo(Swift_Mime_SimpleMessage $message)
    {
        if ($message->getReplyTo()) {
            foreach ($message->getReplyTo() as $email => $name) {
                return ['email' => $email, 'name' => $name];
            }
        }
        return null;
    }

    /**
     * Get contents.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    private function getContents(Swift_Mime_SimpleMessage $message)
    {
        $contentType = $message->getContentType();
        switch ($contentType) {
            case 'text/plain':
                return [
                    [
                        'type'  => 'text/plain',
                        'value' => $message->getBody(),

                    ],
                ];
            case 'text/html':
                return [
                    [
                        'type'  => 'text/html',
                        'value' => $message->getBody(),
                    ],
                ];
        }

        // Following RFC 1341, text/html after text/plain in multipart
        $content = [];
        foreach ($message->getChildren() as $child) {
            if ($child instanceof Swift_MimePart && $child->getContentType() === 'text/plain') {
                $content[] = [
                    'type'  => 'text/plain',
                    'value' => $child->getBody(),
                ];
            }
        }

        if (is_null($message->getBody())) {
            return null;
        }

        $content[] = [
            'type'  => 'text/html',
            'value' => $message->getBody(),
        ];
        return $content;
    }

    /**
     * @param Swift_Mime_SimpleMessage $message
     * @return array
     */
    private function getAttachments(Swift_Mime_SimpleMessage $message)
    {
        $attachments = [];
        foreach ($message->getChildren() as $attachment) {
            if ((!$attachment instanceof Swift_Attachment && !$attachment instanceof Swift_Image)
                || $attachment->getFilename() === self::SMTP_API_NAME
            ) {
                continue;
            }
            $attachments[] = [
                'content'     => base64_encode($attachment->getBody()),
                'filename'    => $attachment->getFilename(),
                'type'        => $attachment->getContentType(),
                'disposition' => $attachment->getDisposition(),
                'content_id'  => $attachment->getId(),
            ];
        }
        return $this->attachments = $attachments;
    }

    /**
     * Set Request Body Parameters
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param array $data
     * @return array
     * @throws \Exception
     */
    protected function setParameters(Swift_Mime_SimpleMessage $message, $data)
    {
        $this->numberOfRecipients = 0;

        $smtp_api = [];
        foreach ($message->getChildren() as $attachment) {
            if (!$attachment instanceof Swift_Image
                || !in_array(self::SMTP_API_NAME, [$attachment->getFilename(), $attachment->getContentType()])
            ) {
                continue;
            }
            $smtp_api = self::decode($attachment->getBody());
        }

        if (!is_array($smtp_api)) {
            return $data;
        }

        foreach ($smtp_api as $key => $val) {

            switch ($key) {

                case 'api_key':
                    $this->apiKey = $val;
                    continue 2;

                case 'personalizations':
                    $this->setPersonalizations($data, $val);
                    continue 2;

                case 'attachments':
                    $val = array_merge($this->attachments, $val);
                    break;

                case 'unique_args':
                    throw new \Exception('Sendgrid v3 now uses custom_args instead of unique_args');

                case 'custom_args':
                    foreach ($val as $name => $value) {
                        if (!is_string($value)) {
                            throw new \Exception('Sendgrid v3 custom arguments have to be a string.');
                        }
                    }
                    break;

            }

            Arr::set($data, $key, $val);
        }
        return $data;
    }

    private function setPersonalizations(&$data, $personalizations)
    {
        foreach ($personalizations as $index => $params) {
            foreach ($params as $key => $val) {
                if (in_array($key, ['to', 'cc', 'bcc'])) {
                    Arr::set($data, 'personalizations.' . $index . '.' . $key, [$val]);
                    ++$this->numberOfRecipients;
                } else {
                    Arr::set($data, 'personalizations.' . $index . '.' . $key, $val);
                }
            }
        }
    }

    /**
     * @param $payload
     * @return Response
     */
    private function post($payload)
    {
        return $this->client->post($this->endpoint, $payload);
    }
}
