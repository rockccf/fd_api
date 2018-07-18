<?php

namespace app\controllers;

use app\components\dbix\CommonClass;
use app\components\dbix\TenantPdfClass;
use app\components\dbix\WorkflowClass;
use app\models\Category;
use app\models\City;
use app\models\Country;
use app\models\Currency;
use app\models\Industry;
use app\models\Item;
use app\models\ItemPriceHistory;
use app\models\ItemSampleDelivery;
use app\models\Product;
use app\models\ProductMappingTenant;
use app\models\State;
use app\models\Subcategory;
use app\models\Supplier;
use app\models\SupplierCategory;
use app\models\SupplierUserProfile;
use app\models\Tax;
use app\models\Tenant;
use app\models\TenantSupplier;
use app\models\Tender;
use app\models\TenderCategory;
use app\models\TenderCategoryEntry;
use app\models\TenderCategoryItem;
use app\models\TenderCategorySupplier;
use app\models\Uom;
use app\models\UomFormula;
use app\models\User;
use app\models\UserProfile;
use Yii;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}