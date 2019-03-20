# pf-tools-upload
## 描述
工具——文件上传部件，验证

## 注意事项
- 支持普通文件域上传，模型文件域上传，验证模型文件域上传
- 上传都是通过\UploadFile::getInstance...(); 来手动上传
- 支持文件上传验证：主要验证是否必传，文件后缀，文件大小，文件的mime-type等
- 文件url和path管理可使用或参考"UploadManager"：使用getUrl 和 getPath
- 文件上传验证类型（标识）：\UploadFile::VALID_CLASS


## 使用方法
### 1. 普通文件域上传
```php
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
```
### 2. 模型文件域上传
```php
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
```
### 3. 验证模型文件域上传
#### 3.1 验证model
```php
class TestUploadValid extends FormModel
{
    public $version;
    public $upload;

    public function rules()
    {
        return [
            ['version', 'string', 'allowEmpty' => false],
            ['upload', \UploadFile::VALID_CLASS, 'allowEmpty' => false, 'types' => ['gif', 'png', 'jpg', 'jpeg'],],
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
        $upload = \UploadFile::getInstance($this, 'upload');
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
```
#### 3.2 控制器
```php
            $model->setAttributes($_POST['TestUploadValid']);
            if ($model->save()) {
                $this->success('', -1);
            } else {
                $this->failure('操作失败', $model->getErrors());
            }
```

### 4. getUrl 和 getPath 使用
```php
        // 获取上传文件目录，无则创建
        var_dump(UploadManager::getPath('valid', true));

        // 获取上传文件的访问URL
        var_dump(UploadManager::getUrl('avatars') . '1546831660.txt');
        var_dump(UploadManager::getUrl('avatars', true) . '1546831660.txt');
```

## ====== 异常代码集合 ======

异常代码格式：1022 - XXX - XX （组件编号 - 文件编号 - 代码内异常）
```
 - 102200101 : "{file}"上传不完整
 - 102200102 : "{file}"的临时上传文件丢失
 - 102200103 : 持久化"{file}"失败
 - 102200104 : 没有支持文件上传的扩展
 - 102200105 : "\UploadSupports\FileValidator.types"必须为数组
 - 102200106 : "\UploadSupports\FileValidator.mimeTypes"必须为数组
```