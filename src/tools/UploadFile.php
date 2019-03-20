<?php
/**
 * Link         :   http://www.phpcorner.net
 * User         :   qingbing<780042175@qq.com>
 * Date         :   2019-03-20
 * Version      :   1.0
 */

namespace Tools;


use Html;

class UploadFile
{
    const VALID_CLASS = '\UploadSupports\FileValidator';
    /**
     * @var UploadFile[]
     */
    static private $_files;

    private $_name;
    private $_tempName;
    private $_type;
    private $_size;
    private $_error;

    /**
     * 返回制定上传文件的实例，来源参考{@link Html :: activeFileField}
     * @param \Abstracts\Model $model
     * @param string $attribute
     * @return UploadFile
     */
    static public function getInstance($model, $attribute)
    {
        return self::getInstanceByName(Html::resolveName($model, $attribute));
    }

    /**
     * 返回指定上传文件的实例，名称可以为字符串或数组："Post ['imageFile']"或"Post [0] ['imageFile']"
     * @param string $name
     * @return UploadFile
     * Null is returned if no file is uploaded for the specified name.
     */
    static public function getInstanceByName($name)
    {
        if (null === self::$_files) {
            self::prefetchFiles();
        }

        return isset(self::$_files[$name]) && self::$_files[$name]->getError() != UPLOAD_ERR_NO_FILE ? self::$_files[$name] : null;
    }

    /**
     * 返回模型制定属性的所有上传文件
     * @param \Abstracts\Model $model
     * @param string $attribute
     * @return UploadFile[]
     */
    static public function getInstances($model, $attribute)
    {
        return self::getInstancesByName(Html::resolveName($model, $attribute));
    }

    /**
     * 返回指定数组名开头的实例数组
     * 如果多文件被上传并保存在"Files[0]","Files[1]"...,可通过传递"Files"作为数组名称
     * @param string $name
     * @return UploadFile[]
     * Empty array is returned if no adequate upload was found.
     */
    static public function getInstancesByName($name)
    {
        if (null === self::$_files) {
            self::prefetchFiles();
        }

        $len = strlen($name);
        $results = [];
        foreach (array_keys(self::$_files) as $key) {
            if (0 === strncmp($key, $name . '[', $len + 1) && UPLOAD_ERR_NO_FILE != self::$_files[$key]->getError()) {
                $results[] = self::$_files[$key];
            }
        }
        return $results;
    }

    /**
     * 清理已经加载的 UploadFile实例
     */
    static public function reset()
    {
        self::$_files = null;
    }

    /**
     * 将 $_FILES 实例化成全局变量方便使用
     */
    static protected function prefetchFiles()
    {
        self::$_files = [];
        if (!isset($_FILES) || !is_array($_FILES)) {
            return;
        }
        foreach ($_FILES as $class => $info) {
            self::collectFilesRecursive($class, $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
        }
    }

    /**
     * 处理{@link getInstanceByName}的传入文件。
     * @param string $key key for identifiing uploaded file: class name and subarray indexes
     * @param mixed $names file names provided by PHP
     * @param mixed $tmp_names temporary file names provided by PHP
     * @param mixed $types filetypes provided by PHP
     * @param mixed $sizes file sizes provided by PHP
     * @param mixed $errors uploading issues provided by PHP
     */
    static protected function collectFilesRecursive($key, $names, $tmp_names, $types, $sizes, $errors)
    {
        if (is_array($names)) {
            foreach ($names as $item => $name) {
                self::collectFilesRecursive($key . '[' . $item . ']', $names[$item], $tmp_names[$item], $types[$item], $sizes[$item], $errors[$item]);
            }
        } else {
            self::$_files[$key] = new UploadFile($names, $tmp_names, $types, $sizes, $errors);
        }
    }

    /**
     * 构造函数，实例化 "UploadFile" 实例
     * @param string $name the original name of the file being uploaded
     * @param string $tempName the path of the uploaded file on the server.
     * @param string $type the MIME-type of the uploaded file (such as "image/gif").
     * @param integer $size the actual size of the uploaded file in bytes
     * @param integer $error the error code
     */
    public function __construct($name, $tempName, $type, $size, $error)
    {
        $this->_name = $name;
        $this->_tempName = $tempName;
        $this->_type = $type;
        $this->_size = $size;
        $this->_error = $error;
    }

    /**
     * 返回上传文件的原始名称
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 返回服务器上上传文件的临时路径
     * @return string
     */
    public function getTempName()
    {
        return $this->_tempName;
    }

    /**
     * 返回上传文件的"mime-type",如："image/gif",使用"File::getMimeType"来确定mime类型
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * 返回上传文件的实际大小
     * fanhui
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * 返回文件上传状态的错误代码，为"0"表示正常
     * @return int the error code
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * 判断上传是否遇到错误
     * @return bool
     */
    public function getHasError()
    {
        return $this->_error != UPLOAD_ERR_OK;
    }

    /**
     * 返回上传文件的扩展名，扩展名不包含"."
     * @return string
     */
    public function getExtensionName()
    {
        if (false !== ($pos = strrpos($this->_name, '.'))) {
            return substr($this->_name, $pos + 1);
        }
        return '';
    }

    /**
     * 保存上传的文件，上传采用 "move_uploaded_file" 方法，如果目标文件"$file"存在将会被覆盖
     * @param string $file the file path used to save the uploaded file
     * @param bool $deleteTempFile whether to delete the temporary file after saving.
     * @return bool whether the file is saved successfully
     */
    public function saveAs($file, $deleteTempFile = true)
    {
        if ($this->_error == UPLOAD_ERR_OK) {
            if ($deleteTempFile) {
                return @move_uploaded_file($this->_tempName, $file);
            } else if (is_uploaded_file($this->_tempName)) {
                return copy($this->_tempName, $file);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 返回对象的字符串表示，这里放回上传文件的文件名
     * @return string the string representation of the object
     */
    public function __toString()
    {
        return $this->_name;
    }
}