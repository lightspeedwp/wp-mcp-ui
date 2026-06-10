# Security Model

## How MCP authentication works in WordPress

MCP clients connect to WordPress as authenticated users. Every action the MCP client takes is performed with the permissions of the WordPress user it authenticated as. The client cannot do anything that user cannot do.

This means:

- If the MCP user is an Administrator, the client has Administrator permissions.
- If the MCP user only has `manage_options`, the client can only do what `manage_options` allows.
- There is no MCP-specific permission model separate from WordPress capabilities.

## Use a dedicated MCP user

Create a dedicated user with the login `mcp-dev-agent` (or configure `LSX_MCP_DEDICATED_USER_LOGIN`). This makes it easy to:

- Identify MCP activity in logs.
- Revoke access quickly by revoking the Application Password or deleting the user.
- Scope capabilities to only what the agent needs.

Avoid using your personal admin account for MCP authentication.

## Use the minimum useful capability

The LightSpeed testing server requires `manage_options` by default. This can be restricted via the `lsx_mcp_testing_server_capability` filter if your use case allows a lower capability. Do not raise it higher than necessary.

## Start with read-only abilities

The LightSpeed custom server exposes only read-only diagnostic abilities. No ability in this server creates, updates, or deletes content. This is intentional and should be preserved when extending.

If you need write abilities in future, register a separate server with a separate authentication requirement and a separate review process.

## Do not enable on production by default

The LightSpeed MCP features are blocked on production environments by design. The environment check is enforced at runtime by `LSX_MCP_UI_Environment::is_dev_environment()`, which checks `wp_get_environment_type()`.

Only enable MCP on production after a full review of:

- Which abilities are exposed and to what server.
- What the transport security model is (HTTPS, auth, rate limiting).
- What the MCP user's permissions are.
- Whether the MCP client is trusted.

## Application Password safety

Application Password compatibility is gated by three independent conditions:

1. `LSX_MCP_ENABLED = true` — must be explicitly set.
2. `LSX_MCP_ENABLE_APPLICATION_PASSWORDS = true` — must be explicitly set.
3. Environment must be `local` or `development` AND host must match an allowed pattern.

All three must pass or Application Passwords remain at their default availability (which may be `false` if Wordfence blocks them).

Wordfence is **never disabled globally** by this plugin. Only the WordPress Application Password availability filters are restored for the specific user/environment combination allowed.

## Application Password hygiene

- Do not store Application Passwords in WordPress options or the database.
- Do not paste Application Passwords into the WordPress admin form on this plugin's pages.
- Do not commit Application Passwords to version control.
- Revoke Application Passwords from the user profile when they are no longer needed.
- Generate a new password per MCP client rather than sharing one password across tools.

## Credential exposure risks

This plugin exposes the following information that could be sensitive in the wrong hands:

- Site URL, WordPress version, PHP version, environment type (via `lightspeed/site-summary`).
- Plugin list and versions (via `lightspeed/plugin-inventory`).
- Theme structure and file names (via `lightspeed/theme-audit`, `lightspeed/block-theme-audit`).
- URL inventory of published content (via `lightspeed/url-inventory`).
- Content QA signals (via `lightspeed/content-readiness`).

This information is appropriate for development and QA use. It is **not** appropriate for public or unauthenticated access. All abilities require authentication and `manage_options` capability.

The following are **never exposed** by any LightSpeed ability:

- Database credentials.
- WordPress salts or security keys.
- Environment variables.
- Absolute filesystem paths (except optionally, with `debug_paths=true` and `manage_options`).
- Application Password values.
- Plugin license keys or API keys.
- User passwords or hashed passwords.
