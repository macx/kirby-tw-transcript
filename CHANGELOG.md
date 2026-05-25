# Changelog

All notable changes to the Tw Podcast Transcript plugin will be documented in this file.

## [1.0.0] - 2026-05-25

### Added
- Initial plugin release with transcript block (blueprint, snippet, CSS)
- Panel area for transcript import (TXT upload or paste)
- Transcript parser with timestamp and speaker extraction
- API endpoints for import, parsing, and preview
- Support for global speaker display in single-speaker transcripts
- Timestamp button sync with Podlove Web Player v5
- Glitch guard for false jumps during seek operations
- Full audio sync logic with manual seek detection

### Changed
- Extracted transcript block from main technikwuerze plugin
- Consolidated transcript/audio sync logic into dedicated plugin
- Updated block rendering to use English labels where applicable

### Fixed
- Redundant speaker names in single-speaker transcripts (global display option)
- False jump to end of track on manual segment seek

## Migration Notes

See [MIGRATION.md](./MIGRATION.md) for integration instructions.

---

Format based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
