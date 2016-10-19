<?php
namespace Sichikawa\LaravelSendgridDriver\Transport;

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Swift_Attachment;
use Swift_Image;
use Swift_Mime_Message;
use Swift_MimePart;

class SendgridTransport extends Transport
{
    const MAXIMUM_FILE_SIZE = 7340032;
    const SMTP_API_NAME = 'sendgrid/x-smtpapi';

    private $client;
    private $options;

    public function __construct(ClientInterface $client, $api_key)
    {
        $this->client = $client;
        $this->options = [
            'headers' => ['Authorization' => 'Bearer ' . $api_key]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        list($from, $fromName) = $this->getFromAddresses($message);
        $payload = $this->options;

        $data = [
            'from'     => $from,
            'fromname' => isset($fromName) ? $fromName : null,
            'subject'  => $message->getSubject(),
            'html'     => $message->getBody()
        ];
        $this->setTo($data, $message);
        $this->setCc($data, $message);
        $this->setBcc($data, $message);
        $this->setReplyTo($data, $message);
        $this->setText($data, $message);
        $this->setAttachment($data, $message);
        $this->setSmtpApi($data, $message);

        if (version_compare(ClientInterface::VERSION, '6') === 1) {
            $payload += ['form_params' => $data];
        } else {
            $payload += ['body' => $data];
        }

        return $this->client->post('https://api.sendgrid.com/api/mail.send.json', $payload);
    }

    /**
     * @param  $data
     * @param  Swift_Mime_Message $message
     */
    protected function setTo(&$data, Swift_Mime_Message $message)
    {
        if ($from = $message->getTo()) {
            $data['to'] = array_keys($from);
            $data['toname'] = array_values($from);
        }
    }

    /**
     * @param $data
     * @param Swift_Mime_Message $message
     */
    protected function setCc(&$data, Swift_Mime_Message $message)
    {
        if ($cc = $message->getCc()) {
            $data['cc'] = array_keys($cc);
            $data['ccname'] = array_values($cc);
        }
    }

    /**
     * @param $data
     * @param Swift_Mime_Message $message
     */
    protected function setBcc(&$data, Swift_Mime_Message $message)
    {
        if ($bcc = $message->getBcc()) {
            $data['bcc'] = array_keys($bcc);
            $data['bccname'] = array_values($bcc);
        }
    }

    /**
     * @param $data
     * @param Swift_Mime_Message $message
     */
    protected function setReplyTo(&$data, Swift_Mime_Message $message)
    {
        if ($replyTo = $message->getReplyTo()) {
            $data['replyto'] = key($replyTo);
        }
    }

    /**
     * Get From Addresses.
     *
     * @param Swift_Mime_Message $message
     * @return array
     */
    protected function getFromAddresses(Swift_Mime_Message $message)
    {
        if ($message->getFrom()) {
            foreach ($message->getFrom() as $address => $name) {
                return [$address, $name];
            }
        }
        return [];
    }

    /**
     * Set text contents.
     *
     * @param $data
     * @param Swift_Mime_Message $message
     */
    protected function setText(&$data, Swift_Mime_Message $message)
    {
        foreach ($message->getChildren() as $attachment) {
            if (!$attachment instanceof Swift_MimePart) {
                continue;
            }
            $data['text'] = $attachment->getBody();
        }
    }

    /**
     * Set Attachment Files.
     *
     * @param $data
     * @param Swift_Mime_Message $message
     */
    protected function setAttachment(&$data, Swift_Mime_Message $message)
    {
        foreach ($message->getChildren() as $attachment) {
            if (!$attachment instanceof Swift_Attachment || !strlen($attachment->getBody()) > self::MAXIMUM_FILE_SIZE) {
                continue;
            }
            $handler = tmpfile();
            fwrite($handler, $attachment->getBody());
            $data['files[' . $attachment->getFilename() . ']'] = $handler;
        }
    }

    /**
     * Set Sendgrid SMTP API
     *
     * @param $data
     * @param Swift_Mime_Message $message
     */
    protected function setSmtpApi(&$data, Swift_Mime_Message $message)
    {
        foreach ($message->getChildren() as $attachment) {
            if (!$attachment instanceof Swift_Image
                || !in_array(self::SMTP_API_NAME, [$attachment->getFilename(), $attachment->getContentType()])
            ) {
                continue;
            }
            $data['x-smtpapi'] = json_encode($attachment->getBody());
        }
    }
}
