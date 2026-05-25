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
    'en' => [
      'tw.transcript.area.title' => 'Transcripts',
      'tw.transcript.tab.importer' => 'Importer',
      'tw.transcript.tab.overview' => 'Overview',
      'tw.transcript.tab.settings' => 'Settings',
      'tw.transcript.section.importer' => 'Importer',
      'tw.transcript.section.importFile' => 'Import File',
      'tw.transcript.section.transcript' => 'Transcript Text',
      'tw.transcript.section.actions' => 'Actions',
      'tw.transcript.section.preview' => 'Preview',
      'tw.transcript.section.insert' => 'Insert Into Episode',
      'tw.transcript.section.overview' => 'Overview',
      'tw.transcript.section.settings' => 'Settings',
      'tw.transcript.info.importer' =>
        'Import a TXT or DOTE JSON transcript and review the detected segments before inserting the block into an episode.',
      'tw.transcript.field.file' => 'Import File',
      'tw.transcript.field.transcript' => 'Transcript Text',
      'tw.transcript.field.episodeSearch' => 'Filter Episodes',
      'tw.transcript.field.episode' => 'Episode',
      'tw.transcript.placeholder.file' => 'Drag a TXT or DOTE JSON file here or select one',
      'tw.transcript.placeholder.transcript' => "Speaker\n00:48\nText...",
      'tw.transcript.placeholder.search' => 'Title, ID or number',
      'tw.transcript.message.fileLoaded' => 'Loaded file: {{ fileName }}',
      'tw.transcript.message.previewEmpty' => 'No preview available yet.',
      'tw.transcript.message.previewMore' => '+ {{ count }} more segments',
      'tw.transcript.message.searching' => 'Searching …',
      'tw.transcript.message.overviewLoading' => 'Loading overview …',
      'tw.transcript.message.overviewEmpty' => 'No episodes found.',
      'tw.transcript.message.settingsPending' => 'Settings will follow in the next step.',
      'tw.transcript.action.generatePreview' => 'Generate Preview',
      'tw.transcript.action.checking' => 'Checking …',
      'tw.transcript.action.reset' => 'Reset',
      'tw.transcript.action.insert' => 'Insert Block Into Episode',
      'tw.transcript.action.inserting' => 'Inserting …',
      'tw.transcript.action.openEpisode' => 'Open Episode in Panel',
      'tw.transcript.table.episode' => 'Episode',
      'tw.transcript.table.name' => 'Name',
      'tw.transcript.table.slug' => 'Slug',
      'tw.transcript.table.status' => 'Transcript',
      'tw.transcript.table.noEpisode' => 'No number',
      'tw.transcript.status.available' => 'Transcript available',
      'tw.transcript.status.missing' => 'Transcript missing',
      'tw.transcript.preview.kicker' => 'Transcript',
      'tw.transcript.preview.segments' => '{{ count }} segments',
      'tw.transcript.preview.speakers' => '{{ count }} speakers',
      'tw.transcript.preview.duration' => 'Length: {{ duration }}',
      'tw.transcript.preview.repeat' => 'Repeat names: {{ value }}',
      'tw.transcript.preview.repeat.yes' => 'Yes',
      'tw.transcript.preview.repeat.no' => 'No',
      'tw.transcript.speaker.unknown' => 'Unknown Speaker',
      'tw.transcript.error.readFile' => 'Could not read file.',
      'tw.transcript.error.noTranscript' => 'Please paste a transcript or load a file first.',
      'tw.transcript.error.importFailed' => 'Import failed.',
      'tw.transcript.error.selectEpisode' => 'Please select an episode.',
      'tw.transcript.error.noSegments' => 'No segments available to insert.',
      'tw.transcript.error.insertFailed' => 'Insert failed.',
      'tw.transcript.success.detected' => '{{ count }} segments detected.',
      'tw.transcript.success.inserted' => 'Block inserted into the episode successfully.',
    ],
    'de' => [
      'tw.transcript.area.title' => 'Transkripte',
      'tw.transcript.tab.importer' => 'Import',
      'tw.transcript.tab.overview' => 'Übersicht',
      'tw.transcript.tab.settings' => 'Einstellungen',
      'tw.transcript.section.importer' => 'Import',
      'tw.transcript.section.importFile' => 'Datei importieren',
      'tw.transcript.section.transcript' => 'Transkripttext',
      'tw.transcript.section.actions' => 'Aktionen',
      'tw.transcript.section.preview' => 'Vorschau',
      'tw.transcript.section.insert' => 'In Episode einfügen',
      'tw.transcript.section.overview' => 'Übersicht',
      'tw.transcript.section.settings' => 'Einstellungen',
      'tw.transcript.info.importer' =>
        'Importiere ein TXT- oder DOTE-JSON-Transkript und prüfe die erkannten Abschnitte, bevor du den Block in eine Episode einfügst.',
      'tw.transcript.field.file' => 'Datei importieren',
      'tw.transcript.field.transcript' => 'Transkripttext',
      'tw.transcript.field.episodeSearch' => 'Episoden filtern',
      'tw.transcript.field.episode' => 'Episode',
      'tw.transcript.placeholder.file' => 'TXT- oder DOTE-JSON-Datei hierher ziehen oder auswählen',
      'tw.transcript.placeholder.transcript' => "Speaker\n00:48\nText...",
      'tw.transcript.placeholder.search' => 'Titel, ID oder Nummer',
      'tw.transcript.message.fileLoaded' => 'Geladene Datei: {{ fileName }}',
      'tw.transcript.message.previewEmpty' => 'Noch keine Vorschau vorhanden.',
      'tw.transcript.message.previewMore' => '+ {{ count }} weitere Abschnitte',
      'tw.transcript.message.searching' => 'Suche läuft …',
      'tw.transcript.message.overviewLoading' => 'Übersicht wird geladen …',
      'tw.transcript.message.overviewEmpty' => 'Keine Episoden gefunden.',
      'tw.transcript.message.settingsPending' => 'Einstellungen folgen im nächsten Schritt.',
      'tw.transcript.action.generatePreview' => 'Vorschau erzeugen',
      'tw.transcript.action.checking' => 'Prüfe …',
      'tw.transcript.action.reset' => 'Zurücksetzen',
      'tw.transcript.action.insert' => 'Block in Episode einfügen',
      'tw.transcript.action.inserting' => 'Wird eingefügt …',
      'tw.transcript.action.openEpisode' => 'Folge im Panel öffnen',
      'tw.transcript.table.episode' => 'Episode',
      'tw.transcript.table.name' => 'Name',
      'tw.transcript.table.slug' => 'Slug',
      'tw.transcript.table.status' => 'Transkript',
      'tw.transcript.table.noEpisode' => 'Ohne Nummer',
      'tw.transcript.status.available' => 'Transkript vorhanden',
      'tw.transcript.status.missing' => 'Transkript fehlt',
      'tw.transcript.preview.kicker' => 'Transkript',
      'tw.transcript.preview.segments' => '{{ count }} Abschnitte',
      'tw.transcript.preview.speakers' => '{{ count }} Sprecher',
      'tw.transcript.preview.duration' => 'Länge: {{ duration }}',
      'tw.transcript.preview.repeat' => 'Namen wiederholen: {{ value }}',
      'tw.transcript.preview.repeat.yes' => 'Ja',
      'tw.transcript.preview.repeat.no' => 'Nein',
      'tw.transcript.speaker.unknown' => 'Ohne Sprecher',
      'tw.transcript.error.readFile' => 'Datei konnte nicht gelesen werden.',
      'tw.transcript.error.noTranscript' =>
        'Bitte zuerst ein Transkript einfügen oder eine Datei laden.',
      'tw.transcript.error.importFailed' => 'Import fehlgeschlagen.',
      'tw.transcript.error.selectEpisode' => 'Bitte eine Episode auswählen.',
      'tw.transcript.error.noSegments' => 'Keine Abschnitte zum Einfügen vorhanden.',
      'tw.transcript.error.insertFailed' => 'Einfügen fehlgeschlagen.',
      'tw.transcript.success.detected' => '{{ count }} Abschnitte erkannt.',
      'tw.transcript.success.inserted' => 'Block erfolgreich in die Episode eingefügt.',
    ],
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
