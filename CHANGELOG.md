# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

## [1.1.1] - 2026-08-26

### Fixed
- [bonsai-notoolbar-edit.php] Icons weren't rendering because the active theme dequeues/doesn't load the `dashicons` stylesheet on the front end. Replaced the dashicons-font approach with hardcoded inline SVGs (dashboard / edit), so the icons no longer depend on any external font or stylesheet being present

## [1.1.0] - 2026-08-26

### Changed
- [bonsai-notoolbar-edit.php] Fixed links now render as dashicons-based icon buttons (dashicons-dashboard / dashicons-edit) instead of text labels, with `title` and screen-reader-only text retained for accessibility
- [bonsai-notoolbar-edit.php] Dashicons stylesheet is conditionally enqueued on the front end (only for users who'll see the icons) since it's no longer loaded automatically once the admin bar is hidden

### Added
- [bonsai-notoolbar-edit.php] Settings page under **Settings → No Toolbar Edit** with a corner-placement option (top right / top left / bottom right / bottom left), stored as the `bne_placement` option and deleted on uninstall
- [bonsai-notoolbar-edit.php] `bonsai_notoolbar_edit_settings_capability` filter (defaults to `manage_options`) to gate the new settings page separately from the front-end display capability

## [1.0.0] - 2026-08-26

### Added
- [bonsai-notoolbar-edit.php] Initial release: hides the front-end admin toolbar (`show_admin_bar`) for users who can `edit_posts` (filterable via `bonsai_notoolbar_edit_capability`)
- [bonsai-notoolbar-edit.php] Fixed top-right panel rendered via `wp_footer` with a **WP Dashboard** link and a context-aware **Edit Page** link (only shown when `get_edit_post_link()` resolves for the current singular view)
- [composer.json, vendor/] Wired up YahnisElsts/plugin-update-checker (^5.6) so the plugin can self-update from GitHub releases via the wp-admin Plugins screen, matching `bonsai-code-injector` and `bonsai-maintenance`. Generated a fresh `vendor/` install with a unique Composer autoloader class name to avoid the class-name collision documented in `bonsai-code-injector`'s changelog (1.1.1)
