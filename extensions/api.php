<?php

require_once dirname(__DIR__) . '/lib/parser.php';

$importAction = function () {
  $request = kirby()->request();
  $files = $request->files();
  $uploadedFile = $files->get('file');
  $data = $request->data();
  $body = $request->body();

  $transcript = null;

  // Get transcript from file upload or POST body
  if (
    is_array($uploadedFile) &&
    !empty($uploadedFile['tmp_name']) &&
    is_uploaded_file($uploadedFile['tmp_name'])
  ) {
    $transcript = file_get_contents($uploadedFile['tmp_name']);
  } elseif (is_array($data) && !empty($data['transcript']) && is_string($data['transcript'])) {
    $transcript = $data['transcript'];
  } elseif (!empty($body)) {
    if (is_string($body)) {
      $decoded = json_decode($body, true);
      if (
        is_array($decoded) &&
        !empty($decoded['transcript']) &&
        is_string($decoded['transcript'])
      ) {
        $transcript = $decoded['transcript'];
      } else {
        $transcript = $body;
      }
    }
  }

  if (empty($transcript)) {
    return [
      'status' => 'error',
      'message' => 'No transcript provided (file upload or text paste required)',
    ];
  }

  $normalizeJsonTimestamp = static function (string $value): string {
    $value = trim($value);
    if ($value === '') {
      return '';
    }

    if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})(?:[,.]\d+)?$/', $value, $match) === 1) {
      $hours = (int) $match[1];
      $minutes = (int) $match[2];
      $seconds = (int) $match[3];

      if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
      }

      return sprintf('%02d:%02d', $minutes, $seconds);
    }

    if (preg_match('/^(\d{1,2}):(\d{2})(?:[,.]\d+)?$/', $value, $match) === 1) {
      return sprintf('%02d:%02d', (int) $match[1], (int) $match[2]);
    }

    return '';
  };

  $segments = [];

  // Accept DOTE transcript JSON line format:
  // { "lines": [{ "startTime": "00:00:00,001", "speakerDesignation": "...", "text": "..." }] }
  $decodedTranscript = json_decode(trim((string) $transcript), true);
  if (
    is_array($decodedTranscript) &&
    isset($decodedTranscript['lines']) &&
    is_array($decodedTranscript['lines'])
  ) {
    foreach ($decodedTranscript['lines'] as $line) {
      if (!is_array($line)) {
        continue;
      }

      $speaker = trim((string) ($line['speakerDesignation'] ?? ($line['speaker'] ?? '')));
      $timestamp = $normalizeJsonTimestamp(
        (string) ($line['startTime'] ?? ($line['startTie'] ?? '')),
      );
      $text = trim((string) ($line['text'] ?? ''));

      if ($text === '') {
        continue;
      }

      $segments[] = [
        'speaker' => $speaker,
        'timestamp' => $timestamp,
        'text' => $text,
      ];
    }
  }

  if (empty($segments)) {
    // Parse transcript using the Parser class
    // Expects format: Speaker\nTimestamp\nText\n\n (multiline, blank-line separated)
    $parser = new \Tw\Transcript\Parser();
    $segments = $parser->parse($transcript);
  }

  if (empty($segments)) {
    return [
      'status' => 'error',
      'message' =>
        'No valid transcript segments found. Use either text format (Speaker\nTimestamp\nText) or DOTE JSON format with lines/startTime/speakerDesignation/text.',
    ];
  }

  return [
    'status' => 'ok',
    'segments' => $segments,
    'count' => count($segments),
    'previewUrl' => kirby()->url('api') . '/tw-transcript/import',
  ];
};

$episodeOverviewAction = function () {
  if (!kirby()->user()) {
    return ['status' => 'error', 'message' => 'Unauthorized'];
  }

  $query = trim((string) kirby()->request()->get('q', ''));
  $cache = kirby()->cache('tw-transcript');
  $cacheKey = 'overview:' . md5(mb_strtolower($query));
  $cached = $cache->get($cacheKey);

  if (is_array($cached) === true) {
    return $cached;
  }

  $mediathek = site()->find('mediathek');

  if (!$mediathek) {
    return ['status' => 'error', 'message' => 'Mediathek page not found'];
  }

  $episodeMatches = [];

  $allEpisodes = $mediathek->index()->filterBy('intendedTemplate', 'episode')->published();

  foreach ($allEpisodes as $episode) {
    $episodeId = (string) $episode->id();
    $title = trim((string) $episode->title()->value());
    $slug = trim((string) $episode->slug());
    $dateTimestamp = 0;
    $dateField = $episode->content()->get('Date');
    if ($dateField->isNotEmpty()) {
      $dateTimestamp = (int) ($dateField->toDate() ?? 0);
    }
    if ($dateTimestamp <= 0) {
      $dateTimestamp = (int) $episode->modified();
    }

    $parentSlug = (string) $episode->parent()->slug();
    $seasonNumber = null;
    $seasonHaystack = $episodeId . ' ' . $parentSlug;
    if (
      preg_match('/(?:^|[\/_\-\s])s0*(\d+)(?:$|[\/_\-\s])/i', $seasonHaystack, $seasonMatch) === 1
    ) {
      $seasonNumber = (int) $seasonMatch[1];
    } elseif (preg_match('/staffel[\-_\s]*0*(\d+)/i', $seasonHaystack, $seasonMatch) === 1) {
      $seasonNumber = (int) $seasonMatch[1];
    }

    if ($seasonNumber !== null && $seasonNumber < 1) {
      $seasonNumber = null;
    }

    $episodeNumberRaw = trim((string) $episode->content()->get('Podcasterepisode')->value());
    if ($episodeNumberRaw === '') {
      $episodeNumberRaw = trim((string) $episode->content()->get('podcasterepisode')->value());
    }
    if ($episodeNumberRaw === '') {
      $episodeNumberRaw = trim((string) $episode->content()->get('Podcasterepisodetotal')->value());
    }
    if ($episodeNumberRaw === '') {
      $episodeNumberRaw = trim((string) $episode->content()->get('podcasterepisodetotal')->value());
    }

    $episodeNumber = null;
    if ($episodeNumberRaw !== '' && ctype_digit($episodeNumberRaw)) {
      $episodeNumber = (int) $episodeNumberRaw;
    } elseif (preg_match('/tw\s*(\d+)/i', $slug, $episodeMatch) === 1) {
      $episodeNumber = (int) $episodeMatch[1];
    } elseif (preg_match('/(?:^|[_-])(\d{1,4})(?:[_-]|$)/', $slug, $episodeMatch) === 1) {
      $episodeNumber = (int) $episodeMatch[1];
    }

    if ($query !== '') {
      $matches =
        mb_stripos($title, $query) !== false ||
        mb_stripos($slug, $query) !== false ||
        mb_stripos($episodeId, $query) !== false ||
        ($episodeNumber !== null && mb_stripos((string) $episodeNumber, $query) !== false);

      if ($matches === false) {
        continue;
      }
    }

    $episodeMatches[] = [
      'page' => $episode,
      'id' => $episodeId,
      'title' => $title,
      'slug' => $slug,
      'seasonNumber' => $seasonNumber,
      'episodeNumber' => $episodeNumber,
      'sortTimestamp' => $dateTimestamp,
    ];
  }

  usort($episodeMatches, static function (array $a, array $b): int {
    $timestampA = (int) ($a['sortTimestamp'] ?? 0);
    $timestampB = (int) ($b['sortTimestamp'] ?? 0);

    if ($timestampA === $timestampB) {
      return strcmp((string) ($b['id'] ?? ''), (string) ($a['id'] ?? ''));
    }

    return $timestampB <=> $timestampA;
  });

  if ($query === '') {
    $episodeMatches = array_slice($episodeMatches, 0, 10);
  }

  $episodes = [];

  foreach ($episodeMatches as $episodeMatch) {
    $page = $episodeMatch['page'] ?? null;
    $blockField = $page ? (string) $page->content()->get('blocks')->value() : '';
    $blocks = $blockField !== '' ? json_decode($blockField, true) : [];
    $hasTranscriptBlock = false;

    if (is_array($blocks)) {
      foreach ($blocks as $block) {
        $type = strtolower(trim((string) ($block['type'] ?? '')));
        if ($type === 'tw-transcript') {
          $hasTranscriptBlock = true;
          break;
        }
      }
    }

    unset($episodeMatch['page']);

    $episodes[] = $episodeMatch + [
      'hasTranscriptBlock' => $hasTranscriptBlock,
      'panelUrl' => $page ? $page->panel()->url() : '',
    ];
  }

  $response = [
    'status' => 'ok',
    'episodes' => $episodes,
  ];

  $cache->set($cacheKey, $response, 300);

  return $response;
};

$insertAction = function () {
  if (!kirby()->user()) {
    return ['status' => 'error', 'message' => 'Unauthorized'];
  }

  $request = kirby()->request();
  $data = $request->data();
  $pageId = is_array($data) ? $data['pageId'] ?? '' : '';
  $segments = is_array($data) ? $data['segments'] ?? [] : [];

  if (empty($pageId) || !is_array($segments) || count($segments) === 0) {
    return ['status' => 'error', 'message' => 'Missing pageId or segments'];
  }

  $episode = kirby()->page($pageId);

  if (!$episode) {
    return ['status' => 'error', 'message' => 'Episode not found'];
  }

  // Build the new transcript block value
  $newBlock = [
    'type' => 'tw-transcript',
    'id' => \Kirby\Toolkit\Str::uuid(),
    'isHidden' => false,
    'content' => [
      'headline' => 'Transkript',
      'segments' => $segments,
    ],
  ];

  // Write to changes version so the editor still needs to save manually in Panel
  kirby()->impersonate('kirby', function () use ($episode, $newBlock) {
    $language = \Kirby\Cms\Language::ensure('current');
    $changes = $episode->version('changes');
    $latest = $episode->version('latest');

    $source = $changes->exists($language) ? $changes : $latest;
    $content = $source->content($language)->toArray();

    $existingRaw = $content['blocks'] ?? '';
    $blocks = $existingRaw ? json_decode($existingRaw, true) : [];
    if (!is_array($blocks)) {
      $blocks = [];
    }

    $blocks[] = $newBlock;
    $content['blocks'] = json_encode($blocks);

    $changes->save($content, $language);
  });

  return [
    'status' => 'ok',
    'message' => 'Block als ungespeicherte Aenderung eingefuegt',
    'panelUrl' => $episode->panel()->url(),
  ];
};

return [
  'routes' => [
    [
      'pattern' => 'tw-transcript/import',
      'method' => 'POST',
      'action' => $importAction,
    ],
    [
      'pattern' => 'tw-transcript/episodes',
      'method' => 'GET',
      'action' => $episodeOverviewAction,
    ],
    [
      'pattern' => 'tw-transcript/overview',
      'method' => 'GET',
      'action' => $episodeOverviewAction,
    ],
    [
      'pattern' => 'tw-transcript/insert',
      'method' => 'POST',
      'action' => $insertAction,
    ],

    [
      'pattern' => 'transcript/import',
      'method' => 'POST',
      'action' => $importAction,
    ],
    [
      'pattern' => 'transcript/episodes',
      'method' => 'GET',
      'action' => $episodeOverviewAction,
    ],
    [
      'pattern' => 'transcript/insert',
      'method' => 'POST',
      'action' => $insertAction,
    ],
  ],
];
