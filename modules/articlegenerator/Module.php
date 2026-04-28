<?php
namespace modules\articlegenerator;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use yii\base\Event;

class Module extends \yii\base\Module
{
    public function init(): void
    {
        parent::init();
        Craft::setAlias('@modules/articlegenerator', __DIR__);

        if (Craft::$app instanceof \craft\console\Application) {
            $this->controllerNamespace = 'modules\\articlegenerator\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\articlegenerator\\web\\controllers';
            $this->_registerSidebarHtml();
            $this->_registerTemplateRoot();
        }
    }

    private function _registerTemplateRoot(): void
    {
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['articlegenerator'] = __DIR__ . '/templates';
        });
    }

    private function _registerSidebarHtml(): void
    {
        Event::on(Entry::class, Element::EVENT_DEFINE_SIDEBAR_HTML, function(DefineHtmlEvent $event) {
            /** @var Entry $entry */
            $entry = $event->sender;

            if ($entry->type->handle !== 'article') {
                return;
            }

            $event->html .= Craft::$app->view->renderTemplate(
                'articlegenerator/_button',
                ['entry' => $entry],
                View::TEMPLATE_MODE_CP
            );
        });
    }
}
