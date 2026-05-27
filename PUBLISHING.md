# Publishing Guide

This plugin is prepared as a beta Kirby plugin that can be consumed either directly from GitHub via Composer VCS or later from Packagist.

## Package Identity

- Composer package: `macx/kirby-tw-transcript`
- Kirby plugin name: `tw/transcript`
- Current beta branch alias: `0.9.x-dev`

## Current Distribution Path

Use the GitHub repository as the source of truth until Packagist is enabled.

```bash
composer config repositories.kirby-tw-transcript vcs https://github.com/macx/kirby-tw-transcript
composer require macx/kirby-tw-transcript:"0.9.x-dev"
```

## Planned Public Release Path

When the beta is stable enough:

1. Update `CHANGELOG.md` with the next version entry.
2. Merge the change into `main`.
3. Tag the plugin repository with the release version.
4. Push the tag to GitHub.
5. Publish or update the package on Packagist.
6. Update the website repository to require the released tag or semver range.

## Release Rules

- Do not release the website before the plugin tag exists.
- Keep beta changes on the `0.9.x-dev` line until the first stable 1.0.0 release.
- Use the changelog as the human-readable source for what changed between tags.

## Website Integration

The website should only reference the plugin through Composer or a local symlink during development.
The production deployment should never depend on an untagged plugin revision.
