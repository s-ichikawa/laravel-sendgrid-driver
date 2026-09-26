<?php

declare(strict_types=1);

class TestCase extends PHPUnit\Framework\TestCase
{
    protected const API_KEY = 'SG.test-api-key';

    /**
     * @var string
     */
    protected $api_key = self::API_KEY;
}
