<?php

namespace Accyl\helpers;

/**
 * 用户解析请求参数的小助手.
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class QueryHelper
{
    /**
     * 解析请求参数中的查询条件.
     *
     * @example
     * 1. between: Url xxx.xx/api/resource?id=1@-@10 // to where ['between', 'id', [1, 10]]
     * 2. not between: Url xxx.xx/api/resource?id=!1@-@10 // to where ['not between', 'id', [1, 10]]
     * 3. in: Url xxx.xx/api/resource?id=1,2,3,4,5 // to where ['in', 'id', [1, 2, 3, 4, 5]
     * 4. not in: Url xxx.xx/api/resource?id=!1,2,3,4,5 //to where ['not in', 'id', [1, 2, 3, 4, 5]]
     * 5. like: Url xxx.xx/api/resource?name=%%abc // to where ['like', 'name', 'abc']
     * 6. not like Url xxx.xx/api/resource?name=!%%abc // to where ['not like', 'name', 'abc']
     *
     * 支持在关联关系中查询
     * 7. relation: xxx.xx/api/resource?relation>id=123 // ['=', 'relation.id', 123]
     *
     * @param array $conditions
     * @param bool  $allowRelation 是否允许在关联关系中查询
     *
     * @return array
     */
    public static function parseCondition(array $conditions = null, $allowRelation = true): array
    {
        $conditions = $conditions ?? \Yii::$app->request->get();
        unset($conditions['sort'], $conditions['page'], $conditions['size']);

        $result = ['and'];
        foreach ($conditions as $column => $condition) {
            if ($allowRelation) {
                $column = strtr($column, ['>' => '.']);
            }

            if (false !== strpos($condition, ',')) {
                $values = explode(',', $condition);

                $operator = 'in';
                if ('!' === $values[0][0]) {
                    $values[0] = ltrim($values[0], '!');
                    $operator = 'not in';
                }

                $result[] = [$operator, $column, $values];
                continue;
            }

            if (false !== strpos($condition, '@-@')) {
                $values = explode('@-@', $condition);

                $operator = 'between';
                if ('!' === $values[0][0]) {
                    $values[0] = ltrim($values[0], '!');
                    $operator = 'not between';
                }

                $result[] = [$operator, $column, $values];
                continue;
            }

            if (false !== strpos($condition, '%%')) {
                $operator = 'like';
                if ('!' === $condition[0]) {
                    $operator = 'not like';
                }

                $result[] = [$operator, $column, trim($condition, '!%')];
                continue;
            }

            $operator = '=';
            if ('!' === $condition[0]) {
                $condition = ltrim($condition, '!');
                $operator = '!=';
            }

            $result[] = [$operator, $column, $condition];
        }

        return 1 < count($result) ? $result : [];
    }

    /**
     * 解析请求参数中的排序条件.
     *
     * @example Url: xxx.xx/api/resource?sort=id,desc
     *
     * @param array $conditions 可以将整个\Yii::$app->request->get获取到的数据传入进来，或者自行处理后的数据
     * @param array $default
     *
     * @return array
     */
    public static function parseOrder(array $conditions = null, array $default = []): array
    {
        $conditions = $conditions ?? \Yii::$app->request->get();
        $condition = explode(',', $conditions['sort']);
        if (empty($condition)) {
            return $default;
        }

        $order = [];
        $sort = $condition[1] ?? 'desc';
        if (0 === strcmp('level', $condition[0])) {
            $order['level'] = StringHelper::startsWith($sort, 'asc') ? SORT_ASC : SORT_DESC;
        }

        if (0 === strcmp('time', $condition[0]) || 0 === strcmp('id', $condition[0])) {
            $order['id'] = 0 === strcmp('asc', $sort) ? SORT_ASC : SORT_DESC;
        } else {
            $order['id'] = SORT_DESC;
        }

        return $order;
    }

    /**
     * 解析请求参数中的page.
     *
     * @param int $default
     *
     * @return int
     */
    public static function parseQueryPage(int $default = 1): int
    {
        return (int) \Yii::$app->request->get('page', $default);
    }

    /**
     * 解析请求参数中的size.
     *
     * @param int $default
     *
     * @return int
     */
    public static function parseQuerySize(int $default = 10): int
    {
        return (int) \Yii::$app->request->get('size', $default);
    }

    /**
     * 计算当前的offset.
     *
     * @return int
     */
    public static function getOffset(): int
    {
        return (static::parseQueryPage() - 1) * static::parseQuerySize();
    }

    /**
     * 计算总页数.
     *
     * @param int $total
     *
     * @return int
     */
    public static function getTotalPages($total): int
    {
        return (int) ceil($total / static::parseQuerySize());
    }

    /**
     * 获取列表页的information.
     *
     * @param $total
     *
     * @return array
     */
    public static function getInformation($total): array
    {
        $sort = [];
        foreach (static::parseOrder() as $key => $value) {
            $sort[] = [$key => SORT_DESC === $value ? 'desc' : 'asc'];
        }

        return ['total' => $total, 'current_page' => static::parseQueryPage(), 'pages' => static::getTotalPages($total), 'size' => static::parseQuerySize(), 'sort' => $sort];
    }
}
