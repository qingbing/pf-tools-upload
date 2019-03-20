<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-03-20
 * Version      :   1.0
 */

namespace Tools;


class UploadManager
{
    /**
     * 获取并创建绝对的上传路径
     * @param string $path
     * @param bool $createIfNoExists
     * @return string
     */
    static public function getPath($path = '', $createIfNoExists = false)
    {
        // 如果已经设置，直接返回
        static $_paths = [];
        if (isset($_paths[$path])) {
            return $_paths[$path];
        }

        // 获取上传文件根目录
        static $_baseUploadPath;
        if (null === $_baseUploadPath) {
            $uploadFolder = \PF::app()->getParam('uploadFolder');
            if (null === $uploadFolder) {
                $uploadFolder = 'upload';
            } else {
                $uploadFolder = trim($uploadFolder, '/');
            }
            $_baseUploadPath = dirname($_SERVER['SCRIPT_FILENAME']) . DS . $uploadFolder;
        }

        // 构建返回上传文件目录
        $rPath = $_baseUploadPath . DS . trim($path, '/');
        if (!is_dir($rPath) && true === $createIfNoExists) {
            @mkdir($rPath, 0777, true);
        }
        return $_paths[$path] = $rPath;
    }

    /**
     * 获取图片显示的绝对URL
     * @param string $type
     * @param bool $absolute
     * @return string
     */
    static public function getUrl($type = '', $absolute = false)
    {
        $_id = $type . ($absolute ? 1 : 0);
        // 如果已经设置，直接返回
        static $_urlPaths = [];
        if (isset($_urlPaths[$_id])) {
            return $_urlPaths[$_id];
        }

        // 获取上传文件根目录
        static $_baseUploadPath;
        if (null === $_baseUploadPath) {
            $uploadFolder = \PF::app()->getParam('uploadFolder');
            if (null === $uploadFolder) {
                $uploadFolder = 'upload';
            }
            $_baseUploadPath = dirname($_SERVER['SCRIPT_FILENAME']) . DS . $uploadFolder;
        }

        // 获取上传文件baseUrl
        static $_baseUrl;
        static $_absoluteBaseUrl;
        if ($absolute) {
            if (null === $_absoluteBaseUrl) {
                $uploadFolder = \PF::app()->getParam('uploadFolder');
                if (null === $uploadFolder) {
                    $uploadFolder = 'upload';
                }
                $_absoluteBaseUrl = \PF::app()->getRequest()->getBaseUrl(true) . '/' . $uploadFolder;
            }
            $rA = [$_absoluteBaseUrl];
        } else {
            if (null === $_baseUrl) {
                $uploadFolder = \PF::app()->getParam('uploadFolder');
                if (null === $uploadFolder) {
                    $uploadFolder = 'upload';
                }
                $_baseUrl = \PF::app()->getRequest()->getBaseUrl() . '/' . $uploadFolder;
            }
            $rA = [$_baseUrl];
        }
        empty($type) || array_push($rA, $type);
        return $_urlPaths[$_id] = implode('/', $rA) . '/';
    }
}