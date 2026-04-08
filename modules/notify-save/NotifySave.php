<?php

namespace modules\notifysave;

use Craft;
use yii\base\Event;
use yii\base\Module as BaseModule;
use craft\elements\User;
use craft\events\ModelEvent;


/**
 * NotifySave module
 *
 * @method static NotifySave getInstance()
 */
class NotifySave extends BaseModule
{
    public function init(): void
    {
        Craft::setAlias('@modules/notifysave', __DIR__);

        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'modules\\notifysave\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\notifysave\\controllers';
        }

        parent::init();

        // Register the event listener
        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function(ModelEvent $event) {
                /** @var User $user */
                $user = $event->sender;

                // Only notify for front-end saves, not control panel edits
                if (Craft::$app->request->isCpRequest) {
                    return;
                }

                $this->sendNotificationUserSaved($user);
            }
        );
    
        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function() {
            // ...
            
        });
    }

     private function sendNotificationUserSaved(User $user)
    {
        // Send your email here
        $systemEmail = Craft::$app->getProjectConfig()->get('email.fromEmail');
        Craft::$app->mailer->compose()
            ->setTo($systemEmail)
            ->setSubject('User '. $user->fullName.' Saved: ' . $user->email)
            ->setTextBody('A user was saved: ' . $user->email)
            ->send();
    }
    
    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
    }
}


