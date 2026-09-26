<?php

use PHPUnit\Framework\Attributes\DataProvider;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class SendGridTest extends \PHPUnit\Framework\TestCase
{
    use SendGrid;

    const PARAMS = [
        'personalizations' => [
            [
                'to' => [
                    'email' => 'foo@sink.sendgrid.net',
                    'name' => 'foo',
                ],
            ],
        ],
    ];
    const STR_PARAMS = '{"personalizations":[{"to":{"email":"foo@sink.sendgrid.net","name":"foo"}}]}';

    public static function providerTestSgEncode(): array
    {
        return [
            'array' => [self::PARAMS, self::STR_PARAMS],
            'string' => [self::STR_PARAMS, self::STR_PARAMS],
        ];
    }

    #[DataProvider('providerTestSgEncode')]
    public function testSgEncode($params, string $expected): void
    {
        $result = self::sgEncode($params);
        $this->assertSame($expected, $result);
    }

    public static function providerTestSgDecode(): array
    {
        return [
            'string' => [self::STR_PARAMS, self::PARAMS],
            'array' => [self::PARAMS, self::PARAMS],
        ];
    }

    #[DataProvider('providerTestSgDecode')]
    public function testSgDecode($str, array $expected): void
    {
        $result = self::sgDecode($str);
        $this->assertSame($expected, $result);
    }
}
