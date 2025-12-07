<?php
/**
 * Guest Entry Notification Module
 * 
 * Installation:
 * 1. Place this in /modules/guestentrynotify/Module.php
 * 2. Add to config/app.php:
 *    'modules' => [
 *        'guestentrynotify' => \modules\guestentrynotify\Module::class,
 *    ],
 *    'bootstrap' => ['guestentrynotify'],
 */

namespace modules\guestentrynotify;

use Craft;
use yii\base\Event;
use yii\base\Module as BaseModule;
use craft\guestentries\controllers\SaveController;
use craft\guestentries\events\SaveEvent;
use craft\elements\Entry;

class Module extends BaseModule
{
    public function init()
    {
        parent::init();
        
        // Set the controllerNamespace
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'modules\\guestentrynotify\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\guestentrynotify\\controllers';
        }

        // Listen for guest entry save events
        Event::on(
            SaveController::class,
            SaveController::EVENT_AFTER_SAVE_ENTRY,
            function(SaveEvent $event) {
                /** @var Entry $entry */
                $entry = $event->entry;
                
                // Send notification email
                $this->sendNotificationEmail($entry);
            }
        );
    }
    
    /**
     * Send notification email when a guest entry is saved
     */
    private function sendNotificationEmail(Entry $entry)
    {
        try {
            Craft::info('Guest entry notification triggered for entry ID: ' . $entry->id, __METHOD__);
            
            // Get system email settings
            $systemEmail = Craft::$app->getProjectConfig()->get('email.fromEmail');
            $systemName = Craft::$app->getProjectConfig()->get('email.fromName');
            
            Craft::info('Sending notification to: ' . $systemEmail, __METHOD__);
            
            // Prepare variables to pass to the template
            $variables = [
                'entry' => $entry,
                'entryTitle' => $entry->title,
                'entryUrl' => $entry->getUrl(),
                'sectionName' => $entry->getSection()->name,
                'authorEmail' => $entry->author->email ?? 'Guest',
            ];
            
            Craft::info('Rendering email templates...', __METHOD__);
            
            // Render the HTML email template
            $htmlBody = Craft::$app->getView()->renderTemplate(
                '_emails/guest-entry-notification',
                $variables
            );
            
            Craft::info('HTML template rendered successfully', __METHOD__);
            
            // Optional: Create a plain text version
            try {
                $textBody = Craft::$app->getView()->renderTemplate(
                    '_emails/guest-entry-notification.txt',
                    $variables
                );
                Craft::info('Text template rendered successfully', __METHOD__);
            } catch (\Exception $e) {
                Craft::warning('Text template not found, using auto-generated text: ' . $e->getMessage(), __METHOD__);
                $textBody = null; // Let Craft auto-generate from HTML
            }
            
            Craft::info('Attempting to send email...', __METHOD__);
            
            // Send the email
            $message = Craft::$app->mailer->compose()
                ->setTo($systemEmail) // Send to system email
                ->setSubject("New Guest Entry: {$entry->title}")
                ->setHtmlBody($htmlBody);
            
            if ($textBody) {
                $message->setTextBody($textBody);
            }
            
            $sent = $message->send();
            
            if ($sent) {
                Craft::info(
                    "Guest entry notification email sent for entry ID {$entry->id}",
                    __METHOD__
                );
            } else {
                Craft::warning(
                    "Failed to send guest entry notification email for entry ID {$entry->id}",
                    __METHOD__
                );
            }
            
        } catch (\Exception $e) {
            Craft::error(
                "Error sending guest entry notification: {$e->getMessage()}",
                __METHOD__
            );
        }
    }
}