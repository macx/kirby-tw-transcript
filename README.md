# Tw Podcast Transcript

A Kirby plugin for convenient import, management, and audio sync of podcast transcripts.

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
- Or install via Composer (recommended):

```bash
composer require technikwuerze/podcast-transcript
```

## Quickstart

1. In Kirby Panel: Go to "Transcript Import" area
2. Upload TXT file or paste transcript text
3. Review preview, select target episode, import
4. Transcript saved as block and synced with player

## License

MIT
