<?php
namespace modules\updateprofilestatus\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;


class UserController extends Controller
{
    protected array|int|bool $allowAnonymous = false;
    
    public function actionUpdate(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        
        $request = Craft::$app->getRequest();
        $userId = $request->getBodyParam('userId');
        $profileComplete = $request->getBodyParam('fields.profileComplete', false);
        
        // Security: ensure user can only update their own profile
        $currentUser = Craft::$app->getUser()->getIdentity();
        if (!$currentUser || ($currentUser->id != $userId && !$currentUser->admin)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }
        
        $user = Craft::$app->getUsers()->getUserById($userId);
        
        if (!$user) {
            return $this->asJson([
                'success' => false,
                'message' => 'User not found'
            ]);
        }
        
        $user->setFieldValue('profileComplete', $profileComplete);
        
        if (Craft::$app->getElements()->saveElement($user)) {
            return $this->asJson([
                'success' => true,
                'message' => 'Profile status updated'
            ]);
        }
        
        return $this->asJson([
            'success' => false,
            'message' => 'Failed to save',
            'errors' => $user->getErrors()
        ]);
    }
}