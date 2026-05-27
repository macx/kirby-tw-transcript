<?php

$headline = trim((string) $block->headline()->value());
$intro = trim((string) $block->intro()->value());
$segments = $block->segments()->toStructure();
$repeatSpeakerPerSegment = $block->repeatSpeakerPerSegment()->toBool();
$isOpen = $block->initialState()->toBool();

if ($segments->isEmpty()) {
  return;
}

$timestampToMs = static function (string $value): int {
  $value = trim($value);
  if ($value === '' || preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $matches) !== 1) {
    return 0;
  }

  if (isset($matches[3]) && $matches[3] !== '') {
    return ((int) $matches[1] * 3600 + (int) $matches[2] * 60 + (int) $matches[3]) * 1000;
  }

  return ((int) $matches[1] * 60 + (int) $matches[2]) * 1000;
};

$uniqueSpeakers = [];
foreach ($segments as $segment) {
  $speaker = trim((string) $segment->speaker()->value());
  if ($speaker === '') {
    continue;
  }

  if (!in_array($speaker, $uniqueSpeakers, true)) {
    $uniqueSpeakers[] = $speaker;
  }
}

$singleSpeakerName = count($uniqueSpeakers) === 1 ? $uniqueSpeakers[0] : '';
$hideRepeatedSingleSpeaker = $singleSpeakerName !== '' && $repeatSpeakerPerSegment === false;
$segmentIndex = 0;
?>
<details class="tw-transcript" <?= $isOpen ? 'open' : '' ?> aria-labelledby="<?= $headline !== ''
   ? esc($block->id(), 'attr') . '-headline'
   : esc($block->id(), 'attr') . '-segments' ?>">
  <summary>
    <?php if ($headline !== ''): ?>
      <h2 id="<?= esc($block->id(), 'attr') ?>-headline">
        <?= esc($headline) ?>
      </h2>
    <?php else: ?>
      <h2 id="<?= esc($block->id(), 'attr') ?>-headline">
        <?= t('tw.transcript.block.name') ?>
      </h2>
    <?php endif; ?>
  </summary>

  <div class="wrapper">
    <?php if ($intro !== ''): ?>
      <div class="intro"><?= $block->intro()->kt() ?></div>
    <?php endif; ?>

    <ol class="segments" id="<?= esc($block->id(), 'attr') ?>-segments">
      <?php foreach ($segments as $segment): ?>
        <?php
        $speaker = trim((string) $segment->speaker()->value());
        $timestamp = trim((string) $segment->timestamp()->value());
        $timestampMs = $timestampToMs($timestamp);
        $text = trim((string) $segment->text()->value());

        if ($speaker === '' && $timestamp === '' && $text === '') {
          continue;
        }

        $segmentIndex++;
        $labelSpeaker = $speaker !== '' ? $speaker : $singleSpeakerName;
        $showSpeakerInSegment = $speaker !== '';
        if ($hideRepeatedSingleSpeaker) {
          $showSpeakerInSegment = $speaker !== '' && $segmentIndex === 1;
        }

        $articleAriaLabel = '';
        if ($showSpeakerInSegment === false && $labelSpeaker !== '') {
          $articleAriaLabel = 'Speaker: ' . $labelSpeaker;
        }
        ?>
        <li class="segment">
          <article<?= $articleAriaLabel !== ''
            ? ' aria-label="' . esc($articleAriaLabel, 'attr') . '"'
            : '' ?>>
            <?php if ($showSpeakerInSegment || $timestamp !== ''): ?>
              <header class="meta">
                <?php if ($timestamp !== ''): ?>
                  <button
                    type="button"
                    class="timestamp"
                    data-timestamp="<?= $timestampMs ?>"
                    aria-label="Jump to <?= $labelSpeaker !== ''
                      ? esc($labelSpeaker . ' at ' . $timestamp, 'attr')
                      : esc($timestamp, 'attr') ?>"
                  >
                    <span class="icon" aria-hidden="true"></span>
                    <span class="time"><?= esc($timestamp) ?></span>
                  </button>
                <?php endif; ?>

                <?php if ($showSpeakerInSegment): ?>
                  <span class="speaker"><?= esc($speaker) ?></span>
                <?php endif; ?>

              </header>
            <?php endif; ?>

            <?php if ($text !== ''): ?>
              <div class="content"><?= $segment->text()->kt() ?></div>
            <?php endif; ?>
          </article>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</details>
