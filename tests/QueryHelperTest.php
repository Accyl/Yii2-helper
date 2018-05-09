<?php

namespace Accyl\helpers\tests;

use Accyl\helpers\QueryHelper;

/**
 * QueryHelper单元测试.
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class QueryHelperTest extends \Codeception\Test\Unit
{
    /**
     * @param $actual
     * @param $expected
     *
     * @dataProvider parseConditionDataProvider
     */
    public function testParseCondition($actual, $expected)
    {
        $this->assertEquals($expected, QueryHelper::parseCondition($actual), '测试失败');
    }

    /**
     * 为parseCondition方法提供测试数据.
     *
     * @return array
     */
    public function parseConditionDataProvider()
    {
        return [
            '基本测试' => [['id' => 123, 'name' => '!keyword'], ['and', ['=', 'id', 123], ['!=', 'name', 'keyword']]],
            '关键词匹配测试' => [['name' => '%%keyword', 'description' => '!%%keyword'], ['and', ['like', 'name', 'keyword'], ['not like', 'description', 'keyword']]],
            '元素匹配测试' => [['id' => '1,2,3,4,5', 'type' => '!2,3'], ['and', ['in', 'id', [1, 2, 3, 4, 5]], ['not in', 'type', [2, 3]]]],
            '范围匹配测试' => [['id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['between', 'id', [1, 5]], ['not between', 'number', [100, 1000]]]],
            '关联匹配测试' => [['relation>id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['between', 'relation.id', [1, 5]], ['not between', 'number', [100, 1000]]]],
            '完整测试' => [['id' => '1@-@5', 'number' => '!100@-@1000', 'name' => '%%keyword', 'description' => '!%%keyword', 'relation>type' => '1,2,3'], ['and', ['between', 'id', [1, 5]], ['not between', 'number', [100, 1000]], ['like', 'name', 'keyword'], ['not like', 'description', 'keyword'], ['in', 'relation.type', [1, 2, 3]]]],
        ];
    }

    public function testParseOrder()
    {
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
