<?php

namespace Sichikawa\LaravelSendgridDriver;

use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Sichikawa\LaravelSendgridDriver\Transport\SendgridTransport;
use Symfony\Component\Mime\Email;

trait SendGrid
{

    /**
     * @param null|array $params
     * @return $this
     */
    public function sendgrid($params)
    {
        $isValidInstance = $this instanceof Mailable || $this instanceof MailMessage;

        if ($isValidInstance && $this->mailDriver() == "sendgrid") {
            $this->withSymfonyMessage(function (Email $email) use ($params) {
                $email->embed(static::sgEncode($params), SendgridTransport::REQUEST_BODY_PARAMETER);
            });
        }
        return $this;
    }

    /**
     * @return string
     */
    private function mailDriver()
    {
        return function_exists('config') ? config('mail.default', config('mail.driver')) : env('MAIL_MAILER', env('MAIL_DRIVER'));
    }

    /**
     * @param array $params
     * @return string
     */
    public static function sgEncode($params)
    {
        if (is_string($params)) {
            return $params;
        }
        return json_encode($params);
    }

    /**
     * @param string $strParams
     * @return array
     */
    public static function sgDecode($strParams)
    {
        if (!is_string($strParams)) {
            return (array)$strParams;
        }
        $params = json_decode($strParams, true);
        return is_array($params) ? $params : [];
    }

}
