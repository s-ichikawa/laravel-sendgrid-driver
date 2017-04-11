<?php
namespace Sichikawa\LaravelSendgridDriver;

use Illuminate\Mail\Mailable;
use Swift_Message;

trait SendGrid
{

    /**
     * @param null|array $params
     * @return $this
     */
    public function sendgrid($params)
    {
        if ($this instanceof Mailable) {
            $this->withSwiftMessage(function (Swift_Message $message) use ($params) {
                $message->embed(\Swift_Image::newInstance($params, 'sendgrid/x-smtpapi'));
            });
        }
        return $this;
    }
}
