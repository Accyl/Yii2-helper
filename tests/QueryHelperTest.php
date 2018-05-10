<?php

namespace Accyl\helpers\tests;

use Accyl\helpers\QueryHelper;

/**
 * QueryHelper单元测试.
 *
 * @method assertEquals($expected, $actual, $message = '');
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class QueryHelperTest extends \Codeception\Test\Unit
{
    /**
     * 测试parseCondition方法.
     *
     * @param $actual
     * @param $expected
     * @param $allowedColumns
     * @param $allowRelation
     *
     * @dataProvider parseConditionDataProvider
     */
    public function testParseCondition($actual, $expected, $allowedColumns, $allowRelation)
    {
        $this->assertEquals($expected, QueryHelper::parseCondition($actual, $allowedColumns, $allowRelation), '测试失败');
    }

    /**
     * 为parseCondition方法提供测试数据.
     *
     * @return array
     */
    public function parseConditionDataProvider()
    {
        return [
            '基本测试' => [['id' => 123, 'name' => '!keyword'], ['and', ['=', 'id', 123], ['!=', 'name', 'keyword']], ['id' => null, 'name'], true],
            '关键词匹配测试' => [['name' => '%%keyword', 'description' => '!%%keyword'], ['and', ['like', 'name', 'keyword'], ['not like', 'description', 'keyword']], [], true],
            '元素匹配测试' => [['id' => '1,2,3,4,5', 'type' => '!2,3'], ['and', ['in', 'id', [1, 2, 3, 4, 5]], ['not in', 'type', [2, 3]]], null, true],
            '范围匹配测试' => [['id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['between', 'id', [1, 5]], ['not between', 'number', [100, 1000]]], null, true],
            '关联匹配测试-1' => [['relation>id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['between', 'relation.id', [1, 5]], ['not between', 'number', [100, 1000]]], null, true],
            '关联匹配测试-2' => [['relation>id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['not between', 'number', [100, 1000]]], null, false],
            '列匹配测试-1' => [['id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['between', 'id', [1, 5]], ['not between', 'number', [100, 1000]]], ['id', 'number'], true],
            '列匹配测试-2' => [['id' => '1@-@5', 'number' => '!100@-@1000'], ['and', ['not between', 'number', [100, 1000]]], ['number'], true],
            '列匹配测试-3' => [['id' => '1@-@5', 'number' => '!100@-@1000', 'type' => '1@-@10'], ['and', ['not between', 'number', [100, 1000]]], ['id' => null, 'number' => ['between'], 'type' => ['in']], true],
            '无效参数过滤测试' => [['id' => '1@-@5', 'number' => '!100@-@1000', 'type' => '1@-@10', 'sort' => 'id,desc;type;'], ['and', ['not between', 'number', [100, 1000]]], ['id' => null, 'number' => ['between'], 'type' => ['in']], true],
        ];
    }

    /**
     * 测试parseOrder方法.
     *
     * @param $actual
     * @param $expected
     * @param $default
     * @param $allowedColumns
     *
     * @dataProvider parseOrderDataProvider
     */
    public function testParseOrder($actual, $expected, $default, $allowedColumns)
    {
        $this->assertEquals($expected, QueryHelper::parseOrder($actual, $default, $allowedColumns), '测试失败');
    }

    /**
     * 为parseOrder方法提供测试数据.
     *
     * @return array
     */
    public function parseOrderDataProvider()
    {
        return [
            '降序排序测试-1' => [['sort' => 'id,desc'], ['id' => SORT_DESC], ['id' => SORT_DESC], null],
            '降序排序测试-2' => [['sort' => 'id,descending'], ['id' => SORT_DESC], ['id' => SORT_DESC], null],
            '升序排序测试-1' => [['sort' => 'id,asc'], ['id' => SORT_ASC], ['id' => SORT_DESC], null],
            '升序排序测试-2' => [['sort' => 'id,ascending'], ['id' => SORT_ASC], ['id' => SORT_DESC], null],
            '默认值排序测试-1' => [[], ['id' => SORT_DESC], ['id' => SORT_DESC], null],
            '默认值排序测试-2' => [['sort' => ''], ['id' => SORT_ASC, 'type' => SORT_DESC], ['id' => SORT_ASC, 'type' => SORT_DESC], null],
            '组合排序测试-1' => [['sort' => 'id,desc;type'], ['id' => SORT_DESC, 'type' => SORT_ASC], ['id' => SORT_ASC, 'type' => SORT_DESC], null],
            '组合排序测试-2' => [['sort' => 'id,desc;type,ascending'], ['id' => SORT_DESC, 'type' => SORT_ASC], ['id' => SORT_ASC, 'type' => SORT_DESC], null],
            '列过滤排序测试-1' => [['sort' => 'id,desc;type,ascending'], ['id' => SORT_DESC], ['id' => SORT_ASC, 'type' => SORT_DESC], ['id']],
            '列过滤排序测试-2' => [['sort' => 'id;type;time,desc'], ['id' => SORT_DESC, 'type' => SORT_DESC], ['id' => SORT_DESC, 'type' => SORT_DESC], []],
        ];
    }

    protected function _before()
    {
    }

    protected function _after()
    {
    }
}
