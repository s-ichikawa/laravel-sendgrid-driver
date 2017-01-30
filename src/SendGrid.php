<?php
namespace Sichikawa\LaravelSendgridDriver;

use Illuminate\Mail\Mailable;
use Sichikawa\SendgridApiBuilder\SendGridApi;
use Swift_Message;

trait SendGrid
{
    use SendGridApi;

    /**
     * @param null|array $params
     * @return $this
     */
    public function sendgrid($params = null)
    {
        $this->sg_params = $params ?: $this->sg_params;
        if ($this instanceof Mailable) {
            $this->withSwiftMessage(function (Swift_Message $message) {
                $message->embed(\Swift_Image::newInstance($this->sg_params, 'sendgrid/x-smtpapi'));
            });
        }
        return $this;
    }
}
