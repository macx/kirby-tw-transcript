# Development Guide

## Quick Start

1. **Plugin Location**: `site/plugins/kirby-tw-transcript/`
2. **Main Entry**: `index.php` (registers all components)
3. **Reload Kirby Panel** to see changes

## File Structure

```
├── blueprints/blocks/     Block schema (YAML)
├── snippets/blocks/       Block rendering (PHP)
├── extensions/
│   ├── area.php           Panel area config
│   └── api.php            API endpoint handlers
├── lib/
│   └── parser.php         Transcript parser class
├── assets/
│   ├── transcript.css     Block styles
│   └── scripts/           TypeScript (future: compiled to JS)
└── index.php              Plugin bootstrap
```

## Development Workflow

### Adding Features

1. **New API Endpoint**: Add to `extensions/api.php`
2. **New Block Field**: Update `blueprints/blocks/transcript.yml`
3. **New Parser Format**: Extend `lib/parser.php` class
4. **New Styles**: Add to `assets/transcript.css` (CSS Nesting scope)

### Code Standards

- **PHP**: PSR-12, no short tags, full type hints
- **Comments**: English only, brief and precise
- **CSS**: Mobile-first, use CSS custom properties (--clr-_, --sp-_, etc.)
- **Timestamps**: Always convert to milliseconds for player sync

### Testing Locally

**Transcript Format** (multiline, TXT):

```
Speaker
MM:SS or HH:MM:SS
Text content

Speaker
MM:SS or HH:MM:SS
Text content
```

**Example**:

```
David
07:08
Ich bedanke mich fürs Zuhören und wünsche euch fröhliche Weihnachten und einen guten Rutsch ins neue Jahr. Bis dahin!

David
07:15
[Musik]

Host
08:00
Thanks for listening!
```

**Expected Parsing**:

```php
[
  [
    'speaker' => 'David',
    'timestamp' => '07:08',
    'text' => 'Ich bedanke mich fürs Zuhören...',
  ],
  [
    'speaker' => 'David',
    'timestamp' => '07:15',
    'text' => '[Musik]',
  ],
  [
    'speaker' => 'Host',
    'timestamp' => '08:00',
    'text' => 'Thanks for listening!',
  ],
];
```

### API Testing

**Import Endpoint** (via curl):

```bash
curl -X POST \
  http://localhost:8000/api/transcript/import \
  -F 'file=@transcript.txt'
```

**Expected Response**:

```json
{
  "status": "ok",
  "segments": [...],
  "count": 3,
  "previewUrl": "..."
}
```

## Common Tasks

### Add a new toggle option to transcript block

1. Edit `blueprints/blocks/transcript.yml`:

```yaml
myNewOption:
  type: toggle
  width: 1/2
```

2. Use in `snippets/blocks/transcript.php`:

```php
$myNewOption = $block->myNewOption()->toBool();
```

### Extend the parser for new timestamp formats

1. Edit `lib/parser.php`, add format detection:

```php
public static function parse(string $txt): array {
  // Add new regex pattern for format detection
  // Use existing segments as fallback
}
```

### Modify transcript styling

1. Edit `assets/transcript.css`
2. Add new rules within `:scope { ... }` block
3. All selectors prefixed with `.tw-transcript-*`

## Performance Considerations

- Parser runs on import (one-time cost)
- Snippet rendering is efficient (no loops on large structures)
- CSS uses GPU-friendly transitions (avoid layout thrashing)
- Podlove sync debounced with `requestAnimationFrame`

## Debugging

**Enable Panel area debugging**:

```php
// In site/config/config.php
'debug' => true,
```

**Check parser output**:

```bash
php -r "
  require_once 'site/plugins/kirby-tw-transcript/lib/parser.php';
  \$parser = new \Tw\Transcript\Parser();
  \$result = \$parser->parse(file_get_contents('test.txt'));
  print_r(\$result);
"
```

## Dependency Notes

- **Kirby**: 5.x (plugin discovered via index.php)
- **PHP**: 8.2+ (strict types, match expressions)
- **Browser**: ES2020+, CSS Nesting support
- **Podlove**: Web Player v5.x (async store-based API)

## Release Readiness

- Composer package name: `macx/kirby-tw-transcript`
- Current release line: `0.9.x-dev` beta branch alias
- Public install path: GitHub VCS now, Packagist later
- Publication steps: update changelog, tag the plugin repo, publish the tag, then update the website repo to that released version
- Release details: see [PUBLISHING.md](./PUBLISHING.md)

## Next Milestones

- [ ] Migrate full TypeScript sync logic from main repo
- [ ] Build panel import form component
- [ ] Add transcript format presets (standardized speaker names, etc.)
- [ ] Publish to Composer registry for reuse
- [ ] Add tests (Pest or PHPUnit)
