<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => '@symfony/stimulus-bundle/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'tom-select/dist/css/tom-select.bootstrap5.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'tom-select' => [
        'version' => '2.4.3',
    ],
    'vanillajs-datepicker' => [
        'version' => '1.3.4',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'vanillajs-datepicker/dist/css/datepicker-bs5.min.css' => [
        'version' => '1.3.4',
        'type' => 'css',
    ],
    'vanillajs-datepicker/locales/fr' => [
        'version' => '1.3.4',
    ],
    'htmx.org' => [
        'version' => '2.0.4',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    '@ymlluo/bs5dialog/dist/bs5dialog.css' => [
        'version' => '1.0.11',
        'type' => 'css',
    ],
    '@ymlluo/bs5dialog/dist/bs5dialog.js' => [
        'version' => '1.0.11',
    ],
    'push.js' => [
        'version' => '1.0.12',
    ],
    'bracketry' => [
        'version' => '1.1.3',
    ],
    '@orchidjs/sifter' => [
        'version' => '1.1.0',
    ],
    '@orchidjs/unicode-variants' => [
        'version' => '1.1.2',
    ],
    'tom-select/dist/css/tom-select.default.min.css' => [
        'version' => '2.4.3',
        'type' => 'css',
    ],
    'htmx-ext-sse' => [
        'version' => '2.2.3',
    ],
];
