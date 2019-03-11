<?php
/**
 * Created by PhpStorm.
 * User: ichikawashingo
 * Date: 2019/02/24
 * Time: 11:21
 */


class SendGridTest extends PHPUnit_Framework_TestCase
{
    use \Sichikawa\LaravelSendgridDriver\SendGrid;

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

    public function providerTestSgEncode()
    {
        return [
            [self::PARAMS, self::STR_PARAMS],
            [self::STR_PARAMS, self::STR_PARAMS],
        ];
    }

    /**
     * @param $params
     * @param $expected
     * @dataProvider providerTestSgEncode
     */
    public function testSgEncode($params, $expected)
    {
        $result = self::sgEncode($params);
        $this->assertSame($expected, $result);
    }

    public function providerTestSgDecode()
    {
        return [
            [self::STR_PARAMS, self::PARAMS],
            [self::PARAMS, self::PARAMS],
        ];
    }

    /**
     * @param $str
     * @param $expected
     * @dataProvider providerTestSgDecode
     */
    public function testSgDecode($str, $expected)
    {
        $result = self::sgDecode($str);
        $this->assertSame($expected, $result);
    }
}
