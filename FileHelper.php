<?php

namespace Accyl\helpers;

use yii\web\UploadedFile;

/**
 * 文件助手类.
 *
 * @author Luna <Luna@cyl-mail.com>
 */
class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * @var array
     */
    const IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/jpg', 'image/jpe', 'image/gif'];

    /**
     * 获取文件的时间信息.
     *
     * @param string $file
     *
     * @return array
     */
    public static function getFileTimes(string $file): array
    {
        return ['aTime' => fileatime($file), 'cTime' => filectime($file), 'mTime' => filemtime($file)];
    }

    /**
     * 通过mimeType获取文件类型的后缀.
     *
     * @param string      $mimeType
     * @param null|string $magicFile
     *
     * @return string
     */
    public static function getExtensionByMimeType(string $mimeType, string $magicFile = null): string
    {
        $extensions = static::getExtensionsByMimeType($mimeType, $magicFile);

        if ('image/jpeg' === $mimeType || 'image/jpg' === $mimeType || 'image/jpe' === $mimeType) {
            return 'jpg';
        }

        if (!empty($extensions)) {
            return $extensions[0];
        }

        return '';
    }

    /**
     * 上传单个文件.
     *
     * @param string $name
     * @param string $savePath
     * @param bool   $onlyImage
     *
     * @throws \Exception
     *
     * @return bool|string
     */
    public static function saveUploadFileByName(string $name, string $savePath, bool $onlyImage = false)
    {
        if (!$instance = UploadedFile::getInstanceByName($name)) {
            \Yii::error(['上传文件失败，找不到指定的文件名称', $name]);

            throw new \InvalidArgumentException('上传文件失败，找不到指定的文件名称：'.$name);
        }

        if (0 !== $instance->error) {
            \Yii::error(['上传文件失败', $instance->error]);

            throw new \ErrorException('上传文件失败：'.$instance->error);
        }

        if ($onlyImage && !\in_array($instance->type, self::IMAGE_TYPES, true)) {
            \Yii::error(['上传的文件类型错误', $instance->type]);

            throw new \ErrorException('上传的文件类型错误：'.$instance->type);
        }

        if (!\is_writable(static::getUploadFileRootPath())) {
            \Yii::error(['图片上传路径不具有写入权限', static::getUploadFileRootPath()], __METHOD__);

            throw new \ErrorException('图片上传路径不具有写入权限：'.static::getUploadFileRootPath());
        }

        try {
            if (!static::createDirectory(self::getWebRootPath().$savePath)) {
                throw new \ErrorException('创建目录失败：'.static::getWebRootPath().$savePath);
            }
        } catch (\Exception $exception) {
            \Yii::error(['创建目录失败', self::getWebRootPath().$savePath, $exception->getMessage()], __METHOD__);

            throw $exception;
        }

        $file = $savePath.DIRECTORY_SEPARATOR.md5(time().StringHelper::generateUniqueId()).'.'.static::getExtensionByMimeType($instance->type);

        if (!$instance->saveAs(self::getWebRootPath().$file)) {
            \Yii::error(['保存文件失败', $instance->error], __METHOD__);

            throw new \ErrorException('保存文件失败：'.$instance->error);
        }

        return $file;
    }

    /**
     * 保存所有上传的文件.
     *
     * @param string $name      文件数组的名称
     * @param string $savePath  文件保存的路径
     * @param bool   $onlyImage 是否只允许上传图片
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function saveUploadFilesByName(string $name, string $savePath, $onlyImage = false): array
    {
        $result = [];

        if (!$instances = UploadedFile::getInstancesByName($name)) {
            return $result;
        }

        foreach ($instances as $instance) {
            if (0 !== $instance->error) {
                \Yii::error(['上传文件失败', $instance->error]);

                throw new \InvalidArgumentException('上传文件失败，找不到指定的文件名称：'.$name);
            }

            if ($onlyImage && !\in_array($instance->type, self::IMAGE_TYPES, true)) {
                \Yii::error(['上传的文件类型错误', $instance->type]);

                throw new \ErrorException('上传的文件类型错误：'.$instance->type);
            }
        }

        if (!\is_writable(static::getUploadFileRootPath())) {
            \Yii::error(['图片上传路径不具有写入权限', static::getUploadFileRootPath()], __METHOD__);

            throw new \ErrorException('图片上传路径不具有写入权限：'.static::getUploadFileRootPath());
        }

        try {
            if (!static::createDirectory(self::getWebRootPath().$savePath)) {
                throw new \ErrorException('创建目录失败：'.static::getWebRootPath().$savePath);
            }
        } catch (\Exception $exception) {
            \Yii::error(['创建目录失败', self::getWebRootPath().$savePath, $exception->getMessage()], __METHOD__);

            throw $exception;
        }

        foreach ($instances as $instance) {
            $file = $savePath.DIRECTORY_SEPARATOR.md5(time().StringHelper::generateUniqueId()).'.'.static::getExtensionByMimeType($instance->type);

            if (!$instance->saveAs(self::getWebRootPath().$file)) {
                \Yii::error(['保存文件失败', $instance->error], __METHOD__);

                throw new \ErrorException('保存文件失败：'.$instance->error);
            }

            $result[] = $file;
        }

        return $result;
    }

    /**
     * 移除文件或文件夹.
     *
     * @param array $items
     * @param bool  $allowDir
     *
     * @return bool
     */
    public static function remove(array $items, bool $allowDir = false): bool
    {
        foreach ($items as $item) {
            if ($allowDir && is_dir($item)) {
                try {
                    static::removeDirectory($item);
                } catch (\Exception $exception) {
                    \Yii::error(['删除目录失败', $item, $exception->getMessage()], __METHOD__);
                }

                continue;
            }

            $filename = realpath($item);
            $filePath = \dirname($filename);

            if (!\is_file($filename)) {
                \Yii::error(['文件不存在或不是一个文件', $filename], __METHOD__);

                continue;
            }

            if (!is_writable($filePath)) {
                \Yii::error(['该目录没有写入权限', $filePath], __METHOD__);

                continue;
            }

            @unlink($filename);
        }

        return true;
    }

    /**
     * @return string
     */
    public static function getWebRootPath(): string
    {
        return (string) \Yii::getAlias('@webroot');
    }

    /**
     * 获取图片的URL.
     *
     * @param string $image 图片地址
     * @param bool   $abs   是否获取绝对路径
     *
     * @return string
     */
    public static function getImageUrl(string $image, bool $abs = true): string
    {
        if ($abs) {
            return 0 === strncmp($image, 'https://', 8) || 0 === strncmp($image, 'http://', 7) ? $image : \Yii::$app->params['image-host-address'].$image;
        }

        if (0 === strncmp($image, 'https://', 8) || 0 === strncmp($image, 'http://', 7)) {
            $protocol = strncmp($image, 'https://', 8) ? 'https://' : 'http://';
            $image = (string) substr($image, 0, \strlen($protocol));

            $array = explode('/', $image);
            array_shift($array);
            $image = implode('/', $array);
        }

        return $image;
    }

    /**
     * @return string
     */
    private static function getUploadFileRootPath(): string
    {
        return self::getWebRootPath().(\Yii::$app->params['upload-dir'] ?? 'uploads');
    }
}
