<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;

//Remove the debugger toolbar
if (class_exists('yii\debug\Module')) {
    $this->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?= Html::cssFile(Url::to('@web/css/pdf.css', true)) ?> <!-- True, get absolute url -->
    <?= Html::cssFile(Url::to('@web/css/horecabid.css', true)) ?> <!-- True, get absolute url -->
    <?= Html::cssFile(Url::to('@web/bootstrap/css/bootstrap.min.css', true)) ?> <!-- True, get absolute url -->
    <?= Html::jsFile(Url::to('@web/js/jquery-3.1.1.min.js', true)) ?>
    <?= Html::jsFile(Url::to('@web/bootstrap/js/bootstrap.min.js', true)) ?>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="container-fluid">
<?= $content ?>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
