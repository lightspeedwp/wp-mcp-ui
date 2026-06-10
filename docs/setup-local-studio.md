# Local Studio Setup (STDIO)

Connect a WordPress Studio local site to an MCP client using STDIO transport via WP-CLI.

## Why STDIO for local development

- No Application Password required.
- No public HTTP endpoint exposed.
- The agent runs as a real WordPress user in the same process context.
- Ideal for theme.json editing, pattern authoring, templates, and local theme work.
- Fastest round-trip — no HTTP overhead.

## Requirements

- WordPress Studio running a local site.
- WP-CLI installed and accessible as `wp` in your terminal (`wp --info` should succeed).
- The **WordPress MCP Adapter** plugin active on the local site.
- The **WP MCP UI / LightSpeed MCP** plugin active on the local site.
- `define( 'LSX_MCP_ENABLED', true );` in `wp-config.php`.
- An admin user, or a dedicated `mcp-dev-agent` user on the local site.

## VS Code — Default Server

Create or update `.vscode/mcp.json` at your project root:

```json
{
  "servers": {
    "wordpress-local-default": {
      "command": "wp",
      "args": [
        "--path=/Users/YOUR_USER/Studio/YOUR_SITE",
        "mcp-adapter",
        "serve",
        "--server=mcp-adapter-default-server",
        "--user=admin"
      ]
    }
  }
}
```

## VS Code — LightSpeed Testing Server

```json
{
  "servers": {
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

## Claude Code — Default Server

Add to `.mcp.json` at project root (or `~/.claude/claude_desktop_config.json`):

```json
{
  "mcpServers": {
    "wordpress-local-default": {
      "command": "wp",
      "args": [
        "--path=/Users/YOUR_USER/Studio/YOUR_SITE",
        "mcp-adapter",
        "serve",
        "--server=mcp-adapter-default-server",
        "--user=admin"
      ]
    }
  }
}
```

## Claude Code — LightSpeed Testing Server

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

## Claude Desktop

Same JSON format as Claude Code, placed in:

- **macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`
- **Windows:** `%APPDATA%\Claude\claude_desktop_config.json`

## Replacing placeholders

| Placeholder | Replace with |
|---|---|
| `/Users/YOUR_USER/Studio/YOUR_SITE` | Absolute path to your Studio WordPress root (the folder with `wp-config.php`) |
| `admin` | A real WordPress username on that site with the required capability |
| `--server=lightspeed-testing-mcp-server` | Any registered server ID on that site |

**Tip:** In WordPress Studio, right-click the site name and choose **Open in Finder** to find the path.

## Troubleshooting

**`wp` command not found**
WP-CLI is not installed or not in your PATH. Install from [wp-cli.org](https://wp-cli.org/) and verify with `wp --info`.

**Wrong Studio path**
The `--path` must point to the WordPress root containing `wp-config.php`. Check by running `wp --path=/your/path option get siteurl`.

**MCP Adapter inactive**
The `wp mcp-adapter serve` command does not exist if the plugin is not active. Install and activate the WordPress MCP Adapter plugin.

**Server starts but shows zero tools**
For `lightspeed-testing-mcp-server`: check `LSX_MCP_ENABLED = true` and `WP_ENVIRONMENT_TYPE = local` or `development`. For the default server: enable abilities in **MCP UI → Abilities**.

**User has insufficient permissions**
The `--user` argument must resolve to a WordPress user with `manage_options` capability (or whatever `lsx_mcp_testing_server_capability` is filtered to).
