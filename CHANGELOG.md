# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

## [1.0.0] - 2026-08-26

### Added
- [bonsai-notoolbar-edit.php] Initial release: hides the front-end admin toolbar (`show_admin_bar`) for users who can `edit_posts` (filterable via `bonsai_notoolbar_edit_capability`)
- [bonsai-notoolbar-edit.php] Fixed top-right panel rendered via `wp_footer` with a **WP Dashboard** link and a context-aware **Edit Page** link (only shown when `get_edit_post_link()` resolves for the current singular view)
- [composer.json, vendor/] Wired up YahnisElsts/plugin-update-checker (^5.6) so the plugin can self-update from GitHub releases via the wp-admin Plugins screen, matching `bonsai-code-injector` and `bonsai-maintenance`. Generated a fresh `vendor/` install with a unique Composer autoloader class name to avoid the class-name collision documented in `bonsai-code-injector`'s changelog (1.1.1)
