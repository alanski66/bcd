<?php
namespace modules\fetchprofile\assets;

use craft\web\AssetBundle;

class SiteAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================
    public function init(): void
    {
        $this->sourcePath = '@fetch-profile/assets';

        $this->js = [
            'js/site.js',
        ];

        $this->css = [
            'css/site.css',
        ];

        parent::init();
    }
}
