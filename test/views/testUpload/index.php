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
<?php /*echo \Html::beginForm(['testUpload/uploadDo', 'id' => 5], 'post', [
        "enctype" => "multipart/form-data",
]);*/ ?>
<?php echo \Html::beginForm('', 'post', [
        "enctype" => "multipart/form-data",
]); ?>

<p><?php echo \Html::fileField('upload'); ?></p>
<p>
    <?php echo \Html::submitButton('Submit', [
        'name' => 'submit',
    ]); ?>
</p>

<?php echo \Html::endForm(); ?>

</body>
</html>