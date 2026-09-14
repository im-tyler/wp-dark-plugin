# wp-dark-plugin

WordPress plugin: always-dark post editor. Darkens the admin chrome and the block-editor canvas on post edit screens only (`post.php` / `post-new.php`, post type `post`). Does nothing on pages, on the front end, or in the classic editor.

## Install

Copy the folder to `wp-content/plugins/` and activate in the WP admin.

## Files

- `wp-dark-plugin.php` — loader
- `admin-editor.css` — admin chrome (post editor screens only)
- `canvas.css` — writing canvas, injected into the editor iframe, scoped to `body.post-type-post`
