# wirebug

Drop-in floating bug/feedback report widget for Laravel apps. A small floating button opens a modal where the user picks a type (bug, suggestion, other) and writes a message. Reports are stored in your database with automatic technical context (URL, user agent, viewport, locale, authenticated user).

Part of the `wire*` family: themeable through the shared `data-wire-theme` attribute, visually coherent with [wiremodal](https://github.com/edulazaro/wiremodal), [wiretoast](https://github.com/edulazaro/wiretoast) and [wirecookies](https://github.com/edulazaro/wirecookies).

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Alpine.js on the page
- [edulazaro/wiremodal](https://github.com/edulazaro/wiremodal) 1.1+ (pulled automatically; its JS must be loaded)

## Installation

```bash
composer require edulazaro/wirebug
php artisan migrate
```

Import the CSS after wiremodal in your bundle:

```css
@import 'edulazaro/wiremodal/resources/css/wiremodal.css';
@import '../../vendor/edulazaro/wirebug/resources/css/wirebug.css';
```

## Usage

Drop the component in any layout (typically the logged-in app shell):

```blade
<x-wirebug />
```

Pick the theme once on `<html>` (shared with the rest of the family):

```html
<html data-wire-theme="studio">
```

### What the user writes

- **Type**: bug / suggestion / other (configurable via `config('wirebug.types')`).
- **Message**: free text, required, max 5000 chars.
- **Steps to reproduce**: optional free text.
- **Screenshot**: optional image attachment (jpg/png/gif/webp, 5MB max by default).
- **Screen recording**: optional, via the browser's native `getDisplayMedia` + `MediaRecorder` (nothing records until the user clicks; zero background cost). While recording, the modal closes and a small in-app overlay shows a timer with Stop/Discard; on stop the modal reopens with the video attached (WebM/MP4, 90s / 50MB max by default, bitrate capped at 2 Mbps via `recording.bitrate`, `config('wirebug.recording')`). The button only appears on supporting browsers (desktop). The recording lives in the page's JS context: it survives SPA-style navigation (`wire:navigate`), a hard reload kills it.
- **Email**: optional, only shown to guests (authenticated users are already identified). Disable with `'ask_guest_email' => false`.

### What is captured automatically

URL, user agent, viewport, locale, the authenticated reporter (polymorphic `reporter_type`/`reporter_id`, morph-map aware) and referer. Disable with `'capture_context' => false`.

### Where everything is stored

Reports live in the `wirebug_reports` table of your app's database. The screenshot is stored through Laravel's `Storage` on the disk set in `config('wirebug.uploads.disk')` — any disk from your `config/filesystems.php` works the same (`local` server filesystem, `s3`, an S3-compatible R2 disk...). The report row keeps only the file path.

### Reading the reports

```php
use EduLazaro\WireBug\Models\BugReport;

BugReport::new()->latest()->get();   // status = 'new'
BugReport::ofType('bug')->get();
$report->reporter;                    // polymorphic: who sent it (User, Client... or null for guests)
```

### Component props

```blade
<x-wirebug position="right" title="Feedback" />

{{-- Edge tab instead of the floating button --}}
<x-wirebug trigger="tab" position="bottom-right" tab-label="Feedback" />
```

Every visible string can be overridden by prop or via the published translations. Two trigger styles:

- `trigger="button"` (default): floating square button. `position` accepts `left` (default) or `right` (always bottom corner).
- `trigger="tab"`: small labeled tab stuck flush to the screen edge. `position` accepts `bottom-right` (default), `bottom-left`, `top-right` or `top-left`.

### Config

```bash
php artisan vendor:publish --tag=wirebug-config
```

Route path/middleware (add `auth` if the widget only lives behind login), button position, report types, guest email, context capture and table name.

### Publishing assets

```bash
php artisan vendor:publish --tag=wirebug-views
php artisan vendor:publish --tag=wirebug-css
php artisan vendor:publish --tag=wirebug-lang
php artisan vendor:publish --tag=wirebug-migrations
```

## Events

- `wirebug-sent` is dispatched on `window` after a successful submit, in case the host app wants to toast or track it.

## License

MIT © Edu Lázaro
