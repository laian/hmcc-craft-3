<?php

use craft\elements\Entry;
use craft\helpers\UrlHelper;

function sermonTransformer(Entry $entry) {
    return [
        'title' => $entry->title,
        'videoUrl' => $entry->videoUrl
    ];
}

return [
    'endpoints' => [
        'sermons.json' => [
            'elementType' => Entry::class,
            'criteria' => ['section' => 'sermons'],
            'transformer' => sermonTransformer
        ],
        'news/<entryId:\d+>.json' => function($entryId) {
            return [
                'elementType' => Entry::class,
                'criteria' => ['id' => $entryId],
                'one' => true,
                'transformer' => function(Entry $entry) {
                    return [
                        'title' => $entry->title,
                        'url' => $entry->url,
                        'summary' => $entry->summary,
                        'body' => $entry->body,
                    ];
                },
            ];
        },
    ]
];