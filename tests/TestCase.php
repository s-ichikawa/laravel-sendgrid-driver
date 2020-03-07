<?php


class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $api_key;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api_key = env('SENDGRID_API_KEY');
    }
}