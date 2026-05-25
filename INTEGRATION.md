# Integration Checklist

This checklist ensures smooth integration of the Tw Podcast Transcript plugin into your workflow.

## Pre-Integration

- [x] Plugin installed at `site/plugins/kirby-tw-transcript/`
- [x] Kirby 5.x detected (autoloader active)
- [x] composer.json exists with metadata
- [x] All core files present (index.php, blueprints, snippets, API)

## Integration Steps

### 1. Verify Plugin Is Loaded

```bash
cd /Users/macx/Projects/macx/technikwuerze
php -r "require 'kirby/bootstrap.php'; echo 'Plugins: ' . count(kirby()->plugins()) . PHP_EOL;"
```

Expected: Plugin count ≥ 1 (including tw/podcast-transcript)

### 2. Update Episode Blueprint

File: `site/blueprints/pages/episode.yml`

Find the `blocks` field and ensure transcript block reference:

```yaml
# If using field query:
blocks: fields/episode-blocks

# OR directly:
blocks:
  - tw/podcast-transcript/blocks/transcript
  # ... other blocks
```

### 3. Test Panel Area Access

- Open Kirby Panel at `http://localhost:8000/panel`
- Navigate to "Transcript Import" area (if visible)
- Should display import form

### 4. Test Block Creation

- Create/edit an episode page
- Add a "Transcript" block
- Verify fields appear (headline, intro, segments, repeat speaker toggle)

### 5. Test Transcript Upload

- Use panel area to upload example transcript
- Verify parsing works (`/api/transcript/import`)
- Preview should show detected speakers and segments

### 6. Test Block Rendering

- Create transcript block with sample data
- Save episode page
- View on frontend
- Verify markup renders correctly

### 7. Test Player Sync (Optional)

- If Podlove player present on page:
  - Verify timestamp buttons appear
  - Click button → should seek player
  - Verify active state changes with playback

## Troubleshooting

### Plugin not detected

```bash
# Check Kirby plugin discovery
ls -la site/plugins/kirby-tw-transcript/index.php
# Should exist and be readable
```

### "Transcript Import" area not visible

- Clear Panel cache: `site/cache/`
- Refresh browser (force reload)
- Check browser console for JS errors

### Block doesn't render

- Verify blueprint loaded: check `site/blueprints/blocks/` (should include plugin block)
- Check PHP errors: `php -l site/plugins/kirby-tw-transcript/snippets/blocks/transcript.php`

### Import API returns 404

- Verify route registration: debug `extensions/api.php`
- Check kirby() URL: ensure `/api/` prefix works
- Test: `curl http://localhost:8000/api/transcript/import`

## Cleanup (Old Code Removal)

**ONLY after verifying new plugin works:**

1. Delete old transcript block:
   - `site/plugins/technikwuerze/blueprints/blocks/transcript.yml`
   - `site/plugins/technikwuerze/snippets/blocks/transcript.php`

2. Remove old imports from main plugin:
   - `site/plugins/technikwuerze/index.js` (remove transcript block preview)
   - `site/plugins/technikwuerze/assets/transcript.css` (if migrated to new plugin)

3. Update main plugin API (if any transcript endpoints exist):
   - Remove old import logic from `site/plugins/technikwuerze/extensions/api.php`

4. Update site config (if using feature flags):
   - Remove old transcript feature flags

## Rollback Plan

If issues arise:

1. Rename `site/plugins/kirby-tw-transcript/index.php` → `index.php.bak`
2. Clear Panel cache: `rm -rf site/cache/*`
3. Restore old code from git
4. Restart development server

## Post-Integration

- [ ] Document any custom transcript formats used
- [ ] Train team on import panel workflow
- [ ] Set up CI/CD for plugin testing (if applicable)
- [ ] Monitor for issues in production

## Support

See:

- [README.md](./README.md) - Feature overview
- [MIGRATION.md](./MIGRATION.md) - Migration details
- [DEVELOPMENT.md](./DEVELOPMENT.md) - Development guide
- [example-transcript.txt](./example-transcript.txt) - Format reference
