<?php

namespace modules\notifysave;

use Craft;
use yii\base\Event;
use yii\base\Module as BaseModule;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\utilities\SystemMessages;

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
                $this->sendNotificationUserSaved($user);
                // // Only send email for new users or when you want
                // if ($event->isNew) {
                //     $this->sendNotificationEmail($user);
                // }
                   // In a service method or event handler:
        
            }
        );
        // Event::on(
        //     SystemMessages::class,
        //     SystemMessages::EVENT_AFTER_VALIDATE,
        //     User::class,
        //     User::EVENT_AFTER_VALIDATE,
        //     function(ModelEvent $event) {
        //         /** @var User $user */
        //         $user = $event->sender;
        //         $this->sendNotificationUserVerified($user);
        //         // // Only send email for new users or when you want
        //         // if ($event->isNew) {
        //         //     $this->sendNotificationEmail($user);
        //         // }
        //            // In a service method or event handler:
        
        //     }
        // );


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
            ->setSubject('User Saved: ' . $user->email)
            ->setTextBody('A user was saved: ' . $user->email)
            ->send();
    }
         private function sendNotificationUserVerified(User $user)
    {
        // Send your email here
        $systemEmail = Craft::$app->getProjectConfig()->get('email.fromEmail');
        Craft::$app->mailer->compose()
            ->setTo($systemEmail)
            ->setSubject('User Verified: ' . $user->email)
            ->setTextBody('A user was verified: ' . $user->email)
            ->send();
    }
    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/5.x/extend/events.html to get started)
    }
}


