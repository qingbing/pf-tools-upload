<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-01-07
 * Version      :   1.0
 */

namespace UploadSupports;

use Abstracts\Validator;
use Helper\Exception;
use Helper\FileManager;
use Helper\Unit;
use Tools\UploadFile;

class FileValidator extends Validator
{
    /* @var null|array => ['gif', 'jpg', ....]， 允许上传的文件扩展名,默认为"null",表示允许所有的文件格式上传 */
    public $types;
    /* @var null|array 许上传的文件的MIME类型，默认为"null",表示允许所有的"mime"类型，使用该属性，不要安装"fileinfo PECL"扩展 */
    public $mimeTypes;
    /* @var int 上传文件所需的最小字节数 */
    public $minSize;
    /* @var int 上传文件所需的最大字节数，大小限制也受“upload_max_filesize”INI设置和“MAX_FILE_SIZE”隐藏字段值的影响 */
    public $maxSize;
    /* @var string 上传的文件过大时使用的错误消息 */
    public $tooLargeMessage = '上传文件"{file}"太大，不能超过{limit}个字节';
    /* @var string 上传的文件太小时使用的错误消息 */
    public $tooSmallMessage = '上传文件"{file}"太小，不能低于{limit}个字节';
    /* @var string 当上传的文件具有扩展名时使用的错误消息 */
    public $wrongTypeMessage = '"{file}"允许的上传文件扩展：{extensions}';
    /* @var string 当上传文件的"mime"类型不在"mimeTypes"列表中时使用的错误消息 */
    public $wrongMimeTypeMessage = '"{file}"允许的上传文件媒体类型：{mimeTypes}';
    /* @var int 可以保存的最大文件数，默认为"1"，可以通过定义更高的数字，同时上传多个文件 */
    public $maxFiles = 1;
    /* @var string 上传的文件超过最大文件数时使用的错误消息 */
    public $tooManyMessage = '"{attribute}"不能接受大于{limit}个文件同时上传';

    /**
     * 对上传到的文件进行相关验证并处理
     * @param \Abstracts\Model $object
     * @param string $attribute
     * @throws \Exception
     */
    protected function validateAttribute($object, $attribute)
    {
        if ($this->maxFiles > 1) {
            $files = $object->{$attribute};
            if (!is_array($files) || !isset($files[0]) || !$files[0] instanceof UploadFile) {
                $files = UploadFile::getInstances($object, $attribute);
            }
            if ([] === $files) {
                return $this->emptyAttribute($object, $attribute);
            }
            if (count($files) > $this->maxFiles) {
                $this->addError($object, $attribute, $this->tooManyMessage, [
                    '{attribute}' => $attribute,
                    '{limit}' => $this->maxFiles,
                ]);
            } else
                foreach ($files as $file) {
                    $this->validateFile($object, $attribute, $file);
                }
        } else {
            $file = $object->{$attribute};
            if (!$file instanceof UploadFile) {
                $file = UploadFile::getInstance($object, $attribute);
                if (null === $file) {
                    return $this->emptyAttribute($object, $attribute);
                }
            }
            $this->validateFile($object, $attribute, $file);
        }
    }

    /**
     * 验证文件
     * @param \Abstracts\Model $object
     * @param string $attribute
     * @param UploadFile $file
     * @return null|void
     * @throws \Exception
     */
    protected function validateFile($object, $attribute, $file)
    {
        if (null === $file || ($error = $file->getError()) == UPLOAD_ERR_NO_FILE) {
            $this->emptyAttribute($object, $attribute);
            return;
        } else if (
            $error == UPLOAD_ERR_INI_SIZE
            || $error == UPLOAD_ERR_FORM_SIZE
            || (null !== $this->maxSize && $file->getSize() > $this->maxSize)
        ) {
            $this->addError($object, $attribute, $this->tooLargeMessage, [
                '{file}' => $file->getName(),
                '{limit}' => $this->getSizeLimit()
            ]);
        } else if ($error == UPLOAD_ERR_PARTIAL) {
            throw new Exception(str_cover('"{file}"上传不完整', [
                '{file}' => $file->getName(),
            ]), 102200101);
        } else if ($error == UPLOAD_ERR_NO_TMP_DIR) {
            throw new Exception(str_cover('"{file}"的临时上传文件丢失', [
                '{file}' => $file->getName(),
            ]), 102200102);
        } else if ($error == UPLOAD_ERR_CANT_WRITE) {
            throw new Exception(str_cover('持久化"{file}"失败', [
                '{file}' => $file->getName(),
            ]), 102200103);
        } else if (defined('UPLOAD_ERR_EXTENSION') && $error == UPLOAD_ERR_EXTENSION) { // available for PHP 5.2.0 or above
            throw new Exception('没有支持文件上传的扩展', 102200104);
        }

        if (null !== $this->minSize && $file->getSize() < $this->minSize) {
            $this->addError($object, $attribute, $this->tooSmallMessage, [
                '{file}' => $file->getName(),
                '{limit}' => $this->minSize,
            ]);
        }

        if (null !== $this->types) {
            if (!is_array($this->types)) {
                throw new Exception('"\UploadSupports\FileValidator.types"必须为数组', 102200105);
            }
            if (!in_array(strtolower($file->getExtensionName()), $this->types)) {
                $this->addError($object, $attribute, $this->wrongTypeMessage, [
                    '{file}' => $file->getName(),
                    '{extensions}' => implode(', ', $this->types),
                ]);
            }
        }


        if (null !== $this->mimeTypes) {
            if (!is_array($this->mimeTypes)) {
                throw new Exception('"\UploadSupports\FileValidator.mimeTypes"必须为数组', 102200106);
            }
            $mimeType = FileManager::getMimeType($file->getTempName());
            if (null === $mimeType || !in_array(strtolower($mimeType), $this->mimeTypes)) {
                $this->addError($object, $attribute, $this->wrongMimeTypeMessage, [
                    '{file}' => $file->getName(),
                    '{mimeTypes}' => implode(', ', $this->mimeTypes),
                ]);
            }
        }
        return null;
    }

    /**
     * 当上传文件为空时，根据是否允许为空选择是否报错
     * @param \Abstracts\Model $object
     * @param string $attribute
     * @throws \Exception
     */
    protected function emptyAttribute($object, $attribute)
    {
        if (!$this->allowEmpty) {
            $message = $this->message !== null ? $this->message : '"{attribute}"不能为空';
            $this->addError($object, $attribute, $message);
        }
    }

    /**
     * 返回上传文件允许的最大"size"，取决因素有以下三种情况：
     * <pre>
     * php.ini中的 "upload_max_filesize"
     * "MAX_FILE_SIZE" 隐藏字段
     * FileValidator.maxSize
     * </pre>
     * @return integer the size limit for uploaded files.
     */
    protected function getSizeLimit()
    {
        $limit = ini_get('upload_max_filesize');
        $limit = Unit::switchMemoryCapacity($limit, 'B');
        if (null !== $this->maxSize && $limit > 0 && $this->maxSize < $limit) {
            $limit = $this->maxSize;
        }
        if (isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE'] > 0 && $_POST['MAX_FILE_SIZE'] < $limit) {
            $limit = $_POST['MAX_FILE_SIZE'];
        }
        return $limit;
    }
}