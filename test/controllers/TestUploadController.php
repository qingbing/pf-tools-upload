<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-01-04
 * Version      :   1.0
 */

namespace Controllers;

use Helper\Exception;
use Render\Abstracts\Controller;
use TestApp\Models\TestUploadModel;
use TestApp\Models\TestUploadValid;
use Tools\UploadFile;
use Tools\UploadManager;

class TestUploadController extends Controller
{
    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function actionIndex()
    {
        if (isset($_POST['submit'])) {
            // 获取上传实例
            $upload = UploadFile::getInstanceByName('upload');
            if (null === $upload) {
                throw new Exception('请选择要上传的文件');
            }
            $filename = time() . '.' . $upload->getExtensionName();
            // 上传文件
            if (!$upload->saveAs(UploadManager::getPath('avatars', true) . DS . $filename)) {
                throw new Exception('上传图像错误，请联系管理员');
            }
            $this->success('文件上传成功', -1);
        }
        $this->render('index', []);
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function actionModel()
    {
        $model = new TestUploadModel();
        if (isset($_POST['submit'])) {
            // 获取上传实例
            $upload = UploadFile::getInstance($model, 'upload');
            if (null === $upload) {
                throw new Exception('请选择要上传的文件');
            }
            $filename = time() . '.' . $upload->getExtensionName();
            // 上传文件
            if (!$upload->saveAs(UploadManager::getPath('model', true) . DS . $filename)) {
                throw new Exception('上传图像错误，请联系管理员');
            }
            $this->success('文件上传成功', -1);
        }
        $this->render('model', [
            'model' => $model,
        ]);
    }

    /**
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function actionValidateModel()
    {
        $model = new TestUploadValid();
        if (isset($_POST['submit'])) {
            $model->setAttributes($_POST['TestUploadValid']);
            if ($model->save()) {
                $this->success('', -1);
            } else {
                $this->failure('操作失败', $model->getErrors());
            }
        }
        $this->render('validate', [
            'model' => $model,
        ]);
    }

    public function actionUrl()
    {
        var_dump(UploadManager::getUrl('avatars') . '1546831660.txt');
        var_dump(UploadManager::getUrl('avatars', true) . '1546831660.txt');
        var_dump(UploadManager::getUrl('avatars') . '1546831660.txt');
        var_dump(UploadManager::getUrl('avatars', true) . '1546831660.txt');
    }
}