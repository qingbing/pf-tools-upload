<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<dl>
    <dt> 文件上传测试 demo</dt>
    <dd><a href="<?php echo $this->createUrl('/testUpload/index'); ?>" target="_blank">普通文件域上传</a></dd>
    <dd><a href="<?php echo $this->createUrl('/testUpload/model'); ?>" target="_blank">模型文件域上传</a></dd>
    <dd><a href="<?php echo $this->createUrl('/testUpload/validateModel'); ?>" target="_blank">验证模型文件域上传</a></dd>
    <dd><a href="<?php echo $this->createUrl('/testUpload/Url'); ?>" target="_blank">Url测试</a></dd>
</dl>

</body>
</html>