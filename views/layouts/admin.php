<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Blabber',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Users', 'url' => ['/user/index']],
            ['label' => 'Countries', 'url' => ['/country/index']],
            ['label' => 'Cities', 'url' => ['/city/index']],
            ['label' => 'Categories', 'url' => ['/category/index']],
            ['label' => 'Businesses', 'url' => ['/business/index']],
            ['label' => 'Interests', 'url' => ['/interest/index']],
            ['label' => 'Flags', 'url' => ['/flag/index']],
            ['label' => 'Sponsors', 'url' => ['/sponsor/index']],
            ['label' => 'Reviews', 'url' => ['/review/index']],
            ['label' => 'Reports', 'url' => ['/report/index']],
            ['label' => 'Offers', 'url' => ['/offer/index']],
            ['label' => 'Media', 'url' => ['/media/index']],
            ['label' => 'Comments', 'url' => ['/comment/index']],
            ['label' => 'Blogs', 'url' => ['/blog/index']],
            ['label' => 'Polls', 'url' => ['/poll/index']],
            ['label' => 'Votes', 'url' => ['/vote/index']],
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post', ['class' => 'navbar-form'])
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->name . ')',
                    ['class' => 'btn btn-link']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Blabber Company <?= date('Y') ?></p>

        <p class="pull-right"></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
