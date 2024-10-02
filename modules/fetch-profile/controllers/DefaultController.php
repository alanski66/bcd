<?php

namespace modules\fetchprofile\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

/**
 * Fetch Profile controller
 */
class DefaultController extends Controller
{
    // Properties
    // =========================================================================

    protected array|bool|int $allowAnonymous = ['get-data'];


    // Public Methods
    // =========================================================================

    public function actionGetData(): Response
    {
        $data = ['data' => 'Some sample data'];

        return $this->asJson($data);
    }
}
