<?php
namespace Sichikawa\LaravelSendgridDriver\Api\MailSettings;

class SpamCheck
{
    /**
     * @var bool
     */
    public $enable;

    /**
     * @var int
     */
    public $threshold;

    /**
     * @var string
     */
    public $post_to_url;
}
