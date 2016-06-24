<?php

namespace app\controllers;

use app\models\Dishes;
use app\models\Ingredients;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

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

    public function actionIndex()
    {
        $model = new Dishes();
        $request = Yii::$app->request;

        if ($request->post()) {
            $ingredientRequest = ($request->post('Dishes', '')) ? : [];
            $ingredientRequest = (isset($ingredientRequest['ingredients']) && !empty($ingredientRequest['ingredients'])) ?
                $ingredientRequest['ingredients'] : [];

            foreach ($ingredientRequest as &$v) {
                $v = (int) $v;
            }

            $message = '';
            $isFullMatch = false;
            $result = false;
            $data = null;

            if (count($ingredientRequest) > 1 && count($ingredientRequest) <=5 ) {
                $data = $model->getFullIngredientDishes(true, 2, $ingredientRequest, Dishes::FILTER_FULL_MATCH);

                if ($data->count) {
                    $isFullMatch = $result = true;
                } else {
                    $data = $model->getFullIngredientDishes(true, 2, $ingredientRequest, Dishes::FILTER_PARTIAL);

                    if ($data->count) {
                        $result = true;
                    } else {
                        $message = 'Ничего не найдено';
                    }
                }
            } else {
                $message = (count($ingredientRequest) <= 1) ? 'Выберите больше ингредиентов' : $message;
                $message = (count($ingredientRequest) > 5 ) ? 'Выберите меньше ингредиентов' : $message;
            }

            $ingredientRequested = (count($ingredientRequest)) ?
                Ingredients::find()->select(['name'])->where(['id' => $ingredientRequest])->asArray()->all() : [];

            return $this->render('index', [
                'model' => $model,
                'message' => $message,
                'result' => $result,
                'dataProvider' => $data,
                'ingredients' => Ingredients::find()->all(),
                'ingredientRequested' => $ingredientRequested,
                'isFullMatch' => $isFullMatch,
            ]);
        } else {
            return $this->render('index', [
                'model' => $model,
                'result' => false,
                'message' => '',
                'ingredients' => Ingredients::find()->all(),
                'ingredientRequested' => [],
            ]);
        }
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        //if ($model->load(Yii::$app->request->post()) && $model->login()) {
        if ($model->load(Yii::$app->request->post()) && $model->loginAdmin()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}
