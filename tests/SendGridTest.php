<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sichikawa\LaravelSendgridDriver\SendGrid;

class SendGridTest extends TestCase
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

    /**
     * @return array<string, array{array<string, mixed>|string, string}>
     */
    public static function providerTestSgEncode(): array
    {
        return [
            'array' => [self::PARAMS, self::STR_PARAMS],
            'string' => [self::STR_PARAMS, self::STR_PARAMS],
        ];
    }

    /**
     * @param  array<string, mixed>|string  $params
     */
    #[DataProvider('providerTestSgEncode')]
    public function testSgEncode(array|string $params, string $expected): void
    {
        $result = self::sgEncode($params);
        self::assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string|array<string, mixed>, array<string, mixed>}>
     */
    public static function providerTestSgDecode(): array
    {
        return [
            'string' => [self::STR_PARAMS, self::PARAMS],
            'array' => [self::PARAMS, self::PARAMS],
        ];
    }

    /**
     * @param  string|array<string, mixed>  $str
     * @param  array<string, mixed>  $expected
     */
    #[DataProvider('providerTestSgDecode')]
    public function testSgDecode(string|array $str, array $expected): void
    {
        $result = self::sgDecode($str);
        self::assertSame($expected, $result);
    }
}
