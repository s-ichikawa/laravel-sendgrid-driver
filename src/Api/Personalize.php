<?php
namespace Sichikawa\LaravelSendgridDriver\Api;

use Sichikawa\LaravelSendgridDriver\Api\Email\Bcc;
use Sichikawa\LaravelSendgridDriver\Api\Email\Cc;
use Sichikawa\LaravelSendgridDriver\Api\Email\To;

class Personalize
{
    /**
     * @var To
     */
    public $to;

    /**
     * @var Cc
     */
    public $cc;

    /**
     * @var Bcc
     */
    public $bcc;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var array
     */
    public $headers;

    /**
     * @var array
     */
    public $substitutions;

    /**
     * @var array
     */
    public $custom_args;

    /**
     * @var int
     */
    public $send_at;

    /**
     * @param $email
     * @param null $name
     * @return $this
     */
    public function setTo($email, $name = null)
    {
        $this->to = new To($email, $name);
        return $this;
    }

    /**
     * @param $email
     * @param null $name
     * @return $this
     */
    public function setCc($email, $name = null)
    {
        $this->cc = new Cc($email, $name);
        return $this;
    }

    /**
     * @param $email
     * @param null $name
     * @return $this
     */
    public function setBcc($email, $name = null)
    {
        $this->bcc = new Bcc($email, $name);
        return $this;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addSubstitutions($key, $value)
    {
        $this->substitutions[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addCustomArgs($key, $value)
    {
        $this->custom_args[$key] = $value;
        return $this;
    }

    /**
     * @param int $send_at
     */
    public function setSendAt($send_at)
    {
        $this->send_at = $send_at;
    }

    public function toArray()
    {
        return array_filter(json_decode(json_encode($this), true), function ($val) {
            return !empty($val);
        });
    }
}
