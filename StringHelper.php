<?php

namespace accyl\helpers;

/**
 * 扩展StringHelper.
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class StringHelper extends \yii\helpers\StringHelper
{
    /**
     * 获取32位长度的密码.
     *
     * @param string $password
     *
     * @return string
     */
    public static function getPassword(string $password): string
    {
        return 32 === mb_strlen($password) ? $password : md5($password);
    }

    /**
     * 清除xss攻击代码.
     *
     * @param string $data
     *
     * @return string
     */
    public static function xssClean(string $data): string
    {
        $data = str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        return $data;
    }

    /**
     * 生成随机字符串.
     *
     * @param int    $length     字符串长度
     * @param string $prefix     字符串前缀
     * @param string $candidates 随机因子
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function generateRandomString(int $length = 8, string $prefix = '', string $candidates = 'abcdefghijklmnopqrstuvwxyz0123456789'): string
    {
        if (empty($candidates)) {
            throw new \InvalidArgumentException('随机因子不能为空');
        }

        $randomString = '';

        $i = 0;
        while ($i < $length - mb_strlen($prefix)) {
            $index = random_int(0, \mb_strlen($candidates) - 1);
            $randomString .= $candidates[$index];
            ++$i;
        }

        return $prefix.$randomString;
    }

    /**
     * 生成一个简单字符串.
     *
     * @param int    $length 字符串长度，最大不超过62位，该长度不计算前缀的长度
     * @param string $prefix 字符串前缀
     *
     * @return string 返回的字符串总长度为$prefix的长度加上$length的值
     */
    public static function generateSimpleRandomString(int $length = 8, string $prefix = ''): string
    {
        $candidates = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $randomString = substr(str_shuffle($candidates), 0, $length > \mb_strlen($candidates) ? \mb_strlen($candidates) : $length);

        return $prefix.$randomString;
    }

    /**
     * 生成一个唯一ID.
     *
     * @return string
     */
    public static function generateUniqueId(): string
    {
        return md5(uniqid(static::generateSimpleRandomString(6), true).static::generateSimpleRandomString(6));
    }

    /**
     * 隐藏邮箱和手机号码.
     *
     * @param string $identity
     *
     * @return string
     */
    public static function hideIdentity(string $identity): string
    {
        if (!$identity) {
            return $identity;
        }

        if (false !== strpos($identity, '@')) {
            $values = self::explode($identity, '@');

            return substr($values[0], 0, 3).'***@'.$values[1];
        }

        return substr_replace($identity, '****', 3, 4);
    }
}
