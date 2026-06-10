# LightSpeed Custom MCP Server

## What it is

The LightSpeed Testing MCP Server is a custom MCP server registered by this plugin alongside the official WordPress MCP Adapter default server. It exposes a curated set of read-only diagnostic abilities designed for WordPress development and QA testing agents.

## Why it exists

The default MCP Adapter server exposes any ability marked with `meta.mcp.public = true`, including the generic `execute-ability` tool that can run any registered ability. For a testing agent, this is too broad — it exposes both read and write abilities, and gives the agent broad execution scope.

The custom LightSpeed server explicitly lists only the abilities it exposes. The testing agent gets a focused, safe, read-only toolset without accidental exposure of write operations.

## Endpoints

| Transport | Value |
|---|---|
| HTTP endpoint | `/wp-json/lightspeed-testing-mcp-server/mcp` |
| STDIO server ID (WP-CLI) | `lightspeed-testing-mcp-server` |

## Registration conditions

The server only registers when **all** of these are true:

1. `define( 'LSX_MCP_ENABLED', true );`
2. `define( 'LSX_MCP_ENABLE_CUSTOM_SERVER', true );` (this is the default when `LSX_MCP_ENABLED` is true)
3. `wp_get_environment_type()` returns `local` or `development`
4. `WP\MCP\Core\McpAdapter` class exists (MCP Adapter plugin is active)
5. The `HttpTransport` and error/observability handler classes are available

## Authentication

HTTP requests to the server must include valid WordPress credentials (Application Password or session cookie). The transport-level permission callback requires:

- `is_user_logged_in()` — must be authenticated.
- `current_user_can( 'manage_options' )` — by default (filterable via `lsx_mcp_testing_server_capability`).

## Abilities exposed

### `lightspeed/site-summary`

Returns a safe site summary: name, URL, home URL, WordPress version, PHP version, environment type, active theme (name/version/parent), multisite status, debug mode, search engine visibility, permalink structure, active plugin count, WooCommerce status, and MCP adapter availability.

**Does not expose:** secrets, credentials, filesystem paths, environment variables.

### `lightspeed/plugin-inventory`

Returns active plugins (and optionally inactive plugins) with name, version, plugin URI, and author. Optionally includes inactive plugins when `include_inactive: true` is passed.

**Does not expose:** license keys, plugin options, secrets.

### `lightspeed/theme-audit`

Returns active theme info, parent theme info, block theme status, theme.json existence, supported WordPress features, and counts of templates, template parts, PHP patterns, and style variations.

Absolute filesystem paths are only returned when `debug_paths: true` is passed **and** the user has `manage_options`.

### `lightspeed/url-inventory`

Returns public URLs for registered post types. Accepts `post_types` (array), `limit` (integer, max 500), and `status` (default `publish`). Returns post ID, type, title, status, URL, modified date, count by type, and whether the limit was reached.

Only returns published posts by default. Private statuses require `read_private_posts` capability.

### `lightspeed/content-readiness`

Returns per-post QA signals: missing title, empty content, missing excerpt, missing featured image, missing SEO title, missing meta description. Works with Yoast SEO, Rank Math, and AIOSEO — falls back gracefully when none is present. Returns a summary count alongside the per-item list.

### `lightspeed/block-theme-audit`

Returns block theme file inventory: template filenames, template part filenames, pattern filenames, style variation filenames. Also reports whether `theme.json` exists and which top-level sections (`settings.color.palette`, `settings.typography`, `settings.spacing`) are present. Reports dark mode variation existence (`styles/dark.json`).

Does not fully parse large theme.json files — reads the first 64 KB only.

## What is intentionally not exposed

- Any ability that creates, updates, or deletes content.
- Database credentials, secrets, or salts.
- Environment variables.
- User passwords or Application Password values.
- Absolute filesystem paths by default.
- Plugin license keys or API keys.

## How to add a new read-only ability

1. Create a new class file in `includes/abilities/class-your-ability.php` following the pattern of the existing ability classes.
2. Register it via `wp_register_ability()` with `meta.mcp.public = false` and a permission callback that requires authentication and the appropriate capability.
3. Add the ability slug to `LSX_MCP_UI_Custom_Server::TOOLS` constant.
4. Include the class file in `wp-mcp-ui.php`.
5. Call `YourAbilityClass::register()` from `LSX_MCP_UI_Plugin::register_lightspeed_abilities()`.

## How to add a future write ability safely

**Do not add write abilities to the LightSpeed testing server.** Write abilities should live on a separate server with:

- A separate constant gate (e.g. `LSX_MCP_ENABLE_WRITE_SERVER`).
- An explicit review of which post types/operations are allowed.
- A more restrictive permission model (e.g. a dedicated write user with narrowly scoped capabilities).
- Additional confirmation: log all write operations.
- Never on shared, public-facing, or production sites without formal approval.

## Capability filter

Override the required capability for all testing server abilities:

```php
add_filter( 'lsx_mcp_testing_server_capability', function() {
    return 'edit_posts';
} );
```

Override the required capability for all LightSpeed MCP features:

```php
add_filter( 'lsx_mcp_required_capability', function() {
    return 'manage_options';
} );
```
