<?php
namespace Sichikawa\LaravelSendgridDriver\Api;


use Sichikawa\LaravelSendgridDriver\Api\MailSettings\Bcc;
use Sichikawa\LaravelSendgridDriver\Api\MailSettings\BypassListManagement;
use Sichikawa\LaravelSendgridDriver\Api\MailSettings\Footer;
use Sichikawa\LaravelSendgridDriver\Api\MailSettings\SandboxMode;
use Sichikawa\LaravelSendgridDriver\Api\MailSettings\SpamCheck;

class MailSettings
{
    /**
     * @var Bcc
     */
    public $bcc;

    /**
     * @var BypassListManagement
     */
    public $bypass_list_management;

    /**
     * @var Footer
     */
    public $footer;

    /**
     * @var SandboxMode
     */
    public $sandbox_mode;

    /**
     * @var SpamCheck
     */
    public $spam_check;

    /**
     * @param Bcc $bcc
     * @return MailSettings
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @param BypassListManagement $bypass_list_management
     * @return MailSettings
     */
    public function setBypassListManagement($bypass_list_management)
    {
        $this->bypass_list_management = $bypass_list_management;
        return $this;
    }

    /**
     * @param Footer $footer
     * @return MailSettings
     */
    public function setFooter($footer)
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * @param SandboxMode $sandbox_mode
     * @return MailSettings
     */
    public function setSandboxMode($sandbox_mode)
    {
        $this->sandbox_mode = $sandbox_mode;
        return $this;
    }

    /**
     * @param SpamCheck $spam_check
     * @return MailSettings
     */
    public function setSpamCheck($spam_check)
    {
        $this->spam_check = $spam_check;
        return $this;
    }

    public function toArray()
    {
        return array_filter(json_decode(json_encode($this), true), function ($val) {
            return !empty($val);
        });
    }
}
