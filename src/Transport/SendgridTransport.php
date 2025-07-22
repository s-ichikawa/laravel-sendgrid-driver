<?php

namespace Sichikawa\LaravelSendgridDriver\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Sichikawa\LaravelSendgridDriver\SendGrid;
use Stringable;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;

class SendgridTransport extends AbstractTransport implements Stringable
{
    use SendGrid {
        sgDecode as decode;
    }

    /**
     * https://docs.sendgrid.com/api-reference/mail-send/mail-send
     */
    const BASE_URL = 'https://api.sendgrid.com/v3/mail/send';

    /**
     * @deprecated use REQUEST_BODY_PARAMETER instead
     */
    const SMTP_API_NAME = 'sendgrid/request-body-parameter';
    const REQUEST_BODY_PARAMETER = 'sendgrid/request-body-parameter';

    /**
     * @var Client
     */
    private $client;
    private $attachments;
    private $numberOfRecipients;
    private $apiKey;
    private $endpoint;

    public function __construct(ClientInterface $client, string $api_key, ?string $endpoint = null)
    {
        $this->client = $client;
        $this->apiKey = $api_key;
        $this->endpoint = $endpoint ?? self::BASE_URL;
        $this->attachments = [];

        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $data = [
            'personalizations' => $this->getPersonalizations($email),
            'from' => $this->getFrom($email),
            'subject' => $email->getSubject(),
        ];

        if ($contents = $this->getContents($email)) {
            $data['content'] = $contents;
        }

        if ($reply_to = $this->getReplyTo($email)) {
            $data['reply_to'] = $reply_to;
        }

        $attachments = $this->getAttachments($email);
        if (count($attachments) > 0) {
            $data['attachments'] = $attachments;
        }

        $data = $this->setParameters($email, $data);

        $payload = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];

        $response = $this->post($payload);

        $messageId = $response->getHeaderLine('X-Message-Id');

        $message->setMessageId($messageId);

        $message->getOriginalMessage()
            ->getHeaders()
            ->addTextHeader('X-Sendgrid-Message-Id', $messageId);
    }

    /**
     * @param Email $email
     * @return array[]
     */
    private function getPersonalizations(Email $email): array
    {
        $personalization['to'] = $this->setAddress($email->getTo());

        if (count($email->getCc()) > 0) {
            $personalization['cc'] = $this->setAddress($email->getCc());

        }

        if (count($email->getBcc()) > 0) {
            $personalization['bcc'] = $this->setAddress($email->getBcc());

        }

        return [$personalization];
    }

    /**
     * @param Address[] $addresses
     * @return array
     */
    private function setAddress(array $addresses): array
    {
        $recipients = [];
        foreach ($addresses as $address) {
            $recipient = ['email' => $address->getAddress()];
            if ($address->getName() !== '') {
                $recipient['name'] = $address->getName();
            }
            $recipients[] = $recipient;
        }
        return $recipients;
    }

    /**
     * @param Email $email
     * @return array
     */
    private function getFrom(Email $email): array
    {
        if (count($email->getFrom()) > 0) {
            foreach ($email->getFrom() as $from) {
                return ['email' => $from->getAddress(), 'name' => $from->getName()];
            }
        }
        return [];
    }

    /**
     * @param Email $email
     * @return array
     */
    private function getContents(Email $email): array
    {
        $contents = [];
        if (!is_null($email->getTextBody())) {
            $contents[] = [
                'type' => 'text/plain',
                'value' => $email->getTextBody(),
            ];
        }

        if (!is_null($email->getHtmlBody())) {
            $contents[] = [
                'type' => 'text/html',
                'value' => $email->getHtmlBody(),
            ];
        }

        return $contents;
    }

    /**
     * @param Email $email
     * @return array|null
     */
    private function getReplyTo(Email $email): ?array
    {
        if (count($email->getReplyTo()) > 0) {
            $replyTo = $email->getReplyTo()[0];
            return [
                'email' => $replyTo->getAddress(),
                'name' => $replyTo->getName(),
            ];
        }
        return null;
    }

    /**
     * @param Email $email
     * @return array
     */
    private function getAttachments(Email $email): array
    {
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $filename = $this->getAttachmentName($attachment);
            if ($filename === self::REQUEST_BODY_PARAMETER) {
                continue;
            }

            $attachments[] = [
                'content' => base64_encode($attachment->getBody()),
                'filename' => $this->getAttachmentName($attachment),
                'type' => $this->getAttachmentContentType($attachment),
                'disposition' => $attachment->getDisposition(),
                'content_id' => $this->getAttachmentName($attachment),
            ];
        }
        return $attachments;
    }

    private function getAttachmentName(DataPart $dataPart): string
    {
        return $dataPart->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename');
    }

    private function getAttachmentContentType(Datapart $dataPart): string
    {
        return $dataPart->getMediaType() . '/' . $dataPart->getMediaSubtype();
    }

    /**
     * @param Email $email
     * @param array $data
     * @return array
     */
    private function setParameters(Email $email, array $data): array
    {
        $smtp_api = [];
        foreach ($email->getAttachments() as $attachment) {
            $name = $attachment->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename');
            if ($name === self::REQUEST_BODY_PARAMETER) {
                $smtp_api = self::decode($attachment->getBody());
            }
        }

        if (count($smtp_api) < 1) {
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
            }
            Arr::set($data, $key, $val);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param array $personalizations
     * @return void
     */
    private function setPersonalizations(array &$data, array $personalizations): void
    {
        foreach ($personalizations as $index => $params) {
            foreach ($params as $key => $val) {
                if (in_array($key, ['to', 'cc', 'bcc'])) {
                    Arr::set($data, 'personalizations.' . $index . '.' . $key, $val);
                    ++$this->numberOfRecipients;
                } else {
                    Arr::set($data, 'personalizations.' . $index . '.' . $key, $val);
                }
            }
        }
    }

    /**
     * @param array $payload
     * @return ResponseInterface
     * @throws ClientException
     */
    protected function post($payload)
    {
        return $this->client->request('POST', $this->endpoint, $payload);
    }

    public function __toString(): string
    {
        return 'sendgrid';
    }
}
