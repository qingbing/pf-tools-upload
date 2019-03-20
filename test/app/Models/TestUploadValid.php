<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-01-07
 * Version      :   1.0
 */

namespace TestApp\Models;


use Abstracts\FormModel;
use Tools\UploadFile;
use Tools\UploadManager;

class TestUploadValid extends FormModel
{
    public $version;
    public $upload;

    public function rules()
    {
        return [
            ['version', 'string', 'allowEmpty' => false],
            ['upload', UploadFile::VALID_CLASS, 'allowEmpty' => false, 'types' => ['gif', 'png', 'jpg', 'jpeg'],],
        ];
    }

    /**
     * 上传文件并处理
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        // 获取上传实例
        $upload = UploadFile::getInstance($this, 'upload');
        if (null === $upload) {
            $this->addError('upload', '请选择要上传的文件');
            return false;
        }
        $filename = time() . '.' . $upload->getExtensionName();
        // 上传文件
        if (!$upload->saveAs(UploadManager::getPath('valid', true) . DS . $filename)) {
            $this->addError('upload', '上传图像错误');
        }
        return true;
    }
}