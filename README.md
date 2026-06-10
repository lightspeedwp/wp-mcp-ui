# WP MCP UI — LightSpeed MCP Setup & Testing Plugin

A WordPress plugin that does two things:

1. **Ability management** — toggle WordPress MCP abilities (read/write per content type) from the admin UI.
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

The abilities management UI (toggle read/write per content type) is at **Tools → MCP Abilities**.

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
