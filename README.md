# Bonsai No Toolbar Edit

A minimal WordPress plugin that hides the default admin toolbar on the front end and replaces it with two fixed top-right links: **WP Dashboard** and **Edit Page**.

## Features

- Hides the standard WordPress admin bar (`show_admin_bar`) on the front end only — wp-admin is untouched.
- Renders a small fixed panel in the top-right corner of every front-end page with:
  - **WP Dashboard** — links to `wp-admin`.
  - **Edit Page** — links straight to the editor for the current singular post/page. Hidden automatically when there's no editable post for the current view (archives, search results, 404s, etc.) or the user can't edit that specific post.
- Restricted to users who can `edit_posts` by default (filterable via `bonsai_notoolbar_edit_capability`).
- No settings page, no options stored in the database.

## Requirements

- WordPress 6.0+
- PHP 8.0+

## Usage

1. Activate the plugin.
2. Log in as a user who can edit content (author or above) and view the front end — the standard toolbar is gone, replaced by the two fixed links top-right.
3. Logged-out visitors and users without `edit_posts` see no change.

## Filters

- `bonsai_notoolbar_edit_capability` — capability required to hide the toolbar and see the fixed links. Defaults to `edit_posts`.

```php
// Restrict to administrators only
add_filter( 'bonsai_notoolbar_edit_capability', function () {
	return 'manage_options';
} );
```

## Updates

Ships with [YahnisElsts/plugin-update-checker](https://github.com/YahnisElsts/plugin-update-checker) (installed via Composer, `vendor/` committed) pointed at `github.com/Bonsai-Systems/bonsai-notoolbar-edit`. Sites with the plugin installed will see updates in **Plugins** in wp-admin, same as `bonsai-code-injector` and `bonsai-maintenance`.

To ship a new version:

1. Bump the `Version:` header in `bonsai-notoolbar-edit.php` and add a `CHANGELOG.md` entry.
2. Commit and push to `main`.
3. Publish a GitHub Release tagged with the new version (release-assets mode is enabled, so attach a zip of the plugin folder — plain source-archive tags won't be picked up).

Sites check for updates every 6 hours (`$checkPeriod` argument to `buildUpdateChecker()`), or immediately if an admin clicks "Check again" on the Plugins screen.
