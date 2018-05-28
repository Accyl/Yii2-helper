<?php

namespace lunacy\helpers\tests;

use lunacy\helpers\StringHelper;

/**
 * StringHelper元测试类.
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class StringHelperTest extends \Codeception\Test\Unit
{
    /**
     * 对getPassword方法进行测试.
     */
    public function testGetPassword()
    {
        $this->assertEquals(md5('abc'), StringHelper::getPassword('abc'), '测试失败');

        $this->assertEquals(md5('abc'), StringHelper::getPassword(md5('abc')), '测试失败');
    }

    /**
     * 测试generateRandomString方法的默认行为.
     *
     * @throws \Exception
     */
    public function testGenerateRandomStringByDefaultBehavior()
    {
        $this->assertEquals(8, mb_strlen(StringHelper::generateRandomString()), '测试失败：默认长度应该为8');

        $this->assertEquals(10, mb_strlen(StringHelper::generateRandomString(10)), '测试失败：总长度应该为10');

        $this->assertEquals(10, mb_strlen(StringHelper::generateRandomString(10, 'prefix_')), '测试失败：总长度应该为10');

        $this->assertTrue(0 === strncmp('prefix_', StringHelper::generateRandomString(3, 'prefix_'), 7), '测试失败：应该以前缀开头');

        $this->assertEquals(7, mb_strlen(StringHelper::generateRandomString(7, '前缀_')), '测试失败：总长度应该为7');

        $this->assertEquals('前缀_', StringHelper::generateRandomString(3, '前缀_'), '测试失败：应该只返回前缀');

        $this->assertRegExp('/^[噫唏嘘]*$/', StringHelper::generateRandomString(10, '', '噫唏嘘'), '测试失败：应该只包含指定的随机因子');
    }

    /**
     * 测试generateRandomString方法的异常行为.
     *
     * @throws \Exception
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage 随机因子不能为空
     */
    public function testGenerateRandomStringByExceptionBehavior()
    {
        StringHelper::generateRandomString(8, '', '');
    }

    /**
     * 测试generateSimpleRandomString方法的默认行为.
     */
    public function testGenerateSimpleRandomStringByDefaultBehavior()
    {
        $this->assertEquals(8, mb_strlen(StringHelper::generateSimpleRandomString()), '测试失败：默认长度应该为8');

        $this->assertEquals(10, mb_strlen(StringHelper::generateSimpleRandomString(10)), '测试失败：总长度应该为10');

        $this->assertEquals(62, mb_strlen(StringHelper::generateSimpleRandomString(80)), '测试失败：总长度应该为62');

        $this->assertEquals(17, mb_strlen(StringHelper::generateSimpleRandomString(10, 'prefix_')), '测试失败：总长度应该为17');

        $this->assertTrue(0 === strncmp('prefix_', StringHelper::generateSimpleRandomString(3, 'prefix_'), 7), '测试失败：应该以前缀开头');
    }

    /**
     * 测试generateUniqueId方法.
     */
    public function testGenerateUniqueId()
    {
        $except = [];

        for ($i = 0; $i < 100000; ++$i) {
            $uniqueId = StringHelper::generateUniqueId();

            $this->assertFalse(\in_array($uniqueId, $except, true), '唯一ID重复');
            $except[] = $uniqueId;
        }
    }

    /**
     * 测试hideIdentity方法.
     */
    public function testHideIdentity()
    {
        $this->assertEquals('183****0000', StringHelper::hideIdentity('18300000000'), '测试失败');

        $this->assertEquals('e***@abc.com', StringHelper::hideIdentity('e@abc.com'), '测试失败');

        $this->assertEquals('ema***@abc.com', StringHelper::hideIdentity('email@abc.com'), '测试失败');
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
