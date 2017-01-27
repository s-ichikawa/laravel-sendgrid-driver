<?php
namespace Sichikawa\LaravelSendgridDriver\Api;


class Attachment
{
    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $filename;

    /**
     * @var string
     */
    public $disposition;

    /**
     * @var string
     */
    public $content_id;

    public function toArray()
    {
        return array_filter(json_decode(json_encode($this), true), 'strlen');
    }
}