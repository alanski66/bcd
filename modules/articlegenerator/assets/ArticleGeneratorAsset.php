<?php
namespace modules\articlegenerator\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ArticleGeneratorAsset extends AssetBundle
{
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';
        $this->depends    = [CpAsset::class];
        $this->js         = ['article-generator.js'];

        parent::init();
    }
}
