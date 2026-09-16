# wp-dark-plugin

WordPress plugin: always-dark post editor. Darkens the admin chrome and the block-editor canvas on post edit screens only (`post.php` / `post-new.php`, post type `post`). Does nothing on pages, on the front end, or in the classic editor.

## Settings

Settings link appears next to Activate/Deactivate on the Plugins page (also under Settings → Dark Editor). Pick a background and accent colour; surfaces, borders, and code shades are derived automatically, and text colour flips light/dark based on background luminance. The Everywhere toggle applies the dark theme to the entire WordPress admin (all screens, all post types) instead of just the post editor.

## Oxygen Builder

With Everywhere on, Oxygen Builder admin screens (home, import/export, stylesheet list, tooltips) are darkened too, using the same derived palette. Oxygen template previews intentionally keep the public (light) look.

## Install

Copy the folder to `wp-content/plugins/` and activate in the WP admin.

## Files

- `wp-dark-plugin.php` — loader, settings page, palette derivation, canvas stylesheet generation
- `admin-editor.css` — editor chrome, CSS-variable driven (post editor screens, or everywhere when toggled)
- `admin-global.css` — whole-admin dark styles, loaded only when Everywhere is on
- `canvas.php` — dynamic stylesheet injected into the editor iframe; scope depends on the Everywhere toggle
