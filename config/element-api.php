<?php

use craft\elements\Entry;
use craft\elements\Category;
use craft\helpers\UrlHelper;

return [
    'endpoints' => [
        'issues.json' => function() {
            return [
                'elementType' => Entry::class,
                'criteria' => ['section' => 'issues'],
                'transformer' => function(Entry $entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                        'url' => $entry->url,
                        'jsonUrl' => UrlHelper::url("news/$entry->id.json"),
                        'intro' => $entry->intro,
                    ];
                },
            ];
        },
        'categories.json' => function() {
            return [
                'elementType' => Category::class,
                'criteria' => [
                    'group' => 'issues',
                    'orderBy'   => 'title asc'
                ],
                'transformer' => function(Category $category) {
                    return [
                        'id' => $category->id,
                        'value' => $category->title
                    ];
                },
            ];
        },
        'articles/<entryId:\d+>.json' => function($entryId) {
            return [
                'elementType' => Entry::class,
                'criteria' => ['id' => $entryId],
                'one' => true,
                'transformer' => function(Entry $entry) {
                    return [
                        'title' => $entry->title,
                        'url' => $entry->url,
                        'intro' => $entry->intro,
                        'body' => $entry->body,
                    ];
                },
            ];
        },
    ]
];
