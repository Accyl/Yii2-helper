<?php

namespace lunacy\helpers;

use yii\base\Arrayable;

/**
 * 扩展数组小助手.
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * 将对象解析为数组并且对值进行转换.
     *
     * @param mixed  $data         需要处理的数据，可以是继承了Arrayable接口的对象的实例或stdClass的实例以及数组
     * @param string $default      需要被处理的默认值
     * @param bool   $hideIdentity 是否对身份数据进行隐藏处理
     * @param array  $identities   属于身份信息的key的列表
     *
     * @return array 处理后的数据
     */
    public static function decode($data, $default = 'n/a', bool $hideIdentity = true, array $identities = ['mobile', 'email']): array
    {
        if ($data instanceof Arrayable) {
            $result = $data->toArray();
        } else {
            $result = (array) $data;
        }

        foreach ($result as $key => $value) {
            if (\is_array($value) || \is_object($value)) {
                $result[$key] = static::decode($value);

                continue;
            }

            if (\is_string($value) && $default === trim($value)) {
                $value = '';
            }

            if ($hideIdentity && \in_array($key, $identities, true)) {
                $value = StringHelper::hideIdentity($value);
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * 从数据中提取指定的列.
     * 该方法能够处理任意嵌套级别的索引数组.
     *
     * 但是不能处理索引与关联混合的数组类型，请预先处理为纯索引或纯关联数组使用本方法
     *
     * @example
     *  $a = ['a' => 1, 'b' => 2, 'c' => 3];
     *  $a = ArrayHelper::extract($a, ['a', 'b']); // ['a' => 1, 'b' => 2]
     *
     *  $a = ['a' => 1, 'b' => ['c' => 2, 'd' => 3], 'e' => 4];
     *  $a = ArrayHelper::extract($a, ['a', 'b.c']); //['a' => 1, 'b' => ['c' => 2]]
     *
     *  $a = ['a' => 1, 'b' => ['c' => 2, 'd' => 3, 'e' => 4]];
     *  $a = ArrayHelper::extract($a, ['a', 'b' => ['c', 'e']]); // ['a' => 1, 'b' => ['c' => 2, 'e' => 4]]
     *
     * @param array $data
     * @param array $extract
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function extract(array $data, array $extract = []): array
    {
        $result = [];

        if (!static::isAssociative($data, false)) {
            foreach ($data as $key => $datum) {
                $result[$key] = static::extract($datum, $extract);
            }

            return $result;
        }

        foreach ($extract as $key => $value) {
            if (\is_array($value)) {
                if (!\array_key_exists($key, $data)) {
                    throw new \InvalidArgumentException('找不到索引：'.$key);
                }

                $result[$key] = static::extract($data[$key], $value);

                continue;
            }

            if (false !== strpos($value, '.')) {
                $subKeys = explode('.', $value);
                $subKey = array_shift($subKeys);

                if (!array_key_exists($subKey, $data)) {
                    throw new \InvalidArgumentException('找不到索引：'.$subKey);
                }

                try {
                    $result[$subKey] = static::merge($result[$subKey] ?? [], static::extract($data[$subKey], [implode('.', $subKeys)]));
                } catch (\InvalidArgumentException $exception) {
                    throw new \InvalidArgumentException('找不到索引：'.$value);
                }

                continue;
            }

            if (!array_key_exists($value, $data)) {
                throw new \InvalidArgumentException('找不到索引：'.$value);
            }

            $result[$value] = $data[$value];
        }

        return $result;
    }

    /**
     * 从数据中去除指定的键.
     * 该方法能够处理任意嵌套级别的索引数组.
     *
     * 但是不能处理索引与关联混合的数组类型，请预先处理为纯索引或纯关联数组使用本方法
     *
     * @param array $data
     * @param array $exclude
     *
     * @return array
     */
    public static function except(array $data, array $exclude = []): array
    {
        if (!static::isAssociative($data, false)) {
            foreach ($data as $key => $datum) {
                $data[$key] = static::except($datum, $exclude);
            }

            return $data;
        }

        foreach ($exclude as $key => $value) {
            if (\is_array($value)) {
                if (\array_key_exists($key, $data)) {
                    $data[$key] = \is_array($data[$key]) ? static::except($data[$key], $value) : $data[$key];
                }

                continue;
            }

            if (false !== strpos($value, '.')) {
                $subKeys = explode('.', $value);
                $subKey = array_shift($subKeys);

                if (\array_key_exists($subKey, $data)) {
                    $data[$subKey] = \is_array($data[$subKey]) ? static::except($data[$subKey], [implode('.', $subKeys)]) : $data[$subKey];
                }

                continue;
            }

            unset($data[$value]);
        }

        return $data;
    }

    /**
     * 对数组数据进行清理
     * 该方法对于简单类会直接转换为数组.
     *
     * @param array           $data
     * @param string|callable $callback
     * @param string          $chars
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function trim(array $data, $callback = 'trim', $chars = " \t\n\r\0\x0B"): array
    {
        foreach ($data as $key => $item) {
            if (empty($item)) {
                continue;
            }

            if (\is_string($item) || \is_int($item)) {
                if (\is_string($callback) && !function_exists($callback)) {
                    throw new \InvalidArgumentException('找不到指定的方法：'.$callback);
                }

                $data[$key] = call_user_func_array($callback, [$item, $chars]);
            } elseif (\is_array($item) || $item instanceof \stdClass) {
                $data[$key] = static::trim((array) $item);
            } else {
                throw new \InvalidArgumentException('错误的数据格式:'.\gettype($item));
            }
        }

        return $data;
    }
}
