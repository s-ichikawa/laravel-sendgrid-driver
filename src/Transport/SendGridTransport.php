<?php
namespace Sichikawa\LaravelSendgridDriver\Transport;

use GuzzleHttp\ClientInterface;
use Illuminate\Mail\Transport\Transport;
use Swift_Attachment;
use Swift_Mime_Message;

class SendgridTransport extends Transport
{
    const MAXIMUM_FILE_SIZE = 7340032;

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

        $data = [
            'from'     => $from,
            'fromname' => isset($fromName) ? $fromName : null,
            'subject'  => $message->getSubject(),
            'html'     => $message->getBody()
        ];
        $this->setTo($data, $message);
        $this->setCc($data, $message);
        $this->setBcc($data, $message);
        $this->setAttachment($data, $message);

        if (version_compare(ClientInterface::VERSION, '6') === 1) {
            $this->options += ['form_params' => $data];
        } else {
            $this->options += ['body' => $data];
        }

        return $this->client->post('https://api.sendgrid.com/api/mail.send.json', $this->options);
    }

    /**
     * @param  $data
     * @param  Swift_Mime_Message $message
     */
    protected function setTo(&$data, Swift_Mime_Message $message)
    {
        if ($from = $message->getTo()) {
            $data['to'] = array_keys($from);
            $data['toname'] = $from;
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
            $data['ccname'] = $cc;
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
            $data['bccname'] = $bcc;
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
}