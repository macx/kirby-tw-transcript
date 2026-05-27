# Migration Notes

This plugin is now prepared to be developed as an independent Composer-ready Kirby plugin.

## From the Website Repo to the Plugin Repo

1. Keep the plugin source of truth in this repository.
2. Develop locally in the website repo through a symlinked checkout only.
3. Publish releases from this repository by tagging the plugin version.
4. Update the website repository to require the released Composer version.

## From Manual Install to Composer Install

1. Keep the plugin available at `site/plugins/kirby-tw-transcript/` during development.
2. Add the GitHub VCS repository entry in the consuming project.
3. Require `macx/kirby-tw-transcript` with the beta branch alias or a release tag.
4. Remove the temporary manual copy once Composer resolves the package.

## Release Order

- Tag and publish the plugin first.
- Then update the website repository.
- Then deploy the website.

## Notes

- Do not let the website release define the plugin version.
- Keep the beta line on `0.9.x-dev` until the first stable release.
