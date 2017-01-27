<?php
namespace Sichikawa\LaravelSendgridDriver\Api\TrackingSettings;

class GAnalytics
{
    /**
     * @var bool
     */
    public $enable;

    /**
     * @var string
     */
    public $utm_source;

    /**
     * @var string
     */
    public $utm_medium;

    /**
     * @var string
     */
    public $utm_term;

    /**
     * @var string
     */
    public $utm_content;

    /**
     * @var string
     */
    public $utm_campaign;
}