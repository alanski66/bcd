<?php
namespace modules\updateprofilestatus;

use Craft;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();
        
        // Set the controller namespace
        $this->controllerNamespace = 'modules\\updateprofilestatus\\controllers';
        
        Craft::setAlias('@modules/updateprofilestatus', __DIR__);
    }
}