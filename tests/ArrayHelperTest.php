<?php

namespace Accyl\helpers\tests;

use Accyl\helpers\ArrayHelper;

class ArrayHelperTest extends \Codeception\Test\Unit
{
    /**
     * 测试decode方法的默认行为.
     *
     * @param array|\stdClass $actual
     * @param array           $expected
     *
     * @dataProvider decodeDataProvider
     * @group decode
     */
    public function testDecodeByDefaultBehavior($actual, $expected)
    {
        $this->assertEquals($expected, ArrayHelper::decode($actual), '测试失败');
    }

    /**
     * 测试decode方法的自定义参数时的行为.
     *
     * @depends testDecodeByDefaultBehavior
     * @group decode
     */
    public function testDecodeByCustomBehavior()
    {
        // 测试default参数
        $this->assertEquals(['a' => 'n/a', 'b' => '', 'c' => ''], ArrayHelper::decode(['a' => 'n/a', 'b' => '/na', 'c' => ''], '/na'), '测试失败');

        // 测试hideIdentity参数
        $this->assertEquals(['a' => '', 'b' => '/na', 'c' => '', 'mobile' => '18300000000', 'email' => 'e@abc.com'], ArrayHelper::decode(['a' => 'n/a', 'b' => '/na', 'c' => '', 'mobile' => '18300000000', 'email' => 'e@abc.com'], 'n/a', false));

        // 测试identities参数
        $this->assertEquals(['a' => '', 'b' => '/na', 'c' => '', 'mobile' => '183****0000', 'email' => 'e@abc.com'], ArrayHelper::decode(['a' => 'n/a', 'b' => '/na', 'c' => '', 'mobile' => '18300000000', 'email' => 'e@abc.com'], 'n/a', true, ['mobile']));
    }

    /**
     * 对decode方法提供默认行为的测试数据.
     *
     * @return array
     */
    public function decodeDataProvider()
    {
        $stdClass = new \stdClass();
        $stdClass->a = 'n/a';
        $stdClass->b = '/na';
        $stdClass->c = '';

        return [
            '测试默认值转换' => [['a' => 'n/a', 'b' => '/na', 'c' => ''], ['a' => '', 'b' => '/na', 'c' => '']],
            '测试简单类对象及默认值转换' => [$stdClass, ['a' => '', 'b' => '/na', 'c' => '']],
            '测试隐藏邮箱及手机号码-1' => [['a' => 'n/a', 'b' => '/na', 'c' => '', 'mobile' => '18300000000', 'email' => 'e@abc.com'], ['a' => '', 'b' => '/na', 'c' => '', 'mobile' => '183****0000', 'email' => 'e***@abc.com']],
            '测试隐藏邮箱及手机号码-2' => [['a' => 'n/a', 'b' => '/na', 'c' => '', 'mobile' => '18300000000', 'email' => 'eee@abc.com'], ['a' => '', 'b' => '/na', 'c' => '', 'mobile' => '183****0000', 'email' => 'eee***@abc.com']],
        ];
    }

    /**
     * 测试extract方法的默认行为.
     *
     * @param $actual
     * @param $expected
     * @param $columns
     *
     * @throws \Exception
     *
     * @dataProvider extractDataProvider
     * @group extract
     */
    public function testExtractByDefaultBehavior($actual, $expected, $columns)
    {
        $this->assertEquals($expected, ArrayHelper::extract($actual, $columns), '测试失败');
    }

    /**
     * 测试extract方法的异常行为1.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage b
     * @group extract
     */
    public function testExtractByExceptionBehavior1()
    {
        ArrayHelper::extract(['a' => 1], ['b']);
    }

    /**
     * 测试extract方法的异常行为2.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage b.d
     * @group extract
     */
    public function testExtractByExceptionBehavior2()
    {
        ArrayHelper::extract(['a' => 1, 'b' => ['c' => 2]], ['b.d']);
    }

    /**
     * 测试extract方法的异常行为3.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage d
     * @group extract
     */
    public function testExtractByExceptionBehavior3()
    {
        ArrayHelper::extract(['a' => 1, 'b' => ['c' => 2]], ['d.e']);
    }

    /**
     * 测试extract方法的异常行为4.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage d
     * @group extract
     */
    public function testExtractByExceptionBehavior4()
    {
        ArrayHelper::extract(['a' => 1, 'b' => ['c' => 2]], ['d' => ['e']]);
    }

    /**
     * 对extract方法提供默认行为的测试数据.
     *
     * @return array
     */
    public function extractDataProvider()
    {
        return [
            '一维数组提取' => [['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], ['a' => 1, 'c' => 3, 'e' => 5], ['a', 'c', 'e']],
            '二维数组提取-1' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => 5]], ['a' => 1, 'c' => ['d' => 3, 'f' => 5]], ['a', 'c.d', 'c.f']],
            '二维数组提取-2' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => 5]], ['a' => 1, 'c' => ['d' => 3, 'f' => 5]], ['a', 'c' => ['d', 'f']]],
            '三维数组提取-1' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]], 'j' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]]], ['a' => 1, 'c' => ['d' => 3, 'f' => ['i' => 7]]], ['a', 'c.d', 'c.f.i']],
            '三维数组提取-2' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]], 'j' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]]], ['a' => 1, 'c' => ['d' => 3, 'f' => ['i' => 7]]], ['a', 'c' => ['d', 'f' => ['i']]]],
        ];
    }

    /**
     * 测试except方法的默认行为.
     *
     * @param $actual
     * @param $expected
     * @param $columns
     *
     * @throws \Exception
     *
     * @dataProvider exceptDataProvider
     * @group except
     */
    public function testExceptByDefaultBehavior($actual, $expected, $columns)
    {
        $this->assertEquals($expected, ArrayHelper::except($actual, $columns), '测试失败');
    }

    /**
     * 对except方法提供默认行为的测试数据.
     *
     * @return array
     */
    public function exceptDataProvider()
    {
        return [
            '一维数组去除' => [['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], ['a' => 1, 'c' => 3, 'e' => 5], ['b', 'd']],
            '二维数组去除-1' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => 5]], ['a' => 1, 'c' => ['d' => 3, 'f' => 5]], ['b', 'c.e']],
            '二维数组去除-2' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => 5]], ['a' => 1, 'c' => ['d' => 3, 'f' => 5]], ['b', 'c' => ['e']]],
            '三维数组去除-1' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]], 'j' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]]], ['a' => 1, 'c' => ['d' => 3, 'f' => ['i' => 7]]], ['b', 'c.e', 'c.f.g', 'c.f.h', 'j']],
            '三维数组去除-2' => [['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]], 'j' => ['d' => 3, 'e' => 4, 'f' => ['g' => 5, 'h' => 6, 'i' => 7]]], ['a' => 1, 'c' => ['d' => 3, 'f' => ['i' => 7]]], ['b', 'c' => ['e', 'f' => ['g', 'h']], 'j']],
        ];
    }

    /**
     * 测试trim方法的默认行为.
     *
     * @param $actual
     * @param $expected
     * @param $callback
     * @param $chars
     *
     * @throws \Exception
     *
     * @dataProvider trimDataProvider
     * @group trim
     */
    public function testTrimByDefaultBehavior($actual, $expected, $callback, $chars)
    {
        $this->assertEquals($expected, ArrayHelper::trim($actual, $callback, $chars), '测试失败');
    }

    /**
     * 测试trim方法的异常行为1.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage 找不到指定的方法：aaabbc
     * @group trim
     */
    public function testTrimByExceptionBehavior1()
    {
        ArrayHelper::trim(['a'], 'aaabbc');
    }

    /**
     * 测试trim方法的异常行为2.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage 错误的数据格式:object
     * @group trim
     */
    public function testTrimByExceptionBehavior2()
    {
        ArrayHelper::trim([new \Exception()]);
    }

    /**
     * 对trim方法提供默认行为的测试数据.
     *
     * @return array
     */
    public function trimDataProvider()
    {
        return [
            '一维数组-empty-trim' => [[], [], 'trim', " \t\n\r\0\x0B"],
            '一维数组-trim' => [['1 ', ' 2 ', "3\n"], ['1', '2', '3'], 'trim', " \t\n\r\0\x0B"],
            '一维数组-ltrim' => [['1 ', ' 2 ', "3\n"], ['1 ', '2 ', "3\n"], 'ltrim', " \t\n\r\0\x0B"],
            '一维数组-rtrim' => [['1 ', ' 2 ', "3\n"], ['1', ' 2', '3'], 'rtrim', " \t\n\r\0\x0B"],
            '一维数组-trim-spec' => [['1 ', ' 2 ', "3\n"], ['1', '2', "3\n"], 'trim', ' '],
            '一维数组-callback-trim' => [['1 ', ' 2 ', "3\n"], ['1', '2', '3'], function ($data, $chars) {
                return trim($data, $chars);
            }, " \t\n\r\0\x0B"],
        ];
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
