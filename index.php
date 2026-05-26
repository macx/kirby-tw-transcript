<?php
/**
 * Kirby TW Transcript plugin for Kirby CMS.
 *
 * @package Tw\Transcript
 * @license MIT
 */

@include_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/lib/parser.php';

Kirby::plugin('tw/transcript', [
  'areas' => require __DIR__ . '/extensions/area.php',

  'blueprints' => [
    'blocks/tw-transcript' => __DIR__ . '/blueprints/blocks/tw-transcript.yml',
  ],

  'translations' => [
    'en' => require __DIR__ . '/i18n/en.php',
    'de' => require __DIR__ . '/i18n/de.php',
  ],

  'panel' => [
    'js' => 'index.js',
  ],

  'api' => require __DIR__ . '/extensions/api.php',

  'snippets' => [
    'blocks/tw-transcript' => __DIR__ . '/snippets/blocks/tw-transcript.php',
  ],

  'options' => [
    'maxUploadSize' => 5 * 1024 * 1024, // 5MB for transcript files
    'timestampMode' => 'normalize', // 'normalize' or 'strict'
  ],

  'hooks' => [
    'page.update:after' => static function () {
      kirby()->cache('tw-transcript')->flush();
    },
    'page.create:after' => static function () {
      kirby()->cache('tw-transcript')->flush();
    },
  ],
]);
