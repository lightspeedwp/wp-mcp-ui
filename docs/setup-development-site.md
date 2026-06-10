# Development Site Setup (.lightspeedwp.dev)

Connect a shared LightSpeed development site to an MCP client using HTTP transport via `@automattic/mcp-wordpress-remote`.

## Why HTTP for shared dev sites

- Development sites ending in `.lightspeedwp.dev` are accessible over the internet.
- They have more realistic content, plugins, and configuration than a local Studio site.
- Useful for comprehensive testing agents that need production-like data.
- HTTP transport requires authentication — use Application Passwords.

## Requirements

- Site URL ending in `.lightspeedwp.dev` (or your configured `LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX`).
- HTTPS working on the dev site.
- The **WordPress MCP Adapter** plugin active.
- The **WP MCP UI / LightSpeed MCP** plugin active.
- Node.js available locally (`node --version` should succeed).
- `@automattic/mcp-wordpress-remote` used via `npx` — no global install needed.
- A dedicated `mcp-dev-agent` user (recommended) or an admin account.
- An Application Password created for that user.

## Enabling MCP without editing wp-config.php

Sites ending in `.lightspeedwp.dev` are automatically recognised as LightSpeed dev domains regardless of `WP_ENVIRONMENT_TYPE`. You can enable all MCP features directly from the WordPress admin:

1. Go to **Tools → LightSpeed MCP → Settings**.
2. Check **Enable MCP for this site**.
3. Check **Application Password compatibility**.
4. Click **Save Settings**.

No `wp-config.php` editing is required for `.lightspeedwp.dev` sites.

## Alternative: wp-config.php constants

Constants take precedence over admin settings. Use them to lock configuration across deployments or in environments where you cannot access the admin.

If your site is configured as `development`:

```php
define( 'WP_ENVIRONMENT_TYPE', 'development' );
define( 'LSX_MCP_ENABLED', true );
define( 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS', true );
define( 'LSX_MCP_ENABLE_CUSTOM_SERVER', true );
```

If your site is configured as `staging` (some LightSpeed dev servers provision as staging):

```php
define( 'WP_ENVIRONMENT_TYPE', 'staging' );
define( 'LSX_MCP_ENABLED', true );
define( 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS', true );
define( 'LSX_MCP_ENABLE_CUSTOM_SERVER', true );
define( 'LSX_MCP_ALLOWED_ENVIRONMENTS', array( 'local', 'development', 'staging' ) );
```

Note: Sites ending in `.lightspeedwp.dev` do **not** need `LSX_MCP_ALLOWED_ENVIRONMENTS` — the host pattern check already overrides the environment type. The constant is only needed if your dev site uses a different domain suffix.

Add constants **before** the `/* That's all, stop editing! */` line.

## Create an Application Password

1. Log in to the dev site as `mcp-dev-agent` (or admin if no dedicated user exists yet).
2. Go to **Users → Profile**, scroll to **Application Passwords**.
3. Enter a name (e.g. `Claude Code`) and click **Add New Application Password**.
4. Copy the password — it is shown **only once**.
5. Use it in the config below where you see `xxxx xxxx xxxx xxxx xxxx xxxx`.

**Do not commit this password to version control.**

## VS Code — LightSpeed Testing Server

Create or update `.vscode/mcp.json` at your project root:

```json
{
  "servers": {
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

## VS Code — Default Server

```json
{
  "servers": {
    "wordpress-dev-default": {
      "command": "npx",
      "args": ["-y", "@automattic/mcp-wordpress-remote@latest"],
      "env": {
        "WP_API_URL": "https://site-name.lightspeedwp.dev/wp-json/mcp/mcp-adapter-default-server",
        "WP_API_USERNAME": "mcp-dev-agent",
        "WP_API_PASSWORD": "xxxx xxxx xxxx xxxx xxxx xxxx",
        "OAUTH_ENABLED": "false"
      }
    }
  }
}
```

## Claude Code — LightSpeed Testing Server

Add to `.mcp.json` at project root (or `~/.claude/claude_desktop_config.json`):

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

## Claude Desktop

Same JSON format as Claude Code, placed in:

- **macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Windows:** `%APPDATA%\Claude\claude_desktop_config.json`

## Replacing placeholders

| Placeholder | Replace with |
|---|---|
| `site-name.lightspeedwp.dev` | Your actual dev site domain |
| `mcp-dev-agent` | Your MCP user login |
| `xxxx xxxx xxxx xxxx xxxx xxxx` | Your Application Password (with spaces) |

## Troubleshooting

**Application Passwords section not visible in user profile**
All four conditions must be true: `LSX_MCP_ENABLED = true`, `LSX_MCP_ENABLE_APPLICATION_PASSWORDS = true`, `WP_ENVIRONMENT_TYPE = development`, and the host ends in `.lightspeedwp.dev`.

**Wordfence blocks Application Passwords**
The plugin restores Application Password availability via filters at `PHP_INT_MAX` priority. If Wordfence still blocks, check **Wordfence → Login Security** settings. The LightSpeed MCP plugin never globally disables Wordfence.

**HTTP 401**
Wrong username or Application Password. Re-generate a new Application Password from the user profile.

**HTTP 403**
User does not have `manage_options`. Promote the MCP user to Administrator, or change the `lsx_mcp_testing_server_capability` filter.

**REST endpoint 404**
- Go to **Settings → Permalinks** and save (even without changing anything) to flush rewrite rules.
- Confirm the MCP Adapter plugin is active.
- For the LightSpeed server, confirm the Status tab shows it as registered.

**MCP Adapter inactive**
Without the WordPress MCP Adapter plugin, the REST endpoint does not exist.

**Wrong endpoint path**
The LightSpeed server endpoint is `/wp-json/lightspeed-testing-mcp-server/mcp`. The default server is `/wp-json/mcp/mcp-adapter-default-server`. Do not swap them.

**Site is staging and MCP is blocked**
Staging is blocked by default. Add `define( 'LSX_MCP_ALLOWED_ENVIRONMENTS', array( 'local', 'development', 'staging' ) );` to `wp-config.php`. Some LightSpeed dev sites are provisioned as staging — this constant unlocks them without requiring a change to the server's environment type setting.

**Site not in an allowed environment**
Check `WP_ENVIRONMENT_TYPE` with `wp eval "echo wp_get_environment_type();"`. If it returns `staging` and you cannot change it, use `LSX_MCP_ALLOWED_ENVIRONMENTS` as shown in the section above. If it returns `production`, MCP features are blocked regardless of the allowed list.

**Host does not end in `.lightspeedwp.dev`**
Only hosts matching the allowed suffix pass the host check. Use `define( 'LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX', '.yourdomain.dev' );` to override.
