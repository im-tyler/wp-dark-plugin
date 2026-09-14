# wp-dark-plugin

WordPress plugin: always-dark post editor. Darkens the admin chrome and the block-editor canvas on post edit screens only (`post.php` / `post-new.php`, post type `post`). Does nothing on pages, on the front end, or in the classic editor.

## Settings

Settings link appears next to Activate/Deactivate on the Plugins page (also under Settings → Dark Editor). Pick a background and accent colour; surfaces, borders, and code shades are derived automatically, and text colour flips light/dark based on background luminance.

## Install

Copy the folder to `wp-content/plugins/` and activate in the WP admin.

## Files

- `wp-dark-plugin.php` — loader, settings page, palette derivation
- `admin-editor.css` — admin chrome, CSS-variable driven (post editor screens only)
- `canvas.css` — writing canvas, injected into the editor iframe, scoped to `body.post-type-post`
- `canvas-vars.php` — dynamic endpoint serving the palette vars into the editor iframe
