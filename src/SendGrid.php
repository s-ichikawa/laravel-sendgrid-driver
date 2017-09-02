<?php

namespace Sichikawa\LaravelSendgridDriver;

use Illuminate\Mail\Mailable;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Swift_Message;

trait SendGrid
{

    /**
     * @param null|array $params
     * @return $this
     */
    public function sendgrid($params)
    {
        if ($this instanceof Mailable && $this->mailDriver() == "sendgrid") {
            $this->withSwiftMessage(function (Swift_Message $message) use ($params) {
                $message->embed(new \Swift_Image($params, SendgridTransport::SMTP_API_NAME));
            });
        }
        return $this;
    }

    /**
     * @return string
     */
    private function mailDriver()
    {
        return function_exists('config') ? config('mail.driver') : env('MAIL_DRIVER');
    }
}
