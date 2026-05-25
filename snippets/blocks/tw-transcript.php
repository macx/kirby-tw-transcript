<?php
/**
 * Transcript block snippet for Kirby TW Transcript plugin.
 *
 * @var Kirby\Cms\Block $block
 */

$headline = trim((string) $block->headline()->value());
$intro = trim((string) $block->intro()->value());
$segments = $block->segments()->toStructure();
$repeatSpeakerPerSegment = $block->repeatSpeakerPerSegment()->toBool();

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
<section class="tw-transcript" aria-labelledby="<?= $headline !== ''
  ? esc($block->id(), 'attr') . '-headline'
  : esc($block->id(), 'attr') . '-segments' ?>">
  <div class="tw-transcript__wrapper">
    <?php if ($headline !== ''): ?>
      <h2 class="tw-transcript__headline" id="<?= esc($block->id(), 'attr') ?>-headline"><?= esc($headline) ?></h2>
    <?php endif; ?>

    <?php if ($intro !== ''): ?>
      <div class="tw-transcript__intro tw-transcript-intro"><?= $block->intro()->kt() ?></div>
    <?php endif; ?>

    <ol class="tw-transcript__segments tw-transcript-segments" id="<?= esc($block->id(), 'attr') ?>-segments">
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
        <li class="tw-transcript__segment tw-transcript-segment">
          <article class="tw-transcript__segment-item"<?= $articleAriaLabel !== '' ? ' aria-label="' . esc($articleAriaLabel, 'attr') . '"' : '' ?>>
            <?php if ($showSpeakerInSegment || $timestamp !== ''): ?>
              <header class="tw-transcript__segment-meta tw-transcript-segment-meta">
                <?php if ($showSpeakerInSegment): ?>
                  <span class="tw-transcript__speaker tw-transcript-speaker"><?= esc($speaker) ?></span>
                <?php endif; ?>

                <?php if ($timestamp !== ''): ?>
                  <button
                    type="button"
                    class="tw-transcript__timestamp tw-transcript-timestamp"
                    data-timestamp="<?= $timestampMs ?>"
                    aria-label="Jump to <?= $labelSpeaker !== ''
                      ? esc($labelSpeaker . ' at ' . $timestamp, 'attr')
                      : esc($timestamp, 'attr') ?>"
                  >
                    <span class="tw-transcript__timestamp-icon tw-transcript-timestamp-icon" aria-hidden="true"></span>
                    <span class="tw-transcript__timestamp-time tw-transcript-timestamp-time"><?= esc($timestamp) ?></span>
                  </button>
                <?php endif; ?>
              </header>
            <?php endif; ?>

            <?php if ($text !== ''): ?>
              <div class="tw-transcript__content tw-transcript-content"><?= $segment->text()->kt() ?></div>
            <?php endif; ?>
          </article>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
