# Bonsai No Toolbar Edit

A minimal WordPress plugin that hides the default admin toolbar on the front end and replaces it with two fixed icon links: **WP Dashboard** and **Edit Page**.

## Features

- Hides the standard WordPress admin bar (`show_admin_bar`) on the front end only — wp-admin is untouched.
- Renders a small fixed icon panel on every front-end page with:
  - **WP Dashboard** (dashicons-dashboard) — links to `wp-admin`.
  - **Edit Page** (dashicons-edit) — links straight to the editor for the current singular post/page. Hidden automatically when there's no editable post for the current view (archives, search results, 404s, etc.) or the user can't edit that specific post.
  - Both icons carry a `title` and screen-reader-only text, so the link purpose is still available to assistive tech and on hover.
- Corner placement (top right / top left / bottom right / bottom left) is configurable under **Settings → No Toolbar Edit**. Defaults to top right.
- Restricted to users who can `edit_posts` by default (filterable via `bonsai_notoolbar_edit_capability`). The settings page itself requires `manage_options` by default (filterable via `bonsai_notoolbar_edit_settings_capability`).
- Single option (`bne_placement`) stored in the database; removed on uninstall.

## Requirements

- WordPress 6.0+
- PHP 8.0+

## Usage

1. Activate the plugin.
2. Go to **Settings → No Toolbar Edit** and choose a corner placement (defaults to top right).
3. Log in as a user who can edit content (author or above) and view the front end — the standard toolbar is gone, replaced by the two fixed icon links in the chosen corner.
4. Logged-out visitors and users without `edit_posts` see no change.

## Filters

- `bonsai_notoolbar_edit_capability` — capability required to hide the toolbar and see the fixed links. Defaults to `edit_posts`.
- `bonsai_notoolbar_edit_settings_capability` — capability required to access the settings page. Defaults to `manage_options`.

```php
// Restrict the front-end links to administrators only
add_filter( 'bonsai_notoolbar_edit_capability', function () {
	return 'manage_options';
} );
```

## Data Structure

One option is stored:

- `bne_placement` (string) — one of `top-right`, `top-left`, `bottom-right`, `bottom-left`.

Deleted when the plugin is uninstalled.

## Updates

Ships with [YahnisElsts/plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) (installed via Composer, `vendor/` committed) pointed at `github.com/Bonsai-Systems/bonsai-notoolbar-edit`. Sites with the plugin installed will see updates in **Plugins** in wp-admin, same as `bonsai-code-injector` and `bonsai-maintenance`.

To ship a new version:

1. Bump the `Version:` header in `bonsai-notoolbar-edit.php` and add a `CHANGELOG.md` entry.
2. Commit and push to `main`.
3. Publish a GitHub Release tagged with the new version (release-assets mode is enabled, so attach a zip of the plugin folder — plain source-archive tags won't be picked up).

Sites check for updates every 6 hours (`$checkPeriod` argument to `buildUpdateChecker()`), or immediately if an admin clicks "Check again" on the Plugins screen.
