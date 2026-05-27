# Tw Podcast Transcript

Beta Kirby plugin for convenient import, management, and audio sync of podcast transcripts.

## Status

This plugin currently tracks the beta release line and is prepared for Composer-based installation.
The Git branch alias is `0.9.x-dev`, so the package can be consumed before the first stable 1.0 tag.

## Features

- Multiline transcript format (Speaker → Timestamp → Text)
- Panel importer (TXT upload or paste)
- Preview with validation
- Block integration for any episode
- Audio sync with Podlove Web Player v5

## Transcript Format

Simple multiline format with blank line separators:

```
Speaker Name
MM:SS or HH:MM:SS
Transcript text content

Next Speaker
MM:SS or HH:MM:SS
More text content
```

**Rules:**

- Line 1: Speaker name
- Line 2: Timestamp (MM:SS or HH:MM:SS) – optional
- Line 3+: Text (can span multiple lines)
- Blank line: Segment separator

## Installation

- Unzip to `site/plugins/kirby-tw-transcript`
- Or install via Composer from GitHub during beta:

```bash
composer config repositories.kirby-tw-transcript vcs https://github.com/macx/kirby-tw-transcript
composer require macx/kirby-tw-transcript:"0.9.x-dev"
```

- Or install via Composer once published to Packagist:

```bash
composer require macx/kirby-tw-transcript:"^0.9@beta"
```

## Quickstart

1. In Kirby Panel: Go to "Transcript Import" area
2. Upload TXT file or paste transcript text
3. Review preview, select target episode, import
4. Transcript saved as block and synced with player

## Release Flow

See [PUBLISHING.md](./PUBLISHING.md) for the exact beta, tagging, GitHub, and Composer release flow.

## License

MIT
