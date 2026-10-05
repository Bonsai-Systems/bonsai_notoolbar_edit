# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

## [1.4.1] - 2026-10-05

### Changed
- [lib/bonsai-hub/] Bundled Bonsai Hub updated to 1.0.1: the **Bonsai** admin menu now sits directly below Dashboard instead of above it.

## [1.4.0] - 2026-10-03

### Added
- [lib/bonsai-hub/] Bundled Bonsai Hub 1.0.0: a shared top-level **Bonsai** admin menu with a left-hand nav for every Bonsai plugin, plus a **Plugins** screen to install, activate and deactivate the rest of the suite from GitHub releases.

### Changed
- [bonsai-notoolbar-edit.php] Settings moved from **Settings → No Toolbar Edit** to **Bonsai → No Toolbar Edit** (`admin.php?page=bonsai-notoolbar-edit`), registered through the `bonsai_hub_modules` filter. Old `options-general.php` links redirect. No option or field changes.

### Removed
- [includes/admin-ui.php, assets/] Per-plugin header and design-system copy. The hub now provides both.

## [1.3.0] - 2026-09-30

### Changed
- [includes/admin-ui.php, assets/] Settings → No Toolbar Edit restyled with the Bonsai admin design system: logo header with version and GitHub/changelog links, settings in a card. Stylesheet loads on this screen only. No option or field changes.

### Fixed
- [bonsai-notoolbar-edit.php] Sites using the `bonsai_notoolbar_edit_settings_capability` filter could open the settings page but not save it, because `options.php` still required `manage_options`. Added `option_page_capability_bne_settings_group`.
- [bonsai-notoolbar-edit.php] Self-updates never worked: the update checker pointed at `Bonsai-Systems/bonsai-notoolbar-edit`, which doesn't exist (the repo is `bonsai_notoolbar_edit`). Sites on 1.2.0 or earlier need this version installed manually once; updates are automatic from then on.
- [bonsai-notoolbar-edit.php] Hover colour label wasn't tied to its input; added `label_for` and an ID. Removed inline `style` from the placement radios.

## [1.2.0] - 2026-09-03

### Added
- [bonsai-notoolbar-edit.php] **Link Hover Colour** setting under **Settings → No Toolbar Edit** so the icon links' hover/focus background can be matched to a client brand. Stored as the `bne_hover_color` option (native colour picker, `sanitize_hex_color` on save), defaults to the Bonsai pink `#ee4367`, and is deleted on uninstall

## [1.1.2] - 2026-08-26

### Fixed
- [bonsai-notoolbar-edit.php] SVG icons and their labels weren't rendering correctly: the label `<span>` used WordPress core's `.screen-reader-text` class, which many themes don't define on the front end, so it was showing as plain visible text instead of being hidden; and the SVGs themselves were being collapsed to 0 width by theme CSS resets. Renamed the label span to a scoped `.bne-fixed-links__label` class with its own visually-hidden CSS (no longer depends on theme support), and forced explicit SVG sizing with `!important` plus `flex: none` on both the icon and its parent link so theme flex/svg resets can't collapse them

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
