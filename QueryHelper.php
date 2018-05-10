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
     * @param bool  $allowRelation  是否允许在关联关系中查询
     * @param array $allowedColumns 允许进行查询的字段列表，默认允许所有
     *                              该参数可以是被允许字段的列表，也可以是一个以被允许字段作为键的关联数组，键的值为被允许的方法的列表，如果值为null或一个空的列表则表示不允许任何方法
     *                              e.g.:
     *                              ['name', 'description', 'type'] // 列表表示列表内的字段允许任何方法筛选
     *                              ['id' => null, 'name', 'description' => ['like'], 'type' => ['in', 'between'] // id不允许任何方法，name允许所有方法，description仅允许like或not like，type允许in, not in, between, not between
     *
     * @return array
     */
    public static function parseCondition(array $conditions = null, array $allowedColumns = null, $allowRelation = true): array
    {
        $conditions = $conditions ?? \Yii::$app->request->get();
        unset($conditions['sort'], $conditions['page'], $conditions['size']);

        $result = ['and'];
        foreach ($conditions as $column => $condition) {
            if ($allowedColumns && !\in_array($column, $allowedColumns, true) && !\array_key_exists($column, $allowedColumns)) {
                continue;
            }

            if ($allowRelation) {
                $column = strtr($column, ['>' => '.']);
            }

            if (false !== strpos($column, '>')) {
                continue;
            }

            $scope = $allowedColumns && \array_key_exists($column, $allowedColumns) ? $allowedColumns[$column] : ['in', 'like', 'between'];

            if (false !== strpos($condition, ',')) {
                if (!$scope || !\in_array('in', $scope, true)) {
                    continue;
                }

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
                if (!$scope || !\in_array('between', $scope, true)) {
                    continue;
                }

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
                if (!$scope || !\in_array('like', $scope, true)) {
                    continue;
                }

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
     * @param array $conditions     可以将整个\Yii::$app->request->get获取到的数据传入进来，或者自行处理后的数据
     * @param array $default        没有匹配或匹配失败时返回的默认排序
     * @param array $allowedColumns 允许被设置的字段列表，默认为允许所有
     *
     * @return array
     */
    public static function parseOrder(array $conditions = null, array $default = ['id' => SORT_DESC], array $allowedColumns = null): array
    {
        $conditions = $conditions ?? \Yii::$app->request->get();
        if (empty($conditions['sort']) || (null !== $allowedColumns && !$allowedColumns)) {
            return $default;
        }

        $orders = array_filter(explode(';', $conditions['sort']));

        if (empty($orders)) {
            return $default;
        }

        $result = [];
        foreach ($orders as $order) {
            if (false === strpos($order, ',')) {
                if (null === $allowedColumns || \in_array($order, $allowedColumns, true)) {
                    $result[$order] = SORT_ASC;
                }

                continue;
            }

            list($column, $mode) = explode(',', $order);

            if (null !== $allowedColumns && !\in_array($column, $allowedColumns, true)) {
                continue;
            }

            $result[$column] = StringHelper::startsWith($mode, 'asc') ? SORT_ASC : SORT_DESC;
        }

        return $result;
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
