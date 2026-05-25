<?php

$twTranscriptTabs = static function (string $active): array {
  return [
    [
      'name' => 'importer',
      'label' => t('tw.transcript.tab.importer', 'Importer'),
      'link' => '/tw-transcript/importer',
    ],
    [
      'name' => 'overview',
      'label' => t('tw.transcript.tab.overview', 'Overview'),
      'link' => '/tw-transcript/overview',
    ],
    [
      'name' => 'settings',
      'label' => t('tw.transcript.tab.settings', 'Settings'),
      'link' => '/tw-transcript/settings',
    ],
  ];
};

return [
  'tw-transcript' => function () use ($twTranscriptTabs): array {
    return [
      'label' => t('tw.transcript.area.title', 'Transcripts'),
      'icon' => 'audio',
      'menu' => true,
      'link' => 'tw-transcript/importer',
      'views' => [
        [
          'pattern' => 'tw-transcript/importer',
          'action' => function () use ($twTranscriptTabs): array {
            return [
              'component' => 'k-tw-transcript-importer-view',
              'title' => t('tw.transcript.area.title', 'Transcripts'),
              'props' => [
                'tab' => 'importer',
                'tabs' => $twTranscriptTabs('importer'),
              ],
            ];
          },
        ],
        [
          'pattern' => 'tw-transcript/overview',
          'action' => function () use ($twTranscriptTabs): array {
            return [
              'component' => 'k-tw-transcript-overview-view',
              'title' => t('tw.transcript.area.title', 'Transcripts'),
              'props' => [
                'tab' => 'overview',
                'tabs' => $twTranscriptTabs('overview'),
              ],
            ];
          },
        ],
        [
          'pattern' => 'tw-transcript/settings',
          'action' => function () use ($twTranscriptTabs): array {
            return [
              'component' => 'k-tw-transcript-settings-view',
              'title' => t('tw.transcript.area.title', 'Transcripts'),
              'props' => [
                'tab' => 'settings',
                'tabs' => $twTranscriptTabs('settings'),
              ],
            ];
          },
        ],
      ],
    ];
  },
];
