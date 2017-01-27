<?php
namespace Sichikawa\LaravelSendgridDriver;

use Illuminate\Mail\Mailable;
use Sichikawa\LaravelSendgridDriver\Api\Asm;
use Sichikawa\LaravelSendgridDriver\Api\Attachment;
use Sichikawa\LaravelSendgridDriver\Api\MailSettings;
use Sichikawa\LaravelSendgridDriver\Api\Personalize;
use Sichikawa\LaravelSendgridDriver\Api\TrackingSettings;
use Swift_Message;

trait SendGrid
{
    /**
     * @var array
     */
    private $sg_params = [];

    /**
     * @return array
     */
    public function getSgParams()
    {
        return $this->sg_params;
    }

    /**
     * @param array $sg_params
     */
    public function setSgParams($sg_params)
    {
        $this->sg_params = $sg_params;
    }

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

    /**
     * @param array $personalizations
     * @return $this
     */
    public function setPersonalizations($personalizations)
    {
        $this->sg_params['personalizations'] = $personalizations;
        return $this;
    }

    /**
     * @param Personalize $personalize
     * @return $this
     */
    public function addPersonalizations(Personalize $personalize)
    {
        $this->sg_params['personalizations'][] = $personalize->toArray();
        return $this;
    }

    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->sg_params['attachments'] = $attachments;
        return $this;
    }

    /**
     * @param Attachment $attachment
     * @return $this
     */
    public function addAttachments(Attachment $attachment)
    {
        $this->sg_params['attachments'][] = $attachment->toArray();
        return $this;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setTemplateId($id)
    {
        $this->sg_params['template_id'] = $id;
        return $this;
    }

    /**
     * @param array $section
     * @return $this
     */
    public function setSection($section)
    {
        $this->sg_params['section'] = $section;
        return $this;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->sg_params['headers'] = $headers;
        return $this;
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function setCategories($categories)
    {
        $this->sg_params['categories'] = $categories;
        return $this;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function addCategories($category)
    {
        $this->sg_params['categories'][] = $category;
        return $this;
    }

    /**
     * @param array $custom_args
     * @return $this
     */
    public function setCustomArgs($custom_args)
    {
        $this->sg_params['custom_args'] = $custom_args;
        return $this;
    }

    /**
     * @param string $key
     * @param string $val
     * @return $this
     */
    public function addCustomArgs($key, $val)
    {
        $this->sg_params['custom_args'][$key] = $val;
        return $this;
    }

    /**
     * @param int $send_at
     * @return $this
     */
    public function setSendAt($send_at)
    {
        $this->sg_params['send_at'] = $send_at;
        return $this;
    }

    /**
     * @param string $batch_id
     * @return $this
     */
    public function setBatchId($batch_id)
    {
        $this->sg_params['batch_id'] = $batch_id;
        return $this;
    }

    /**
     * @param Asm $asm
     * @return $this
     */
    public function setAsm(Asm $asm)
    {
        $this->sg_params['asm'] = $asm->toArray();
        return $this;
    }

    /**
     * @param string $ip_pool_name
     * @return $this
     */
    public function setIpPoolName($ip_pool_name)
    {
        $this->sg_params['ip_pool_name'] = $ip_pool_name;
        return $this;
    }


    /**
     * @param MailSettings $mailSetting
     * @return $this
     */
    public function setMailSettings(MailSettings $mailSetting)
    {
        $this->sg_params['mail_settings'] = $mailSetting->toArray();
        return $this;
    }

    /**
     * @param TrackingSettings $trackingSettings
     * @return $this
     */
    public function setTrackingSettings(TrackingSettings $trackingSettings)
    {
        $this->sg_params['tracking_settings'] = $trackingSettings->toArray();
        return $this;
    }
}