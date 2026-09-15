<?php

test('translation files contain valid UTF-8 JSON', function () {
    foreach (glob(dirname(__DIR__, 2).'/lang/*.json') as $path) {
        $translations = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        expect($translations)->toBeArray();
        foreach ($translations as $value) {
            expect($value)->toBeString();
        }
    }
});
