# LSX MCP UI — LightSpeed MCP Setup & Ability Manager

A WordPress plugin that does two things:

1. **Ability management** — toggle WordPress MCP abilities (read/write per content type) from the admin UI at **Tools → MCP Abilities**.
2. **LightSpeed MCP layer** — configuration helper, Application Password compatibility, and a read-only custom MCP testing server for LightSpeed development sites.

---

## Security Warning

> **Never activate MCP features on a production site** without a full security review.
> The LightSpeed MCP features are blocked on production environments by design.
> See [docs/security-model.md](docs/security-model.md) for full guidance.

---

## Quick Start — Local Studio (STDIO)

The recommended approach for local WordPress Studio sites.

1. Install and activate **WordPress MCP Adapter** on the local site.
2. Install and activate this plugin.
3. Add to `wp-config.php`:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );
define( 'LSX_MCP_ENABLED', true );
```

4. Add to `.vscode/mcp.json` or `.mcp.json`:

```json
{
  "mcpServers": {
    "wordpress-local-testing": {
      "command": "wp",
      "args": [
        "--path=/Users/YOUR_USER/Studio/YOUR_SITE",
        "mcp-adapter",
        "serve",
        "--server=lightspeed-testing-mcp-server",
        "--user=admin"
      ]
    }
  }
}
```

5. Replace the path and username.

→ See [docs/setup-local-studio.md](docs/setup-local-studio.md) for full instructions and troubleshooting.

---

## Quick Start — .lightspeedwp.dev (HTTP)

For shared LightSpeed development sites accessible over HTTPS. No `wp-config.php` editing required on `.lightspeedwp.dev` sites.

1. Install and activate **WordPress MCP Adapter** on the dev site.
2. Install and activate this plugin.
3. Go to **Tools → LightSpeed MCP → Settings**, enable **Enable MCP for this site** and **Application Password compatibility**, then save.
   - Alternatively, add to `wp-config.php`: `define( 'LSX_MCP_ENABLED', true ); define( 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS', true );`
4. Create an Application Password: **Users → Profile → Application Passwords**.
5. Add to `.vscode/mcp.json` or `.mcp.json`:

```json
{
  "mcpServers": {
    "wordpress-dev-testing": {
      "command": "npx",
      "args": ["-y", "@automattic/mcp-wordpress-remote@latest"],
      "env": {
        "WP_API_URL": "https://site-name.lightspeedwp.dev/wp-json/lightspeed-testing-mcp-server/mcp",
        "WP_API_USERNAME": "mcp-dev-agent",
        "WP_API_PASSWORD": "xxxx xxxx xxxx xxxx xxxx xxxx",
        "OAUTH_ENABLED": "false"
      }
    }
  }
}
```

→ See [docs/setup-development-site.md](docs/setup-development-site.md) for full instructions and troubleshooting.

---

## Configuration

All settings can be managed from **Tools → LightSpeed MCP → Settings** — no `wp-config.php` editing required. Sites on `.lightspeedwp.dev` are automatically recognised as LightSpeed dev domains even when `WP_ENVIRONMENT_TYPE` is set to `production`.

### wp-config.php constants (optional hard overrides)

Constants take precedence over admin settings. Use them to lock configuration across deployments.

| Constant | Default | Purpose |
|---|---|---|
| `LSX_MCP_ENABLED` | (settings) | Master switch for all LightSpeed MCP features |
| `LSX_MCP_ENABLE_APPLICATION_PASSWORDS` | (settings) | Enable Application Password compatibility for HTTP transport |
| `LSX_MCP_ENABLE_CUSTOM_SERVER` | (settings, default `true`) | Register the custom LightSpeed testing server |
| `LSX_MCP_DEDICATED_USER_LOGIN` | `mcp-dev-agent` | Login of the dedicated MCP user |
| `LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX` | `.lightspeedwp.dev` | Host suffix recognised as a LightSpeed dev domain |
| `LSX_MCP_ALLOWED_ENVIRONMENTS` | `['local','development']` | WP environment types in which MCP is permitted (staging opt-in) |

---

## Abilities Reference

All abilities are toggled individually at **Tools → MCP Abilities**. Read abilities are on by default; write abilities are off by default and show a confirmation modal when first enabled.

### Posts

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-posts` | Read | Blog posts with title, URL, date, excerpt, categories, tags |
| `lsxmcpui/create-post` | Write | Create a new blog post |
| `lsxmcpui/update-post` | Write | Update an existing post by ID |
| `lsxmcpui/delete-post` | Write | Move a post to trash |

### Pages

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-pages` | Read | Published pages with title, URL, parent, status |
| `lsxmcpui/create-page` | Write | Create a new page |
| `lsxmcpui/update-page` | Write | Update a page by ID |
| `lsxmcpui/delete-page` | Write | Move a page to trash |

### Custom Post Types

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-post-types` | Read | All registered public post types with labels and REST base |
| `lsxmcpui/get-cpt-items` | Read | Query any post type by slug; includes ACF fields when active |
| `lsxmcpui/create-cpt-item` | Write | Create an item of any registered CPT with meta fields, taxonomy terms, and featured image |
| `lsxmcpui/update-cpt-item` | Write | Update an existing CPT item by ID |
| `lsxmcpui/delete-cpt-item` | Write | Move a CPT item to trash |

#### `lsxmcpui/create-cpt-item` and `lsxmcpui/update-cpt-item` parameters

| Parameter | Type | Description |
|---|---|---|
| `post_type` | string | Registered post type slug, e.g. `tour`, `destination`, `accommodation` |
| `title` | string | Post title |
| `content` | string | Post content (HTML) |
| `excerpt` | string | Post excerpt |
| `status` | string | `publish`, `draft`, or `pending`. Default `draft` |
| `slug` | string | URL slug |
| `featured_image_id` | integer | Attachment ID for the featured image |
| `meta` | object | Key-value pairs of post meta. Array values are automatically PHP-serialized (correct for Tour Operator fields like `gallery`, `itinerary`, `units`, etc.) |
| `taxonomy_terms` | object | Taxonomy assignments: keys are taxonomy slugs (e.g. `travel-style`), values are arrays of term IDs |

### Taxonomy

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-categories` | Read | Post categories with IDs, slugs, post counts |
| `lsxmcpui/create-category` | Write | Create a new category |
| `lsxmcpui/get-tags` | Read | Post tags with IDs, slugs, post counts |
| `lsxmcpui/create-tag` | Write | Create a new tag |

### Patterns

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-patterns` | Read | All registered block patterns with name, title, categories, content |
| `lsxmcpui/create-pattern` | Write | Write a new PHP pattern file to the active theme's `patterns/` directory |
| `lsxmcpui/update-pattern` | Write | Overwrite an existing pattern file in the active theme |
| `lsxmcpui/delete-pattern` | Write | Delete a pattern PHP file from the active theme |

**Notes on `lsxmcpui/create-pattern`:**
- The `slug` field must use the active theme's stylesheet slug as its prefix, e.g. `my-theme/hero-banner`. Call `lsxmcpui/get-site-info` first if unsure of the theme slug — the ability will auto-correct a wrong prefix but the correct value is always safer.
- The generated file includes a closing `?>` tag so that block HTML content following the PHP header comment is valid.

### Comments

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-comments` | Read | Comments with author, status, content snippet |
| `lsxmcpui/approve-comment` | Write | Approve a pending comment |
| `lsxmcpui/delete-comment` | Write | Move a comment to trash |

### Media, Users, Search, Site

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-media` | Read | Media library items with title, URL, MIME type |
| `lsxmcpui/get-users` | Read | Users with display name, email, role |
| `lsxmcpui/search` | Read | Full-text search across all post types |
| `lsxmcpui/get-site-info` | Read | Site name, URL, tagline, WP version, active theme slug, language |
| `lsxmcpui/get-plugins` | Read | Active plugins with name, version, author |

### Tour Operator

| Ability | Access | Description |
|---|---|---|
| `lsxmcpui/get-tour-operator-context` | Read | Full developer context: CPT slugs, all CMB2 meta keys, taxonomy slugs, modal system, CSS classes, Wetu importer field mappings |

Use `lsxmcpui/create-cpt-item` / `lsxmcpui/update-cpt-item` / `lsxmcpui/delete-cpt-item` to manage `tour`, `destination`, and `accommodation` posts. Always call `lsxmcpui/get-tour-operator-context` first to confirm meta key names and taxonomy slugs.

---

## Custom MCP Server

The plugin registers a custom `lightspeed-testing-mcp-server` that exposes read-only diagnostic abilities:

| Ability | Description |
|---|---|
| `lightspeed/site-summary` | Site name, URLs, versions, environment, theme info |
| `lightspeed/plugin-inventory` | Active plugin list with name, version, URI, author |
| `lightspeed/theme-audit` | Theme info, block theme status, template/pattern counts |
| `lightspeed/url-inventory` | Public URLs across all post types |
| `lightspeed/content-readiness` | QA signals: missing titles, images, excerpts, SEO meta |
| `lightspeed/block-theme-audit` | Block theme file inventory and theme.json analysis |

All abilities are read-only and require authentication with `manage_options`.

→ See [docs/custom-mcp-server.md](docs/custom-mcp-server.md) for full documentation.

---

## Dashboard

After activating the plugin, go to **Tools → LightSpeed MCP** for:

- Live status panel showing environment, feature flags, and endpoint URLs.
- Copy-ready config examples for VS Code, Claude Code, and Claude Desktop.
- Setup instructions for both local Studio and dev sites.
- Security checklist.
- Troubleshooting guide.

The abilities management UI is at **Tools → MCP Abilities**.

---

## Troubleshooting

**No tools showing in MCP client**
Check the Status tab at **Tools → LightSpeed MCP**. MCP must be enabled (Settings tab or constant), the operational environment must not be `blocked`, and the MCP Adapter plugin must be active.

**Application Passwords not available**
Enable MCP and Application Password compatibility in **Tools → LightSpeed MCP → Settings** (or via constants). The host must end in `.lightspeedwp.dev` (or your configured suffix). Check the Status tab for which condition is failing.

**401 / 403 errors**
Regenerate the Application Password (it is shown once). Ensure the user has `manage_options`.

**REST 404**
Flush permalinks at **Settings → Permalinks**. Confirm MCP Adapter is active.

**Ability settings reset after rename**
The database option was renamed from `wpmcpui_abilities` to `lsxmcpui_abilities` as part of the rebrand to `lsx-mcp-ui`. Re-enable any abilities that were previously active at **Tools → MCP Abilities**.

→ Full troubleshooting at **Tools → LightSpeed MCP → Troubleshooting** or in [docs/setup-development-site.md](docs/setup-development-site.md).

---

## Documentation

- [Local Studio setup (STDIO)](docs/setup-local-studio.md)
- [Development site setup (HTTP)](docs/setup-development-site.md)
- [Security model](docs/security-model.md)
- [Custom MCP server](docs/custom-mcp-server.md)

---

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
