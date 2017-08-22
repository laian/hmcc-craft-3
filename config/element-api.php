<?php

use craft\elements\Entry;
use craft\helpers\UrlHelper;

function sermonTransformer(Entry $entry) {
    $person = Entry::find()->section('people')->relatedTo($entry)->all()[0];

    return [
        'title' => $entry->title,
        'videoUrl' => $entry->videoUrl,
        'personType' => gettype($person),  
        'person' => [
            'name' => $person->title,
            'photo' => $person->photo
        ]
    ];
}

return [
    'endpoints' => [
        'api/sermons.json' => [
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