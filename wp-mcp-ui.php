<?php
/**
 * Plugin Name: WP MCP UI
 * Plugin URI:  https://lightspeedwp.agency
 * Description: Admin UI for managing WordPress content abilities exposed to AI via MCP. Enable/disable read and write access per content type from MCP UI → Abilities.
 * Version:     1.0.0
 * Author:      Lightspeed WP
 * Author URI:  https://lightspeedwp.agency
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPMCPUI_OPTION',  'wpmcpui_abilities' );
define( 'WPMCPUI_VERSION', '1.0.0' );

// ─────────────────────────────────────────────────────────────────────────────
// ABILITY REGISTRY
// ─────────────────────────────────────────────────────────────────────────────

function wpmcpui_ability_registry() {
	return array(
		// POSTS
		'wpmcpui/get-posts'   => array( 'label' => 'Read Posts',    'description' => 'List published blog posts (title, URL, date, excerpt, categories, tags).', 'group' => 'Posts',      'access' => 'read',  'default' => true  ),
		'wpmcpui/create-post' => array( 'label' => 'Create Post',   'description' => 'Create a new blog post (title, content, status, categories, tags, slug).', 'group' => 'Posts',      'access' => 'write', 'default' => false ),
		'wpmcpui/update-post' => array( 'label' => 'Update Post',   'description' => 'Update an existing post by ID.',                                            'group' => 'Posts',      'access' => 'write', 'default' => false ),
		'wpmcpui/delete-post' => array( 'label' => 'Delete Post',   'description' => 'Move a post to trash by ID.',                                               'group' => 'Posts',      'access' => 'write', 'default' => false ),
		// PAGES
		'wpmcpui/get-pages'   => array( 'label' => 'Read Pages',    'description' => 'List published pages (title, URL, parent, status).',                        'group' => 'Pages',      'access' => 'read',  'default' => true  ),
		'wpmcpui/create-page' => array( 'label' => 'Create Page',   'description' => 'Create a new WordPress page (title, content, status, parent, slug).',       'group' => 'Pages',      'access' => 'write', 'default' => false ),
		'wpmcpui/update-page' => array( 'label' => 'Update Page',   'description' => 'Update an existing page by ID.',                                            'group' => 'Pages',      'access' => 'write', 'default' => false ),
		'wpmcpui/delete-page' => array( 'label' => 'Delete Page',   'description' => 'Move a page to trash by ID.',                                               'group' => 'Pages',      'access' => 'write', 'default' => false ),
		// POST TYPES
		'wpmcpui/get-post-types' => array( 'label' => 'Read Post Types', 'description' => 'List all registered public post types with labels, supports, and REST base.', 'group' => 'Post Types', 'access' => 'read', 'default' => true  ),
		'wpmcpui/get-cpt-items'  => array( 'label' => 'Read CPT Items',  'description' => 'Query items of any registered post type by slug (e.g. tour, destination, accommodation). Includes ACF fields when available.', 'group' => 'Post Types', 'access' => 'read', 'default' => true ),
		// TAXONOMY
		'wpmcpui/get-categories'  => array( 'label' => 'Read Categories', 'description' => 'List all post categories with IDs, slugs, and post counts.', 'group' => 'Taxonomy', 'access' => 'read',  'default' => true  ),
		'wpmcpui/create-category' => array( 'label' => 'Create Category', 'description' => 'Create a new post category.',                                'group' => 'Taxonomy', 'access' => 'write', 'default' => false ),
		'wpmcpui/get-tags'        => array( 'label' => 'Read Tags',       'description' => 'List all post tags with IDs, slugs, and post counts.',       'group' => 'Taxonomy', 'access' => 'read',  'default' => true  ),
		'wpmcpui/create-tag'      => array( 'label' => 'Create Tag',      'description' => 'Create a new post tag.',                                     'group' => 'Taxonomy', 'access' => 'write', 'default' => false ),
		// COMMENTS
		'wpmcpui/get-comments'    => array( 'label' => 'Read Comments',   'description' => 'List comments with author, status, and content snippet.',    'group' => 'Comments', 'access' => 'read',  'default' => false ),
		'wpmcpui/approve-comment' => array( 'label' => 'Approve Comment', 'description' => 'Approve a pending comment by ID.',                           'group' => 'Comments', 'access' => 'write', 'default' => false ),
		'wpmcpui/delete-comment'  => array( 'label' => 'Delete Comment',  'description' => 'Move a comment to trash by ID.',                             'group' => 'Comments', 'access' => 'write', 'default' => false ),
		// MEDIA
		'wpmcpui/get-media'       => array( 'label' => 'Read Media',      'description' => 'List media library items (title, URL, MIME type, date).',    'group' => 'Media',    'access' => 'read',  'default' => false ),
		// USERS
		'wpmcpui/get-users'       => array( 'label' => 'Read Users',      'description' => 'List users with display name, email, and role.',             'group' => 'Users',    'access' => 'read',  'default' => false ),
		// SEARCH
		'wpmcpui/search'          => array( 'label' => 'Search Content',  'description' => 'Search across all post types by keyword.',                   'group' => 'Search',   'access' => 'read',  'default' => true  ),
		// SITE
		'wpmcpui/get-site-info'   => array( 'label' => 'Read Site Info',  'description' => 'Return site name, URL, tagline, WP version, and language.', 'group' => 'Site',     'access' => 'read',  'default' => true  ),
		'wpmcpui/get-plugins'     => array( 'label' => 'Read Plugins',    'description' => 'List all active plugins with name, version, and author.',    'group' => 'Site',     'access' => 'read',  'default' => false ),
		// PATTERNS
		'wpmcpui/get-patterns'   => array( 'label' => 'Read Patterns',   'description' => 'List all registered block patterns (theme + plugins) with name, title, categories, and content.', 'group' => 'Patterns', 'access' => 'read',  'default' => true  ),
		'wpmcpui/create-pattern' => array( 'label' => 'Create Pattern',  'description' => 'Write a new PHP pattern file to the active theme\'s patterns/ directory.',                          'group' => 'Patterns', 'access' => 'write', 'default' => false ),
		'wpmcpui/update-pattern' => array( 'label' => 'Update Pattern',  'description' => 'Overwrite an existing pattern file in the active theme\'s patterns/ directory.',                     'group' => 'Patterns', 'access' => 'write', 'default' => false ),
		'wpmcpui/delete-pattern' => array( 'label' => 'Delete Pattern',  'description' => 'Delete a pattern PHP file from the active theme\'s patterns/ directory.',                            'group' => 'Patterns', 'access' => 'write', 'default' => false ),
		// TOUR OPERATOR
		'wpmcpui/get-tour-operator-context' => array( 'label' => 'Tour Operator Context', 'description' => 'Returns full developer context for Tour Operator sites: CPT slugs, all meta keys, taxonomy slugs, modal system details, CSS classes, and Wetu importer field mappings.', 'group' => 'Tour Operator', 'access' => 'read', 'default' => false ),
	);
}

function wpmcpui_get_settings() {
	$saved    = get_option( WPMCPUI_OPTION, array() );
	$registry = wpmcpui_ability_registry();
	$settings = array();
	foreach ( $registry as $key => $cfg ) {
		$settings[ $key ] = isset( $saved[ $key ] ) ? (bool) $saved[ $key ] : $cfg['default'];
	}
	return $settings;
}

function wpmcpui_is_enabled( $key ) {
	$settings = wpmcpui_get_settings();
	return ! empty( $settings[ $key ] );
}

// ─────────────────────────────────────────────────────────────────────────────
// SETTINGS MANAGEMENT
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'admin_init', 'wpmcpui_register_settings' );
function wpmcpui_register_settings() {
	register_setting( 'wpmcpui_settings_group', WPMCPUI_OPTION, array( 'sanitize_callback' => 'wpmcpui_sanitize_settings' ) );
}

function wpmcpui_sanitize_settings( $input ) {
	$clean = array();
	foreach ( wpmcpui_ability_registry() as $key => $cfg ) {
		$clean[ $key ] = ! empty( $input[ $key ] );
	}
	return $clean;
}

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN MENU
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'admin_menu', 'wpmcpui_add_menu' );
function wpmcpui_add_menu() {
	add_menu_page(
		'WP MCP UI',
		'MCP UI',
		'manage_options',
		'wp-mcp-ui',
		'wpmcpui_settings_page',
		'dashicons-superhero',
		3
	);

	add_submenu_page(
		'wp-mcp-ui',
		'Abilities',
		'Abilities',
		'manage_options',
		'wp-mcp-ui'
	);

	add_submenu_page(
		'wp-mcp-ui',
		'Connect',
		'Connect',
		'manage_options',
		'wp-mcp-ui-connect',
		'wpmcpui_connect_page'
	);
}

// ─────────────────────────────────────────────────────────────────────────────
// SETTINGS PAGE (Abilities)
// ─────────────────────────────────────────────────────────────────────────────

function wpmcpui_settings_page() {
	$registry = wpmcpui_ability_registry();
	$settings = wpmcpui_get_settings();

	$groups = array();
	foreach ( $registry as $key => $cfg ) {
		$groups[ $cfg['group'] ][ $key ] = $cfg;
	}

	$icons = array(
		'Posts'        => '📝',
		'Pages'        => '📄',
		'Post Types'   => '📦',
		'Taxonomy'     => '🏷️',
		'Comments'     => '💬',
		'Media'        => '🖼️',
		'Users'        => '👥',
		'Search'       => '🔍',
		'Site'         => '🌐',
		'Patterns'     => '🧩',
		'Tour Operator' => '🌍',
	);

	$total   = count( $settings );
	$enabled = count( array_filter( $settings ) );
	$writes  = 0;
	foreach ( $settings as $key => $on ) {
		if ( $on && isset( $registry[ $key ] ) && $registry[ $key ]['access'] === 'write' ) {
			$writes++;
		}
	}
	?>
	<style>
		.wpmcpui-wrap { max-width: 860px; margin: 30px 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
		.wpmcpui-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
		.wpmcpui-header h1 { margin: 0; font-size: 22px; font-weight: 700; color: #1d2327; }
		.wpmcpui-badge { background: #2271b1; color: #fff; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
		.wpmcpui-desc { color: #646970; margin-bottom: 24px; font-size: 13.5px; line-height: 1.65; }
		.wpmcpui-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
		.wpmcpui-stat { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 14px 20px; text-align: center; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
		.wpmcpui-stat-n { font-size: 30px; font-weight: 700; color: #1d2327; }
		.wpmcpui-stat-l { font-size: 11px; color: #787c82; margin-top: 2px; text-transform: uppercase; letter-spacing: .5px; }
		.wpmcpui-stat--on .wpmcpui-stat-n { color: #00a32a; }
		.wpmcpui-stat--wr .wpmcpui-stat-n { color: #d63638; }
		.wpmcpui-group { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
		.wpmcpui-gh { background: #f6f7f7; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #dcdcde; }
		.wpmcpui-gt { font-weight: 700; font-size: 13.5px; color: #1d2327; margin: 0; display: flex; align-items: center; gap: 8px; }
		.wpmcpui-toggle-all { font-size: 12px; color: #2271b1; cursor: pointer; text-decoration: underline; background: none; border: none; padding: 0; font-weight: 600; }
		.wpmcpui-row { display: grid; grid-template-columns: 1fr 50px; align-items: center; padding: 13px 20px; border-bottom: 1px solid #f0f0f1; gap: 12px; transition: background .12s; }
		.wpmcpui-row:last-child { border-bottom: none; }
		.wpmcpui-row:hover { background: #fafafa; }
		.wpmcpui-al { font-weight: 600; font-size: 13px; color: #1d2327; display: flex; align-items: center; gap: 7px; margin-bottom: 3px; }
		.wpmcpui-ad { font-size: 12px; color: #787c82; line-height: 1.5; }
		.wpmcpui-ac { display: inline-block; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; text-transform: uppercase; letter-spacing: .4px; }
		.wpmcpui-ac--read  { background: #e0f0fa; color: #2271b1; }
		.wpmcpui-ac--write { background: #fde8e8; color: #d63638; }
		.wpmcpui-sw { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
		.wpmcpui-sw input { opacity: 0; width: 0; height: 0; }
		.wpmcpui-sl { position: absolute; cursor: pointer; inset: 0; background: #c3c4c7; border-radius: 34px; transition: .25s; }
		.wpmcpui-sl:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .25s; box-shadow: 0 1px 3px rgba(0,0,0,.25); }
		input:checked + .wpmcpui-sl { background: #00a32a; }
		input:checked + .wpmcpui-sl:before { transform: translateX(20px); }
		.wpmcpui-savebar { display: flex; align-items: center; gap: 16px; margin-top: 24px; padding: 18px 20px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; }
		.wpmcpui-savebar .button-primary { font-size: 14px; padding: 7px 20px; height: auto; }
		.wpmcpui-savenote { font-size: 12px; color: #787c82; }
	</style>

	<div class="wpmcpui-wrap">
		<div class="wpmcpui-header">
			<h1>WP MCP UI — Abilities</h1>
			<span class="wpmcpui-badge">v<?php echo esc_html( WPMCPUI_VERSION ); ?></span>
		</div>
		<p class="wpmcpui-desc">
			Control exactly what <strong>Claude AI</strong> can <strong>read</strong> and <strong>write</strong> on your WordPress site via MCP.
			<span style="color:#d63638;font-weight:600;"> Write abilities modify live site content — enable with care.</span>
		</p>

		<div class="wpmcpui-stats">
			<div class="wpmcpui-stat">
				<div class="wpmcpui-stat-n"><?php echo esc_html( $total ); ?></div>
				<div class="wpmcpui-stat-l">Total Abilities</div>
			</div>
			<div class="wpmcpui-stat wpmcpui-stat--on">
				<div class="wpmcpui-stat-n"><?php echo esc_html( $enabled ); ?></div>
				<div class="wpmcpui-stat-l">Enabled</div>
			</div>
			<div class="wpmcpui-stat wpmcpui-stat--wr">
				<div class="wpmcpui-stat-n"><?php echo esc_html( $writes ); ?></div>
				<div class="wpmcpui-stat-l">Write Access Active</div>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields( 'wpmcpui_settings_group' ); ?>

			<?php foreach ( $groups as $group_name => $abilities ) : ?>
			<div class="wpmcpui-group">
				<div class="wpmcpui-gh">
					<h3 class="wpmcpui-gt">
						<?php echo $icons[ $group_name ] ?? '⚡'; ?>
						<?php echo esc_html( $group_name ); ?>
					</h3>
					<button type="button" class="wpmcpui-toggle-all" data-group="<?php echo esc_attr( $group_name ); ?>">Toggle All</button>
				</div>
				<?php foreach ( $abilities as $key => $cfg ) : ?>
				<div class="wpmcpui-row">
					<div>
						<div class="wpmcpui-al">
							<?php echo esc_html( $cfg['label'] ); ?>
							<span class="wpmcpui-ac wpmcpui-ac--<?php echo esc_attr( $cfg['access'] ); ?>"><?php echo esc_html( $cfg['access'] ); ?></span>
						</div>
						<div class="wpmcpui-ad"><?php echo esc_html( $cfg['description'] ); ?></div>
					</div>
					<label class="wpmcpui-sw">
						<input
							type="checkbox"
							name="<?php echo esc_attr( WPMCPUI_OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
							value="1"
							data-group="<?php echo esc_attr( $group_name ); ?>"
							data-access="<?php echo esc_attr( $cfg['access'] ); ?>"
							<?php checked( ! empty( $settings[ $key ] ) ); ?>
						>
						<span class="wpmcpui-sl"></span>
					</label>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>

			<div class="wpmcpui-savebar">
				<?php submit_button( 'Save Settings', 'primary', 'submit', false ); ?>
				<span class="wpmcpui-savenote">Changes take effect immediately for all active MCP sessions.</span>
			</div>
		</form>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('input[data-access="write"]').forEach(function (cb) {
			cb.addEventListener('change', function () {
				if (this.checked && !confirm('This ability can MODIFY live site content.\n\nAre you sure you want to enable it?')) {
					this.checked = false;
				}
			});
		});
		document.querySelectorAll('.wpmcpui-toggle-all').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var group = this.dataset.group;
				var boxes = document.querySelectorAll('input[data-group="' + group + '"]');
				var allOn = Array.from(boxes).every(function (b) { return b.checked; });
				boxes.forEach(function (b) { b.checked = !allOn; });
			});
		});
	});
	</script>
	<?php
}

// ─────────────────────────────────────────────────────────────────────────────
// CONNECT PAGE (Config Files)
// ─────────────────────────────────────────────────────────────────────────────

function wpmcpui_connect_page() {
	$current_user = wp_get_current_user();
	$username     = $current_user->exists() ? $current_user->user_login : 'your-wordpress-username';
	$api_url      = untrailingslashit( rest_url( 'mcp/mcp-adapter-default-server' ) );
	$site_domain  = parse_url( get_site_url(), PHP_URL_HOST );
	$server_key   = 'wp-mcp-ui-' . trim( preg_replace( '/[^a-z0-9]+/', '-', strtolower( $site_domain ) ), '-' );

	$base_env = array(
		'WP_API_URL'      => $api_url,
		'WP_API_USERNAME' => $username,
		'WP_API_PASSWORD' => 'replace-with-your-application-password',
	);

	// Claude Code & Claude Desktop share the same JSON format
	$claude_config = array(
		'mcpServers' => array(
			$server_key => array(
				'command' => 'npx',
				'args'    => array( '-y', '@automattic/mcp-wordpress-remote@latest' ),
				'env'     => $base_env,
			),
		),
	);
	$claude_json = wp_json_encode( $claude_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

	// VS Code .vscode/mcp.json format (type: stdio is explicit)
	$vscode_config = array(
		'servers' => array(
			$server_key => array(
				'type'    => 'stdio',
				'command' => 'npx',
				'args'    => array( '-y', '@automattic/mcp-wordpress-remote@latest' ),
				'env'     => $base_env,
			),
		),
	);
	$vscode_json = wp_json_encode( $vscode_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

	// Codex TOML
	$toml_key    = $server_key;
	$toml_config = "[mcp_servers.{$toml_key}]\n"
		. "command = \"npx\"\n"
		. "args = [\"-y\", \"@automattic/mcp-wordpress-remote@latest\"]\n"
		. "\n"
		. "[mcp_servers.{$toml_key}.env]\n"
		. "WP_API_URL = \"{$api_url}\"\n"
		. "WP_API_USERNAME = \"{$username}\"\n"
		. "WP_API_PASSWORD = \"replace-with-your-application-password\"";
	?>
	<style>
		.wpmcpui-wrap { max-width: 860px; margin: 30px 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
		.wpmcpui-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
		.wpmcpui-header h1 { margin: 0; font-size: 22px; font-weight: 700; color: #1d2327; }
		.wpmcpui-desc { color: #646970; margin-bottom: 16px; font-size: 13.5px; line-height: 1.65; }
		.wpmcpui-tabs { display: flex; gap: 0; margin-bottom: 0; border-bottom: 2px solid #dcdcde; }
		.wpmcpui-tab-btn { background: none; border: none; padding: 10px 22px; font-size: 14px; font-weight: 600; color: #787c82; cursor: pointer; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: color .15s, border-color .15s; }
		.wpmcpui-tab-btn:hover { color: #1d2327; }
		.wpmcpui-tab-btn.active { color: #2271b1; border-bottom-color: #2271b1; }
		.wpmcpui-tab-panel { display: none; }
		.wpmcpui-tab-panel.active { display: block; }
		.wpmcpui-config-box { background: #fff; border: 1px solid #dcdcde; border-top: none; border-radius: 0 0 8px 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
		.wpmcpui-instructions { padding: 20px 20px 16px; border-bottom: 1px solid #f0f0f1; }
		.wpmcpui-instructions ol { margin: 0; padding-left: 20px; color: #3c434a; font-size: 14px; line-height: 2; }
		.wpmcpui-instructions code { background: #f0f0f1; padding: 2px 6px; border-radius: 4px; font-size: 12.5px; color: #d63638; font-family: Consolas, Monaco, monospace; }
		.wpmcpui-note { margin-top: 14px; padding: 10px 14px; background: #f0f6fc; border-left: 3px solid #2271b1; border-radius: 0 4px 4px 0; font-size: 13px; color: #3c434a; line-height: 1.6; }
		.wpmcpui-config-header { background: #f6f7f7; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #dcdcde; }
		.wpmcpui-config-title { font-weight: 700; font-size: 13px; color: #1d2327; margin: 0; font-family: Consolas, Monaco, monospace; }
		.wpmcpui-copy-btn { font-size: 12px; color: #2271b1; cursor: pointer; background: none; border: none; padding: 0; font-weight: 600; display: flex; align-items: center; gap: 4px; transition: color .2s; }
		.wpmcpui-copy-btn:hover { color: #00a32a; }
		.wpmcpui-code-area { background: #1e1e1e; color: #d4d4d4; padding: 20px; margin: 0; font-family: Consolas, Monaco, monospace; font-size: 13.5px; line-height: 1.6; overflow-x: auto; white-space: pre; }
	</style>

	<div class="wpmcpui-wrap">
		<div class="wpmcpui-header">
			<h1>WP MCP UI — Connect</h1>
		</div>
		<p class="wpmcpui-desc">
			Use the config below to connect your AI tool to this WordPress site. Your API URL and username are pre-filled —
			generate an <strong>Application Password</strong> and replace the placeholder before saving.
		</p>

		<div class="wpmcpui-tabs">
			<button type="button" class="wpmcpui-tab-btn active" data-tab="claude-code">Claude Code</button>
			<button type="button" class="wpmcpui-tab-btn" data-tab="vscode">VS Code</button>
			<button type="button" class="wpmcpui-tab-btn" data-tab="claude-desktop">Claude Desktop</button>
			<button type="button" class="wpmcpui-tab-btn" data-tab="codex">Codex</button>
		</div>

		<?php /* ── Tab: Claude Code (CLI) ── */ ?>
		<div class="wpmcpui-tab-panel active" id="wpmcpui-tab-claude-code">
			<div class="wpmcpui-config-box">
				<div class="wpmcpui-instructions">
					<ol>
						<li>Go to <strong>Users &rarr; Profile</strong>, scroll to <strong>Application Passwords</strong>, generate a new password, and copy it.</li>
						<li>In the config below, replace <code>replace-with-your-application-password</code> with your new password.</li>
						<li>Open (or create) your Claude Code MCP config file:<br>
							&nbsp;&nbsp;<strong>macOS / Linux:</strong> <code>~/.claude/claude_desktop_config.json</code><br>
							&nbsp;&nbsp;<strong>Windows:</strong> <code>%APPDATA%\Claude\claude_desktop_config.json</code>
						</li>
						<li>If <code>mcpServers</code> already exists in that file, add the new server block inside it. If the file is new, paste the full JSON below as its entire content.</li>
						<li>Save the file and restart the <code>claude</code> CLI. Verify with <code>claude mcp list</code>.</li>
					</ol>
					<p class="wpmcpui-note">
						<strong>Per-project config:</strong> You can also scope this to a single project by adding the same <code>mcpServers</code> block inside <code>.claude/settings.json</code> at your project root. Claude Code merges project and user configs, so the server will be available whenever you open that project.
					</p>
				</div>
				<div class="wpmcpui-config-header">
					<span class="wpmcpui-config-title">~/.claude/claude_desktop_config.json</span>
					<button type="button" class="wpmcpui-copy-btn" data-code="wpmcpui-code-claude-code">
						<span class="dashicons dashicons-clipboard" style="font-size:16px;width:16px;height:16px;"></span> Copy
					</button>
				</div>
				<pre class="wpmcpui-code-area" id="wpmcpui-code-claude-code"><?php echo esc_html( $claude_json ); ?></pre>
			</div>
		</div>

		<?php /* ── Tab: VS Code ── */ ?>
		<div class="wpmcpui-tab-panel" id="wpmcpui-tab-vscode">
			<div class="wpmcpui-config-box">
				<div class="wpmcpui-instructions">
					<ol>
						<li>Go to <strong>Users &rarr; Profile</strong>, scroll to <strong>Application Passwords</strong>, generate a new password, and copy it.</li>
						<li>In the config below, replace <code>replace-with-your-application-password</code> with your new password.</li>
						<li>
							<strong>Option A — workspace (recommended, per-project):</strong><br>
							Create the file <code>.vscode/mcp.json</code> in your project root and paste the config below as its entire content.
						</li>
						<li>
							<strong>Option B — user-global:</strong><br>
							Open VS Code &rarr; <em>Command Palette</em> (<code>Cmd/Ctrl+Shift+P</code>) &rarr; <em>Preferences: Open User Settings (JSON)</em>.<br>
							Wrap the <code>"servers"</code> block in a top-level <code>"mcp"</code> key:<br>
							<code>{ "mcp": { "servers": { ... } } }</code>
						</li>
						<li>Save and reload VS Code. The server will appear under <strong>MCP Servers</strong> in the Claude / Copilot extension sidebar.</li>
					</ol>
					<p class="wpmcpui-note">
						MCP support in VS Code requires the <strong>GitHub Copilot</strong> extension (v1.99+) or a compatible Claude extension with MCP enabled.
					</p>
				</div>
				<div class="wpmcpui-config-header">
					<span class="wpmcpui-config-title">.vscode/mcp.json</span>
					<button type="button" class="wpmcpui-copy-btn" data-code="wpmcpui-code-vscode">
						<span class="dashicons dashicons-clipboard" style="font-size:16px;width:16px;height:16px;"></span> Copy
					</button>
				</div>
				<pre class="wpmcpui-code-area" id="wpmcpui-code-vscode"><?php echo esc_html( $vscode_json ); ?></pre>
			</div>
		</div>

		<?php /* ── Tab: Claude Desktop ── */ ?>
		<div class="wpmcpui-tab-panel" id="wpmcpui-tab-claude-desktop">
			<div class="wpmcpui-config-box">
				<div class="wpmcpui-instructions">
					<ol>
						<li>Go to <strong>Users &rarr; Profile</strong>, scroll to <strong>Application Passwords</strong>, generate a new password, and copy it.</li>
						<li>In the config below, replace <code>replace-with-your-application-password</code> with your new password.</li>
						<li>Open (or create) your Claude Desktop config file:<br>
							&nbsp;&nbsp;<strong>macOS:</strong> <code>~/Library/Application Support/Claude/claude_desktop_config.json</code><br>
							&nbsp;&nbsp;<strong>Windows:</strong> <code>%APPDATA%\Claude\claude_desktop_config.json</code>
						</li>
						<li>If <code>mcpServers</code> already exists, add the new server block inside it. If the file doesn't exist, paste the full JSON below as its entire content.</li>
						<li>Save the file, then <strong>fully quit and reopen</strong> Claude Desktop. Check <em>Settings &rarr; Developer</em> to confirm the server is listed and connected.</li>
					</ol>
				</div>
				<div class="wpmcpui-config-header">
					<span class="wpmcpui-config-title">claude_desktop_config.json</span>
					<button type="button" class="wpmcpui-copy-btn" data-code="wpmcpui-code-claude-desktop">
						<span class="dashicons dashicons-clipboard" style="font-size:16px;width:16px;height:16px;"></span> Copy
					</button>
				</div>
				<pre class="wpmcpui-code-area" id="wpmcpui-code-claude-desktop"><?php echo esc_html( $claude_json ); ?></pre>
			</div>
		</div>

		<?php /* ── Tab: Codex ── */ ?>
		<div class="wpmcpui-tab-panel" id="wpmcpui-tab-codex">
			<div class="wpmcpui-config-box">
				<div class="wpmcpui-instructions">
					<ol>
						<li>Go to <strong>Users &rarr; Profile</strong>, scroll to <strong>Application Passwords</strong>, generate a new password, and copy it.</li>
						<li>In the config below, replace <code>replace-with-your-application-password</code> with your new password.</li>
						<li>Open (or create) <code>~/.codex/config.toml</code> and paste the block below.</li>
						<li>Save the file — Codex picks up the change on next run.</li>
					</ol>
				</div>
				<div class="wpmcpui-config-header">
					<span class="wpmcpui-config-title">~/.codex/config.toml</span>
					<button type="button" class="wpmcpui-copy-btn" data-code="wpmcpui-code-codex">
						<span class="dashicons dashicons-clipboard" style="font-size:16px;width:16px;height:16px;"></span> Copy
					</button>
				</div>
				<pre class="wpmcpui-code-area" id="wpmcpui-code-codex"><?php echo esc_html( $toml_config ); ?></pre>
			</div>
		</div>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Tab switching
		document.querySelectorAll('.wpmcpui-tab-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var tab = btn.dataset.tab;
				document.querySelectorAll('.wpmcpui-tab-btn').forEach(function (b) { b.classList.remove('active'); });
				document.querySelectorAll('.wpmcpui-tab-panel').forEach(function (p) { p.classList.remove('active'); });
				btn.classList.add('active');
				document.getElementById('wpmcpui-tab-' + tab).classList.add('active');
			});
		});

		// Copy buttons
		document.querySelectorAll('.wpmcpui-copy-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var el = document.getElementById(btn.dataset.code);
				if (!el) return;
				navigator.clipboard.writeText(el.innerText).then(function () {
					var orig = btn.innerHTML;
					btn.innerHTML = '<span class="dashicons dashicons-yes-alt" style="font-size:16px;width:16px;height:16px;"></span> Copied!';
					btn.style.color = '#00a32a';
					setTimeout(function () { btn.innerHTML = orig; btn.style.color = ''; }, 2500);
				}).catch(function () {
					alert('Copy failed — please select and copy the text manually.');
				});
			});
		});
	});
	</script>
	<?php
}

// ─────────────────────────────────────────────────────────────────────────────
// ABILITY CATEGORY
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'wp_abilities_api_categories_init', 'wpmcpui_register_ability_category' );
function wpmcpui_register_ability_category() {
	wp_register_ability_category( 'wpmcpui', array(
		'label'       => 'WP MCP UI',
		'description' => 'WordPress content abilities managed by WP MCP UI.',
	) );
}

// ─────────────────────────────────────────────────────────────────────────────
// REGISTER ABILITIES
// ─────────────────────────────────────────────────────────────────────────────

add_action( 'wp_abilities_api_init', 'wpmcpui_register_all_abilities' );
function wpmcpui_register_all_abilities() {

	$base = array(
		'category'      => 'wpmcpui',
		'output_schema' => array( 'type' => 'object' ),
		'meta'          => array( 'mcp' => array( 'public' => true ) ),
	);

	// ── GET POSTS ──────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-posts' ) ) {
		wp_register_ability( 'wpmcpui/get-posts', array_merge( $base, array(
			'label'       => 'Get Blog Posts',
			'description' => 'Returns blog posts with full metadata.',
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'per_page' => array( 'type' => 'integer', 'description' => 'Number of posts. Default 10.' ),
					'status'   => array( 'type' => 'string',  'description' => 'publish | draft | all. Default publish.' ),
				),
			),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_posts',
		) ) );
	}

	// ── CREATE POST ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/create-post' ) ) {
		wp_register_ability( 'wpmcpui/create-post', array_merge( $base, array(
			'label'       => 'Create Post',
			'description' => 'Creates a new blog post.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'title', 'content' ),
				'properties' => array(
					'title'      => array( 'type' => 'string',  'description' => 'Post title.' ),
					'content'    => array( 'type' => 'string',  'description' => 'Post content (HTML).' ),
					'status'     => array( 'type' => 'string',  'description' => 'publish | draft | pending. Default draft.' ),
					'categories' => array( 'type' => 'array',   'items' => array( 'type' => 'integer' ), 'description' => 'Category IDs.' ),
					'tags'       => array( 'type' => 'array',   'items' => array( 'type' => 'integer' ), 'description' => 'Tag IDs.' ),
					'excerpt'    => array( 'type' => 'string',  'description' => 'Post excerpt.' ),
					'slug'       => array( 'type' => 'string',  'description' => 'URL slug.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'publish_posts' ); },
			'execute_callback'   => 'wpmcpui_execute_create_post',
		) ) );
	}

	// ── UPDATE POST ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/update-post' ) ) {
		wp_register_ability( 'wpmcpui/update-post', array_merge( $base, array(
			'label'       => 'Update Post',
			'description' => 'Updates an existing post.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'id' ),
				'properties' => array(
					'id'         => array( 'type' => 'integer', 'description' => 'Post ID.' ),
					'title'      => array( 'type' => 'string',  'description' => 'New title.' ),
					'content'    => array( 'type' => 'string',  'description' => 'New content.' ),
					'status'     => array( 'type' => 'string',  'description' => 'New status.' ),
					'categories' => array( 'type' => 'array',   'items' => array( 'type' => 'integer' ) ),
					'tags'       => array( 'type' => 'array',   'items' => array( 'type' => 'integer' ) ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
			'execute_callback'   => 'wpmcpui_execute_update_post',
		) ) );
	}

	// ── DELETE POST ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/delete-post' ) ) {
		wp_register_ability( 'wpmcpui/delete-post', array_merge( $base, array(
			'label'       => 'Delete Post',
			'description' => 'Moves a post to trash.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'id' ),
				'properties' => array(
					'id' => array( 'type' => 'integer', 'description' => 'Post ID.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'delete_posts' ); },
			'execute_callback'   => 'wpmcpui_execute_delete_post',
		) ) );
	}

	// ── GET PAGES ──────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-pages' ) ) {
		wp_register_ability( 'wpmcpui/get-pages', array_merge( $base, array(
			'label'       => 'Get Pages',
			'description' => 'Returns published pages.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_pages',
		) ) );
	}

	// ── CREATE PAGE ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/create-page' ) ) {
		wp_register_ability( 'wpmcpui/create-page', array_merge( $base, array(
			'label'       => 'Create Page',
			'description' => 'Creates a new page.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'title', 'content' ),
				'properties' => array(
					'title'   => array( 'type' => 'string',  'description' => 'Page title.' ),
					'content' => array( 'type' => 'string',  'description' => 'Page content.' ),
					'status'  => array( 'type' => 'string',  'description' => 'publish | draft.' ),
					'parent'  => array( 'type' => 'integer', 'description' => 'Parent page ID.' ),
					'slug'    => array( 'type' => 'string',  'description' => 'URL slug.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'publish_pages' ); },
			'execute_callback'   => 'wpmcpui_execute_create_page',
		) ) );
	}

	// ── UPDATE PAGE ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/update-page' ) ) {
		wp_register_ability( 'wpmcpui/update-page', array_merge( $base, array(
			'label'       => 'Update Page',
			'description' => 'Updates an existing page.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'id' ),
				'properties' => array(
					'id'      => array( 'type' => 'integer', 'description' => 'Page ID.' ),
					'title'   => array( 'type' => 'string',  'description' => 'New title.' ),
					'content' => array( 'type' => 'string',  'description' => 'New content.' ),
					'status'  => array( 'type' => 'string',  'description' => 'New status.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'edit_pages' ); },
			'execute_callback'   => 'wpmcpui_execute_update_page',
		) ) );
	}

	// ── DELETE PAGE ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/delete-page' ) ) {
		wp_register_ability( 'wpmcpui/delete-page', array_merge( $base, array(
			'label'       => 'Delete Page',
			'description' => 'Moves a page to trash.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'id' ),
				'properties' => array(
					'id' => array( 'type' => 'integer', 'description' => 'Page ID.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'delete_pages' ); },
			'execute_callback'   => 'wpmcpui_execute_delete_page',
		) ) );
	}

	// ── GET POST TYPES ─────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-post-types' ) ) {
		wp_register_ability( 'wpmcpui/get-post-types', array_merge( $base, array(
			'label'       => 'Get Post Types',
			'description' => 'Returns all registered public post types.',
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'include_builtin' => array( 'type' => 'boolean', 'description' => 'Include built-in types (post, page). Default true.' ),
				),
			),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_post_types',
		) ) );
	}

	// ── GET CPT ITEMS ──────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-cpt-items' ) ) {
		wp_register_ability( 'wpmcpui/get-cpt-items', array_merge( $base, array(
			'label'       => 'Get CPT Items',
			'description' => 'Query items of any registered post type by slug. Includes ACF fields when available.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'post_type' ),
				'properties' => array(
					'post_type' => array( 'type' => 'string',  'description' => 'Post type slug (e.g. tour, destination, accommodation).' ),
					'per_page'  => array( 'type' => 'integer', 'description' => 'Number of items. Default 10.' ),
					'status'    => array( 'type' => 'string',  'description' => 'publish | draft | all. Default publish.' ),
					'search'    => array( 'type' => 'string',  'description' => 'Optional keyword filter.' ),
					'orderby'   => array( 'type' => 'string',  'description' => 'date | title | menu_order | modified. Default date.' ),
					'order'     => array( 'type' => 'string',  'description' => 'ASC | DESC. Default DESC.' ),
				),
			),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_cpt_items',
		) ) );
	}

	// ── GET CATEGORIES ─────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-categories' ) ) {
		wp_register_ability( 'wpmcpui/get-categories', array_merge( $base, array(
			'label'       => 'Get Categories',
			'description' => 'Returns all categories.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_categories',
		) ) );
	}

	// ── CREATE CATEGORY ────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/create-category' ) ) {
		wp_register_ability( 'wpmcpui/create-category', array_merge( $base, array(
			'label'       => 'Create Category',
			'description' => 'Creates a new category.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'name' ),
				'properties' => array(
					'name'        => array( 'type' => 'string',  'description' => 'Category name.' ),
					'description' => array( 'type' => 'string',  'description' => 'Description.' ),
					'parent'      => array( 'type' => 'integer', 'description' => 'Parent category ID.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'manage_categories' ); },
			'execute_callback'   => 'wpmcpui_execute_create_category',
		) ) );
	}

	// ── GET TAGS ───────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-tags' ) ) {
		wp_register_ability( 'wpmcpui/get-tags', array_merge( $base, array(
			'label'       => 'Get Tags',
			'description' => 'Returns all tags.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_tags',
		) ) );
	}

	// ── CREATE TAG ─────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/create-tag' ) ) {
		wp_register_ability( 'wpmcpui/create-tag', array_merge( $base, array(
			'label'       => 'Create Tag',
			'description' => 'Creates a new tag.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'name' ),
				'properties' => array(
					'name'        => array( 'type' => 'string', 'description' => 'Tag name.' ),
					'description' => array( 'type' => 'string', 'description' => 'Description.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'manage_categories' ); },
			'execute_callback'   => 'wpmcpui_execute_create_tag',
		) ) );
	}

	// ── GET COMMENTS ───────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-comments' ) ) {
		wp_register_ability( 'wpmcpui/get-comments', array_merge( $base, array(
			'label'       => 'Get Comments',
			'description' => 'Returns comments.',
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'status'   => array( 'type' => 'string',  'description' => 'hold | approve | all.' ),
					'per_page' => array( 'type' => 'integer', 'description' => 'Limit. Default 20.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'moderate_comments' ); },
			'execute_callback'   => 'wpmcpui_execute_get_comments',
		) ) );
	}

	// ── APPROVE COMMENT ────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/approve-comment' ) ) {
		wp_register_ability( 'wpmcpui/approve-comment', array_merge( $base, array(
			'label'       => 'Approve Comment',
			'description' => 'Approves a pending comment.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'id' ),
				'properties' => array(
					'id' => array( 'type' => 'integer', 'description' => 'Comment ID.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'moderate_comments' ); },
			'execute_callback'   => 'wpmcpui_execute_approve_comment',
		) ) );
	}

	// ── DELETE COMMENT ─────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/delete-comment' ) ) {
		wp_register_ability( 'wpmcpui/delete-comment', array_merge( $base, array(
			'label'       => 'Delete Comment',
			'description' => 'Trashes a comment.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'id' ),
				'properties' => array(
					'id' => array( 'type' => 'integer', 'description' => 'Comment ID.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'moderate_comments' ); },
			'execute_callback'   => 'wpmcpui_execute_delete_comment',
		) ) );
	}

	// ── GET MEDIA ──────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-media' ) ) {
		wp_register_ability( 'wpmcpui/get-media', array_merge( $base, array(
			'label'       => 'Get Media',
			'description' => 'Lists media library items.',
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'per_page' => array( 'type' => 'integer', 'description' => 'Limit. Default 20.' ),
					'type'     => array( 'type' => 'string',  'description' => 'MIME type filter e.g. image.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'upload_files' ); },
			'execute_callback'   => 'wpmcpui_execute_get_media',
		) ) );
	}

	// ── GET USERS ──────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-users' ) ) {
		wp_register_ability( 'wpmcpui/get-users', array_merge( $base, array(
			'label'       => 'Get Users',
			'description' => 'Lists registered users.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => function () { return current_user_can( 'list_users' ); },
			'execute_callback'   => 'wpmcpui_execute_get_users',
		) ) );
	}

	// ── SEARCH ─────────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/search' ) ) {
		wp_register_ability( 'wpmcpui/search', array_merge( $base, array(
			'label'       => 'Search Content',
			'description' => 'Search across all post types by keyword.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'query' ),
				'properties' => array(
					'query'     => array( 'type' => 'string',  'description' => 'Search keyword.' ),
					'post_type' => array( 'type' => 'string',  'description' => 'Restrict to post type slug. Default any.' ),
					'per_page'  => array( 'type' => 'integer', 'description' => 'Limit. Default 10.' ),
				),
			),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_search',
		) ) );
	}

	// ── GET SITE INFO ──────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-site-info' ) ) {
		wp_register_ability( 'wpmcpui/get-site-info', array_merge( $base, array(
			'label'       => 'Get Site Info',
			'description' => 'Returns site metadata.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => '__return_true',
			'execute_callback'   => 'wpmcpui_execute_get_site_info',
		) ) );
	}

	// ── GET PLUGINS ────────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-plugins' ) ) {
		wp_register_ability( 'wpmcpui/get-plugins', array_merge( $base, array(
			'label'       => 'Get Active Plugins',
			'description' => 'Lists active plugins.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => function () { return current_user_can( 'activate_plugins' ); },
			'execute_callback'   => 'wpmcpui_execute_get_plugins',
		) ) );
	}

	// ── GET PATTERNS ───────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-patterns' ) ) {
		wp_register_ability( 'wpmcpui/get-patterns', array_merge( $base, array(
			'label'       => 'Get Block Patterns',
			'description' => 'Lists all registered block patterns.',
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'source' => array( 'type' => 'string', 'description' => 'Filter by source: theme | plugin | all. Default all.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
			'execute_callback'   => 'wpmcpui_execute_get_patterns',
		) ) );
	}

	// ── CREATE PATTERN ─────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/create-pattern' ) ) {
		wp_register_ability( 'wpmcpui/create-pattern', array_merge( $base, array(
			'label'       => 'Create Pattern',
			'description' => 'Writes a new PHP pattern file to the active theme\'s patterns/ directory.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'filename', 'title', 'slug', 'content' ),
				'properties' => array(
					'filename'       => array( 'type' => 'string',  'description' => 'PHP filename, e.g. hero-banner.php. No path separators.' ),
					'title'          => array( 'type' => 'string',  'description' => 'Human-readable pattern title.' ),
					'slug'           => array( 'type' => 'string',  'description' => 'Pattern slug, e.g. theme-slug/hero-banner.' ),
					'description'    => array( 'type' => 'string',  'description' => 'Optional pattern description.' ),
					'categories'     => array( 'type' => 'string',  'description' => 'Comma-separated category slugs, e.g. featured, banner.' ),
					'block_types'    => array( 'type' => 'string',  'description' => 'Comma-separated block types this pattern inserts, e.g. core/cover.' ),
					'viewport_width' => array( 'type' => 'integer', 'description' => 'Preview viewport width in pixels. Default 1280.' ),
					'inserter'       => array( 'type' => 'boolean', 'description' => 'Show in the block inserter. Default true.' ),
					'content'        => array( 'type' => 'string',  'description' => 'The HTML/block markup content (everything after the header comment).' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'edit_theme_options' ); },
			'execute_callback'   => 'wpmcpui_execute_create_pattern',
		) ) );
	}

	// ── UPDATE PATTERN ─────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/update-pattern' ) ) {
		wp_register_ability( 'wpmcpui/update-pattern', array_merge( $base, array(
			'label'       => 'Update Pattern',
			'description' => 'Overwrites an existing pattern file in the active theme\'s patterns/ directory.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'filename', 'content' ),
				'properties' => array(
					'filename' => array( 'type' => 'string', 'description' => 'Existing PHP filename to overwrite, e.g. hero-banner.php.' ),
					'content'  => array( 'type' => 'string', 'description' => 'Full new file content including the PHP header comment block.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'edit_theme_options' ); },
			'execute_callback'   => 'wpmcpui_execute_update_pattern',
		) ) );
	}

	// ── DELETE PATTERN ─────────────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/delete-pattern' ) ) {
		wp_register_ability( 'wpmcpui/delete-pattern', array_merge( $base, array(
			'label'       => 'Delete Pattern',
			'description' => 'Deletes a pattern PHP file from the active theme\'s patterns/ directory.',
			'input_schema' => array(
				'type'       => 'object',
				'required'   => array( 'filename' ),
				'properties' => array(
					'filename' => array( 'type' => 'string', 'description' => 'PHP filename to delete, e.g. hero-banner.php.' ),
				),
			),
			'permission_callback' => function () { return current_user_can( 'edit_theme_options' ); },
			'execute_callback'   => 'wpmcpui_execute_delete_pattern',
		) ) );
	}

	// ── TOUR OPERATOR CONTEXT ──────────────────────────────────────────────────
	if ( wpmcpui_is_enabled( 'wpmcpui/get-tour-operator-context' ) ) {
		wp_register_ability( 'wpmcpui/get-tour-operator-context', array_merge( $base, array(
			'label'       => 'Get Tour Operator Context',
			'description' => 'Returns full developer context for Tour Operator sites: CPT slugs, meta keys, taxonomies, modal system, CSS classes, and Wetu field mappings.',
			'input_schema' => array( 'type' => 'object', 'properties' => array() ),
			'permission_callback' => function () { return current_user_can( 'edit_posts' ); },
			'execute_callback'   => 'wpmcpui_execute_get_tour_operator_context',
		) ) );
	}
}

// ─────────────────────────────────────────────────────────────────────────────
// EXECUTE CALLBACKS
// ─────────────────────────────────────────────────────────────────────────────

function wpmcpui_execute_get_posts( $input ) {
	$per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 10;
	$status   = isset( $input['status'] )   ? sanitize_text_field( $input['status'] ) : 'publish';
	if ( $status === 'all' ) {
		$status = array( 'publish', 'draft', 'pending', 'future' );
	}
	$q     = new WP_Query( array( 'post_status' => $status, 'posts_per_page' => $per_page, 'orderby' => 'date', 'order' => 'DESC' ) );
	$posts = array();
	foreach ( $q->posts as $p ) {
		$posts[] = array(
			'id'         => $p->ID,
			'title'      => $p->post_title,
			'url'        => get_permalink( $p->ID ),
			'status'     => $p->post_status,
			'date'       => get_the_date( 'Y-m-d', $p->ID ),
			'author'     => get_the_author_meta( 'display_name', $p->post_author ),
			'categories' => wp_get_post_categories( $p->ID, array( 'fields' => 'names' ) ),
			'tags'       => wp_get_post_tags( $p->ID, array( 'fields' => 'names' ) ),
			'excerpt'    => has_excerpt( $p->ID ) ? get_the_excerpt( $p ) : wp_trim_words( $p->post_content, 40 ),
		);
	}
	return array( 'posts' => $posts, 'total' => $q->found_posts );
}

function wpmcpui_execute_create_post( $input ) {
	$args = array(
		'post_title'   => sanitize_text_field( $input['title'] ),
		'post_content' => wp_kses_post( $input['content'] ),
		'post_status'  => isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'draft',
		'post_type'    => 'post',
	);
	if ( ! empty( $input['excerpt'] ) )    $args['post_excerpt']  = sanitize_text_field( $input['excerpt'] );
	if ( ! empty( $input['slug'] ) )       $args['post_name']     = sanitize_title( $input['slug'] );
	if ( ! empty( $input['categories'] ) ) $args['post_category'] = array_map( 'intval', $input['categories'] );
	$id = wp_insert_post( $args, true );
	if ( is_wp_error( $id ) ) return array( 'success' => false, 'error' => $id->get_error_message() );
	if ( ! empty( $input['tags'] ) ) wp_set_post_tags( $id, array_map( 'intval', $input['tags'] ) );
	return array( 'success' => true, 'id' => $id, 'url' => get_permalink( $id ), 'status' => $args['post_status'] );
}

function wpmcpui_execute_update_post( $input ) {
	$args = array( 'ID' => intval( $input['id'] ) );
	if ( isset( $input['title'] ) )      $args['post_title']    = sanitize_text_field( $input['title'] );
	if ( isset( $input['content'] ) )    $args['post_content']  = wp_kses_post( $input['content'] );
	if ( isset( $input['status'] ) )     $args['post_status']   = sanitize_text_field( $input['status'] );
	if ( isset( $input['categories'] ) ) $args['post_category'] = array_map( 'intval', $input['categories'] );
	$id = wp_update_post( $args, true );
	if ( is_wp_error( $id ) ) return array( 'success' => false, 'error' => $id->get_error_message() );
	if ( isset( $input['tags'] ) ) wp_set_post_tags( $id, array_map( 'intval', $input['tags'] ) );
	return array( 'success' => true, 'id' => $id, 'url' => get_permalink( $id ) );
}

function wpmcpui_execute_delete_post( $input ) {
	$id = intval( $input['id'] );
	return wp_trash_post( $id )
		? array( 'success' => true, 'message' => "Post {$id} moved to trash." )
		: array( 'success' => false, 'error' => 'Could not trash post.' );
}

function wpmcpui_execute_get_pages( $input ) {
	$pages  = get_pages( array( 'post_status' => 'publish' ) );
	$result = array();
	foreach ( $pages as $page ) {
		$result[] = array(
			'id'     => $page->ID,
			'title'  => $page->post_title,
			'url'    => get_permalink( $page->ID ),
			'parent' => $page->post_parent,
			'status' => $page->post_status,
		);
	}
	return array( 'pages' => $result );
}

function wpmcpui_execute_create_page( $input ) {
	$args = array(
		'post_title'   => sanitize_text_field( $input['title'] ),
		'post_content' => wp_kses_post( $input['content'] ),
		'post_status'  => isset( $input['status'] ) ? sanitize_text_field( $input['status'] ) : 'draft',
		'post_type'    => 'page',
	);
	if ( ! empty( $input['parent'] ) ) $args['post_parent'] = intval( $input['parent'] );
	if ( ! empty( $input['slug'] ) )   $args['post_name']   = sanitize_title( $input['slug'] );
	$id = wp_insert_post( $args, true );
	if ( is_wp_error( $id ) ) return array( 'success' => false, 'error' => $id->get_error_message() );
	return array( 'success' => true, 'id' => $id, 'url' => get_permalink( $id ) );
}

function wpmcpui_execute_update_page( $input ) {
	$args = array( 'ID' => intval( $input['id'] ), 'post_type' => 'page' );
	if ( isset( $input['title'] ) )   $args['post_title']   = sanitize_text_field( $input['title'] );
	if ( isset( $input['content'] ) ) $args['post_content'] = wp_kses_post( $input['content'] );
	if ( isset( $input['status'] ) )  $args['post_status']  = sanitize_text_field( $input['status'] );
	$id = wp_update_post( $args, true );
	if ( is_wp_error( $id ) ) return array( 'success' => false, 'error' => $id->get_error_message() );
	return array( 'success' => true, 'id' => $id, 'url' => get_permalink( $id ) );
}

function wpmcpui_execute_delete_page( $input ) {
	$id = intval( $input['id'] );
	return wp_trash_post( $id )
		? array( 'success' => true, 'message' => "Page {$id} moved to trash." )
		: array( 'success' => false, 'error' => 'Could not trash page.' );
}

function wpmcpui_execute_get_post_types( $input ) {
	$include_builtin = isset( $input['include_builtin'] ) ? (bool) $input['include_builtin'] : true;
	$post_types      = get_post_types( array( 'public' => true ), 'objects' );
	$result          = array();
	foreach ( $post_types as $type ) {
		if ( ! $include_builtin && in_array( $type->name, array( 'post', 'page', 'attachment' ), true ) ) {
			continue;
		}
		$result[] = array(
			'slug'        => $type->name,
			'label'       => $type->label,
			'singular'    => $type->labels->singular_name,
			'public'      => $type->public,
			'has_archive' => (bool) $type->has_archive,
			'supports'    => get_all_post_type_supports( $type->name ),
			'rest_base'   => isset( $type->rest_base ) && $type->rest_base ? $type->rest_base : $type->name,
			'menu_icon'   => $type->menu_icon,
		);
	}
	return array( 'post_types' => $result, 'total' => count( $result ) );
}

function wpmcpui_execute_get_cpt_items( $input ) {
	$post_type = sanitize_key( $input['post_type'] );
	if ( ! post_type_exists( $post_type ) ) {
		return array( 'success' => false, 'error' => "Post type '{$post_type}' is not registered." );
	}
	$per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 10;
	$status   = isset( $input['status'] )   ? sanitize_text_field( $input['status'] ) : 'publish';
	$orderby  = isset( $input['orderby'] )  ? sanitize_text_field( $input['orderby'] ) : 'date';
	$order    = isset( $input['order'] )    ? strtoupper( sanitize_text_field( $input['order'] ) ) : 'DESC';
	if ( $status === 'all' ) {
		$status = array( 'publish', 'draft', 'pending', 'future' );
	}
	$query_args = array(
		'post_type'      => $post_type,
		'post_status'    => $status,
		'posts_per_page' => $per_page,
		'orderby'        => in_array( $orderby, array( 'date', 'title', 'menu_order', 'modified' ), true ) ? $orderby : 'date',
		'order'          => in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC',
	);
	if ( ! empty( $input['search'] ) ) {
		$query_args['s'] = sanitize_text_field( $input['search'] );
	}
	$q      = new WP_Query( $query_args );
	$result = array();
	foreach ( $q->posts as $p ) {
		$item = array(
			'id'       => $p->ID,
			'title'    => $p->post_title,
			'url'      => get_permalink( $p->ID ),
			'status'   => $p->post_status,
			'slug'     => $p->post_name,
			'date'     => get_the_date( 'Y-m-d', $p->ID ),
			'modified' => get_the_modified_date( 'Y-m-d', $p->ID ),
			'author'   => get_the_author_meta( 'display_name', $p->post_author ),
			'excerpt'  => has_excerpt( $p->ID ) ? get_the_excerpt( $p ) : wp_trim_words( $p->post_content, 40 ),
		);
		// Include ACF fields if the plugin is active
		if ( function_exists( 'get_fields' ) ) {
			$acf_fields = get_fields( $p->ID );
			if ( $acf_fields ) {
				$item['custom_fields'] = $acf_fields;
			}
		}
		$result[] = $item;
	}
	return array( 'items' => $result, 'total' => $q->found_posts, 'post_type' => $post_type );
}

function wpmcpui_execute_get_categories( $input ) {
	$cats   = get_categories( array( 'hide_empty' => false ) );
	$result = array();
	foreach ( $cats as $c ) {
		$result[] = array( 'id' => $c->term_id, 'name' => $c->name, 'slug' => $c->slug, 'count' => $c->count, 'parent' => $c->parent );
	}
	return array( 'categories' => $result );
}

function wpmcpui_execute_create_category( $input ) {
	$args = array();
	if ( ! empty( $input['description'] ) ) $args['description'] = sanitize_text_field( $input['description'] );
	if ( ! empty( $input['parent'] ) )      $args['parent']      = intval( $input['parent'] );
	$result = wp_insert_term( sanitize_text_field( $input['name'] ), 'category', $args );
	if ( is_wp_error( $result ) ) return array( 'success' => false, 'error' => $result->get_error_message() );
	return array( 'success' => true, 'id' => $result['term_id'], 'name' => $input['name'] );
}

function wpmcpui_execute_get_tags( $input ) {
	$tags   = get_tags( array( 'hide_empty' => false ) );
	$result = array();
	foreach ( $tags as $t ) {
		$result[] = array( 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'count' => $t->count );
	}
	return array( 'tags' => $result );
}

function wpmcpui_execute_create_tag( $input ) {
	$args   = ! empty( $input['description'] ) ? array( 'description' => sanitize_text_field( $input['description'] ) ) : array();
	$result = wp_insert_term( sanitize_text_field( $input['name'] ), 'post_tag', $args );
	if ( is_wp_error( $result ) ) return array( 'success' => false, 'error' => $result->get_error_message() );
	return array( 'success' => true, 'id' => $result['term_id'], 'name' => $input['name'] );
}

function wpmcpui_execute_get_comments( $input ) {
	$per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 20;
	$status   = isset( $input['status'] )   ? sanitize_text_field( $input['status'] ) : '';
	$args     = array( 'number' => $per_page );
	if ( $status && $status !== 'all' ) $args['status'] = $status;
	$comments = get_comments( $args );
	$result   = array();
	foreach ( $comments as $c ) {
		$result[] = array(
			'id'      => $c->comment_ID,
			'post_id' => $c->comment_post_ID,
			'author'  => $c->comment_author,
			'email'   => $c->comment_author_email,
			'content' => wp_trim_words( $c->comment_content, 20 ),
			'status'  => $c->comment_approved,
			'date'    => $c->comment_date,
		);
	}
	return array( 'comments' => $result, 'total' => count( $result ) );
}

function wpmcpui_execute_approve_comment( $input ) {
	return wp_set_comment_status( intval( $input['id'] ), 'approve' )
		? array( 'success' => true, 'message' => 'Comment approved.' )
		: array( 'success' => false, 'error' => 'Failed to approve.' );
}

function wpmcpui_execute_delete_comment( $input ) {
	return wp_trash_comment( intval( $input['id'] ) )
		? array( 'success' => true, 'message' => 'Comment trashed.' )
		: array( 'success' => false, 'error' => 'Failed to trash comment.' );
}

function wpmcpui_execute_get_media( $input ) {
	$per_page = isset( $input['per_page'] ) ? intval( $input['per_page'] ) : 20;
	$args     = array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => $per_page );
	if ( ! empty( $input['type'] ) ) $args['post_mime_type'] = sanitize_text_field( $input['type'] );
	$q      = new WP_Query( $args );
	$result = array();
	foreach ( $q->posts as $item ) {
		$result[] = array(
			'id'    => $item->ID,
			'title' => $item->post_title,
			'url'   => wp_get_attachment_url( $item->ID ),
			'type'  => $item->post_mime_type,
			'date'  => get_the_date( 'Y-m-d', $item->ID ),
		);
	}
	return array( 'media' => $result, 'total' => $q->found_posts );
}

function wpmcpui_execute_get_users( $input ) {
	$users  = get_users();
	$result = array();
	foreach ( $users as $u ) {
		$result[] = array(
			'id'           => $u->ID,
			'display_name' => $u->display_name,
			'email'        => $u->user_email,
			'roles'        => $u->roles,
			'registered'   => $u->user_registered,
		);
	}
	return array( 'users' => $result );
}

function wpmcpui_execute_search( $input ) {
	$keyword   = isset( $input['query'] )     ? sanitize_text_field( $input['query'] ) : '';
	$post_type = isset( $input['post_type'] ) ? sanitize_key( $input['post_type'] )    : 'any';
	$per_page  = isset( $input['per_page'] )  ? intval( $input['per_page'] )           : 10;
	$q         = new WP_Query( array( 's' => $keyword, 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => $per_page ) );
	$results   = array();
	foreach ( $q->posts as $p ) {
		$results[] = array( 'id' => $p->ID, 'title' => $p->post_title, 'url' => get_permalink( $p->ID ), 'type' => $p->post_type );
	}
	return array( 'results' => $results, 'total' => $q->found_posts );
}

function wpmcpui_execute_get_site_info( $input ) {
	return array(
		'name'        => get_bloginfo( 'name' ),
		'url'         => get_site_url(),
		'tagline'     => get_bloginfo( 'description' ),
		'admin_email' => get_option( 'admin_email' ),
		'wp_version'  => get_bloginfo( 'version' ),
		'language'    => get_bloginfo( 'language' ),
	);
}

function wpmcpui_execute_get_plugins( $input ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all    = get_plugins();
	$active = get_option( 'active_plugins', array() );
	$result = array();
	foreach ( $active as $file ) {
		if ( isset( $all[ $file ] ) ) {
			$result[] = array(
				'name'    => $all[ $file ]['Name'],
				'version' => $all[ $file ]['Version'],
				'author'  => $all[ $file ]['Author'],
				'file'    => $file,
			);
		}
	}
	return array( 'active_plugins' => $result, 'total' => count( $result ) );
}

function wpmcpui_execute_get_patterns( $input ) {
	$source   = isset( $input['source'] ) ? sanitize_text_field( $input['source'] ) : 'all';
	$registry = WP_Block_Patterns_Registry::get_instance();
	$all      = $registry->get_all_registered();
	$theme_dir = trailingslashit( get_template_directory() ) . 'patterns/';
	$result   = array();
	foreach ( $all as $pattern ) {
		$is_theme_pattern = isset( $pattern['filePath'] ) && strpos( $pattern['filePath'], $theme_dir ) === 0;
		if ( $source === 'theme' && ! $is_theme_pattern ) continue;
		if ( $source === 'plugin' && $is_theme_pattern ) continue;
		$result[] = array(
			'name'           => $pattern['name'],
			'title'          => $pattern['title'],
			'description'    => $pattern['description'] ?? '',
			'categories'     => $pattern['categories'] ?? array(),
			'keywords'       => $pattern['keywords'] ?? array(),
			'block_types'    => $pattern['blockTypes'] ?? array(),
			'viewport_width' => $pattern['viewportWidth'] ?? null,
			'inserter'       => $pattern['inserter'] ?? true,
			'source'         => $is_theme_pattern ? 'theme' : 'plugin',
			'file'           => $is_theme_pattern ? basename( $pattern['filePath'] ) : null,
			'content'        => $pattern['content'],
		);
	}
	return array( 'patterns' => $result, 'total' => count( $result ) );
}

function wpmcpui_execute_create_pattern( $input ) {
	$filename = isset( $input['filename'] ) ? sanitize_file_name( $input['filename'] ) : '';
	if ( ! $filename || pathinfo( $filename, PATHINFO_EXTENSION ) !== 'php' ) {
		return array( 'success' => false, 'error' => 'filename must be a .php file with no path separators.' );
	}
	$patterns_dir = trailingslashit( get_template_directory() ) . 'patterns/';
	$file_path    = $patterns_dir . $filename;
	if ( file_exists( $file_path ) ) {
		return array( 'success' => false, 'error' => "Pattern file '{$filename}' already exists. Use update-pattern to overwrite it." );
	}
	// Build the PHP header comment.
	$title          = sanitize_text_field( $input['title'] );
	$slug           = sanitize_text_field( $input['slug'] );
	$description    = isset( $input['description'] )    ? sanitize_text_field( $input['description'] )    : '';
	$categories     = isset( $input['categories'] )     ? sanitize_text_field( $input['categories'] )     : '';
	$block_types    = isset( $input['block_types'] )    ? sanitize_text_field( $input['block_types'] )    : '';
	$viewport_width = isset( $input['viewport_width'] ) ? intval( $input['viewport_width'] )              : 1280;
	$inserter       = isset( $input['inserter'] )       ? ( (bool) $input['inserter'] ? 'true' : 'false' ) : 'true';
	$header = "<?php\n/**\n * Title: {$title}\n * Slug: {$slug}\n";
	if ( $description )    $header .= " * Description: {$description}\n";
	if ( $categories )     $header .= " * Categories: {$categories}\n";
	if ( $block_types )    $header .= " * Block Types: {$block_types}\n";
	$header .= " * Viewport Width: {$viewport_width}\n";
	$header .= " * Inserter: {$inserter}\n";
	$header .= " */\n";
	$content  = $header . "\n" . $input['content'];
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$written = file_put_contents( $file_path, $content );
	if ( $written === false ) {
		return array( 'success' => false, 'error' => "Could not write to {$file_path}. Check directory permissions." );
	}
	return array( 'success' => true, 'filename' => $filename, 'path' => $file_path, 'bytes' => $written );
}

function wpmcpui_execute_update_pattern( $input ) {
	$filename = isset( $input['filename'] ) ? sanitize_file_name( $input['filename'] ) : '';
	if ( ! $filename || pathinfo( $filename, PATHINFO_EXTENSION ) !== 'php' ) {
		return array( 'success' => false, 'error' => 'filename must be a .php file with no path separators.' );
	}
	$patterns_dir = trailingslashit( get_template_directory() ) . 'patterns/';
	$file_path    = $patterns_dir . $filename;
	if ( ! file_exists( $file_path ) ) {
		return array( 'success' => false, 'error' => "Pattern file '{$filename}' does not exist. Use create-pattern to create it." );
	}
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	$written = file_put_contents( $file_path, $input['content'] );
	if ( $written === false ) {
		return array( 'success' => false, 'error' => "Could not write to {$file_path}. Check directory permissions." );
	}
	return array( 'success' => true, 'filename' => $filename, 'path' => $file_path, 'bytes' => $written );
}

function wpmcpui_execute_delete_pattern( $input ) {
	$filename = isset( $input['filename'] ) ? sanitize_file_name( $input['filename'] ) : '';
	if ( ! $filename || pathinfo( $filename, PATHINFO_EXTENSION ) !== 'php' ) {
		return array( 'success' => false, 'error' => 'filename must be a .php file with no path separators.' );
	}
	$patterns_dir = trailingslashit( get_template_directory() ) . 'patterns/';
	$file_path    = $patterns_dir . $filename;
	if ( ! file_exists( $file_path ) ) {
		return array( 'success' => false, 'error' => "Pattern file '{$filename}' does not exist." );
	}
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_unlink
	if ( ! unlink( $file_path ) ) {
		return array( 'success' => false, 'error' => "Could not delete '{$filename}'. Check file permissions." );
	}
	return array( 'success' => true, 'filename' => $filename, 'message' => "Pattern '{$filename}' deleted." );
}

function wpmcpui_execute_get_tour_operator_context( $input ) {
	return array(
		'plugin' => array(
			'name'    => 'LSX Tour Operator',
			'version' => defined( 'LSX_TO_VER' ) ? LSX_TO_VER : 'unknown',
			'active'  => class_exists( 'LSX_TO' ),
		),
		'post_types' => array(
			'tour' => array(
				'label'        => 'Tours',
				'singular'     => 'Tour',
				'hierarchical' => false,
				'description'  => 'Travel/safari tour packages.',
			),
			'accommodation' => array(
				'label'        => 'Accommodations',
				'singular'     => 'Accommodation',
				'hierarchical' => false,
				'description'  => 'Lodges, hotels, and safari camps.',
			),
			'destination' => array(
				'label'        => 'Destinations',
				'singular'     => 'Destination',
				'hierarchical' => true,
				'description'  => 'Travel destinations. Supports parent/child hierarchy for country → region nesting.',
			),
		),
		'taxonomies' => array(
			'accommodation-type' => array(
				'label'       => 'Accommodation Types',
				'hierarchical' => true,
				'applies_to'  => array( 'accommodation' ),
				'description' => 'Type classification (lodge, hotel, camp, etc.).',
			),
			'accommodation-brand' => array(
				'label'       => 'Accommodation Brands',
				'hierarchical' => true,
				'applies_to'  => array( 'accommodation' ),
				'description' => 'Brand or group the accommodation belongs to.',
			),
			'facility' => array(
				'label'       => 'Facilities',
				'hierarchical' => true,
				'applies_to'  => array( 'accommodation' ),
				'description' => 'Amenity and facility terms (pool, wifi, spa, etc.). Populated by Wetu importer from available_services, property_facilities, room_facilities, activities_on_site fields.',
			),
			'travel-style' => array(
				'label'       => 'Travel Styles',
				'hierarchical' => true,
				'applies_to'  => array( 'accommodation', 'tour', 'destination' ),
				'description' => 'Travel style classification (luxury, adventure, family, etc.).',
			),
			'continent' => array(
				'label'       => 'Continents',
				'hierarchical' => true,
				'applies_to'  => array( 'destination' ),
				'description' => 'Geographic continent grouping for destinations.',
			),
		),
		'meta_keys' => array(
			'tour' => array(
				'lsx_wetu_id'            => 'Wetu API entity ID (string). Links the post to its Wetu CMS record.',
				'lsx_wetu_ref'           => 'Wetu reference number (string).',
				'lsx_wetu_modified_date' => 'ISO timestamp of last successful Wetu sync.',
				'price'                  => 'Tour price (numeric or formatted string).',
				'duration'               => 'Number of days (integer).',
				'group_size'             => 'Group size description (string).',
				'departs_from'           => 'Starting destination name (string). Mapped from Wetu starts_in_text.',
				'ends_in'                => 'Ending destination name (string). Mapped from Wetu ends_in_text.',
				'highlights'             => 'Tour highlights (HTML/WYSIWYG stored string).',
				'included'               => 'Items included in the tour price (HTML/WYSIWYG).',
				'not_included'           => 'Items not included in the price (HTML/WYSIWYG).',
				'booking_validity_start' => 'Booking validity start date (Y-m-d string).',
				'booking_validity_end'   => 'Booking validity end date (Y-m-d string).',
				'itinerary'              => 'Serialized array of day objects. Each object has: title, tagline, description (HTML), featured_image (attachment ID), accommodation_to_tour (post ID), destination_to_tour (post ID), included, excluded, drinks_basis, room_basis.',
				'wetu_map_points'        => 'Serialized array of GPS coordinate objects representing the tour route.',
				'accommodation_to_tour'  => 'Serialized array of accommodation post IDs linked to this tour.',
				'destination_to_tour'    => 'Serialized array of destination post IDs linked to this tour.',
				'post_to_tour'           => 'Serialized array of blog post IDs related to this tour.',
				'banner_image'           => 'Attachment ID of the banner/hero image.',
				'gallery'                => 'Serialized array of attachment IDs for the main gallery.',
			),
			'accommodation' => array(
				'lsx_wetu_id'              => 'Wetu API entity ID (string).',
				'lsx_wetu_modified_date'   => 'ISO timestamp of last Wetu sync.',
				'tagline'                  => 'Short one-line description (string).',
				'price'                    => 'Base price.',
				'price_type'               => 'Price basis: per_person_per_night | per_person_sharing | per_unit_per_night.',
				'sale_price'               => 'Discounted/sale price.',
				'single_supplement'        => 'Single occupancy surcharge.',
				'rating'                   => 'Star rating 0–5 (integer). Wetu stars value is decremented by 1 on import.',
				'rating_type'              => 'Rating authority: tgcsa | hotelstars_union | unspecified.',
				'number_of_rooms'          => 'Total room/unit count (integer).',
				'checkin_time'             => 'Check-in time (formatted string, e.g. 2:00pm).',
				'checkout_time'            => 'Check-out time (formatted string).',
				'best_time_to_visit'       => 'Serialized array of month strings.',
				'spoken_languages'         => 'Serialized array of language slugs.',
				'suggested_visitor_types'  => 'Serialized array of visitor type slugs: business, children, disability, leisure, luxury, pet, romance, vegetarian, weddings.',
				'special_interests'        => 'Serialized array of interest slugs: adventure, beach, big-5, birding, cycling, fishing, golf, gourmet, hiking, history, wildlife, wine, etc.',
				'included'                 => 'Included services (HTML/WYSIWYG).',
				'not_included'             => 'Excluded services (HTML/WYSIWYG).',
				'units'                    => 'Serialized array of room/unit objects. Each has: type (select), title, description (HTML), price, gallery (attachment IDs array).',
				'location'                 => 'Serialized location object: address (string), latitude, longitude, zoom, elevation.',
				'map_placeholder'          => 'Attachment ID of the map placeholder image.',
				'banner_image'             => 'Attachment ID of the banner/hero image.',
				'gallery'                  => 'Serialized array of attachment IDs.',
				'tour_to_accommodation'    => 'Serialized array of tour post IDs linked to this accommodation.',
				'destination_to_accommodation' => 'Serialized array of destination post IDs.',
				'post_to_accommodation'    => 'Serialized array of related blog post IDs.',
				'team_to_accommodation'    => 'Post ID of the team member / expert for this property.',
			),
			'destination' => array(
				'lsx_wetu_id'                => 'Wetu API entity ID (string).',
				'lsx_wetu_modified_date'     => 'ISO timestamp of last Wetu sync.',
				'tagline'                    => 'Short one-line description (string).',
				'best_time_to_visit'         => 'Serialized array of month strings.',
				'location'                   => 'Serialized location object: address, latitude, longitude, zoom, elevation.',
				'map_placeholder'            => 'Attachment ID of map placeholder image.',
				'banner_image'               => 'Attachment ID of banner/hero image.',
				'gallery'                    => 'Serialized array of attachment IDs.',
				'electricity'                => 'Electricity/power information (HTML).',
				'banking'                    => 'Banking and currency information (HTML).',
				'cuisine'                    => 'Food and dining information (HTML).',
				'climate'                    => 'Climate information (HTML).',
				'transport'                  => 'Transportation information (HTML).',
				'dress'                      => 'Dress code and packing tips (HTML).',
				'health'                     => 'Health and vaccination information (HTML).',
				'safety'                     => 'Safety information (HTML).',
				'visa'                       => 'Visa requirements (HTML).',
				'additional_info'            => 'General travel information (HTML).',
				'tour_to_destination'        => 'Serialized array of tour post IDs.',
				'accommodation_to_destination' => 'Serialized array of accommodation post IDs.',
				'post_to_destination'        => 'Serialized array of related blog post IDs.',
				'disable_auto_zoom'          => 'Boolean. Disables automatic map zoom on the destination map.',
			),
		),
		'modal_system' => array(
			'description'    => 'Tour Operator modals use the hm-popup block system. Modals are <dialog> elements rendered as template parts in the "modals" template part area.',
			'settings_option' => 'lsx_to_settings',
			'per_type_enable' => array(
				'key_pattern' => '{post_type}_enable_modals',
				'example'     => 'tour_enable_modals',
				'values'      => array( '1' => 'enabled', '' => 'disabled' ),
			),
			'per_type_template' => array(
				'key_pattern' => '{post_type}_modal_template',
				'defaults'    => array(
					'tour'          => 'modal-tour',
					'accommodation' => 'modal-accommodation',
					'destination'   => 'modal-destination',
				),
			),
			'html_structure' => array(
				'dialog_id_format'          => 'to-modal-{post_id}',
				'dialog_class'              => 'wp-block-hm-popup',
				'close_button_class'        => 'wp-block-hm-popup__close',
				'close_button_data_attr'    => 'data-close',
				'trigger_data_attr'         => 'data-trigger',
				'trigger_values'            => array( 'click', 'exit' ),
				'expiry_data_attr'          => 'data-expiry',
				'backdrop_opacity_attr'     => 'data-backdrop-opacity',
				'backdrop_opacity_default'  => 0.75,
			),
			'trigger_link_format' => 'To open a modal on click, use href="#to-modal-{post_id}". The {post_id} must match the dialog element\'s id attribute.',
			'block_name'          => 'lsx-tour-operator/modal-button',
			'block_attributes'    => array( 'text', 'modalId', 'buttonStyle', 'align', 'width' ),
			'rest_endpoint'       => array(
				'route'       => 'tour-operator/v1/modal-options',
				'method'      => 'GET',
				'description' => 'Returns available modal template options for use in the block editor.',
				'capability'  => 'edit_posts',
				'nonce_handle' => 'lsx-to-block-modal-button',
				'nonce_global' => 'lsxModalButtonOptions',
			),
			'javascript' => array(
				'exit_intent'   => 'Tracks exit-intent state in localStorage key "exitIntentShown".',
				'click_trigger' => 'Listens on all [href^="#to-modal-"] anchor elements.',
				'build_file'    => 'build/modals.js (minified)',
			),
			'template_part_area' => 'modals',
		),
		'css_classes' => array(
			'lsx_wrappers' => array(
				'lsx-to-section'        => 'Generic Tour Operator section wrapper.',
				'lsx-to-section-view-all' => 'Section "View All" link wrapper.',
				'lsx-price-wrapper'     => 'Price display wrapper. Contains a child .amount element.',
				'lsx-duration-wrapper'  => 'Duration display wrapper.',
				'lsx-map'               => 'Map embed wrapper.',
				'lsx-block-videos'      => 'YouTube video collection wrapper.',
				'lsx-responsive'        => 'Responsive image class (also applied alongside .attachment-responsive and .wp-post-image).',
				'facilities-title'      => 'Facilities section heading.',
				'facilities-list'       => 'Facilities unordered list (also has .wp-block-list).',
			),
			'modal_classes' => array(
				'wp-block-hm-popup'       => 'The <dialog> modal element.',
				'wp-block-hm-popup__close' => 'Modal close button.',
			),
			'block_classes' => array(
				'wp-block-columns'             => 'Core columns block container.',
				'wp-block-column'              => 'Individual column in a columns block.',
				'wp-block-image'               => 'Core image block.',
				'wp-block-cover'               => 'Core cover block.',
				'wp-block-post-featured-image' => 'Post featured image block.',
				'wp-block-post-featured-image__overlay' => 'Overlay layer inside the featured image block.',
				'wp-block-embed is-type-video is-provider-youtube' => 'YouTube video embed block.',
				'wp-block-template-part'       => 'Template part block.',
			),
		),
		'wetu_importer' => array(
			'plugin'  => 'LSX Importer for Wetu',
			'version' => defined( 'LSX_WETU_IMPORTER_VER' ) ? LSX_WETU_IMPORTER_VER : 'unknown',
			'active'  => class_exists( 'LSX_WETU_Importer' ),
			'api_endpoints' => array(
				'search_pins'      => 'https://wetu.com/API/Pins/{api_key}/Search/{keyword}',
				'get_pin'          => 'https://wetu.com/API/Pins/{api_key}/Get?ids={wetu_id}',
				'list_itineraries' => 'https://wetu.com/API/Itinerary/{api_key}/V8/List',
			),
			'image_handler' => array(
				'format'       => 'https://wetu.com/ImageHandler/{cropping}{width}x{height}/{url_fragment}',
				'default_size' => '1024x768',
				'crop_modes'   => array( 'h' => 'height-fit', 'w' => 'width-fit', 'c' => 'crop' ),
			),
			'wordpress_options' => array(
				'lsx_wetu_get_options()'                      => 'Main importer settings retrieval function.',
				'lsx_wetu_importer_accommodation_settings'    => 'Per-accommodation import settings option.',
				'lsx_wetu_importer_que'                       => 'Queue array of post IDs pending background import.',
			),
			'field_mappings' => array(
				'accommodation' => array(
					'name → post_title',
					'content.extended_description → post_content (primary)',
					'content.general_description → post_content (fallback)',
					'content.teaser_description → post_excerpt',
					'last_modified → lsx_wetu_modified_date',
					'category → accommodation-type taxonomy term',
					'position.driving_latitude/longitude → location.latitude/longitude',
					'position.latitude/longitude → location (fallback coordinates)',
					'content.contact_information.address → location.address',
					'rooms[] → units[] (title, description, images→gallery)',
					'features.rooms → number_of_rooms',
					'features.stars → rating (Wetu value decremented by 1)',
					'features.star_authority → rating_type',
					'features.spoken_languages → spoken_languages (sanitized slugs)',
					'features.suggested_visitor_types → suggested_visitor_types',
					'features.special_interests → special_interests',
					'features.check_in_time → checkin_time',
					'features.check_out_time → checkout_time',
					'features.available_services + property_facilities + room_facilities + activities_on_site → facility taxonomy terms',
					'content.images[0] → _thumbnail_id (featured image)',
					'content.images[1] → banner_image attachment ID',
					'content.images[] → gallery (all images)',
					'content.youtube_videos → videos meta',
					'id param → lsx_wetu_id',
				),
				'tour' => array(
					'name → post_title',
					'reference_number → lsx_wetu_ref',
					'last_modified → lsx_wetu_modified_date',
					'description → post_content',
					'price → price',
					'duration → duration',
					'group_size → group_size',
					'starts_in_text → departs_from',
					'ends_in_text → ends_in',
					'itinerary[].destination_content_entity_id → destination_to_tour',
					'itinerary[].content_entity_id → accommodation_to_tour',
					'itinerary[] → itinerary[] repeatable meta group',
					'GPS coordinate array → wetu_map_points',
					'images[0] → _thumbnail_id',
					'images[1] → banner_image',
					'images[] → gallery',
					'id param → lsx_wetu_id',
				),
				'destination' => array(
					'name → post_title',
					'last_modified → lsx_wetu_modified_date',
					'position.country_content_entity_id → post_parent (hierarchical)',
					'id param → lsx_wetu_id',
				),
			),
			'import_content_options' => array(
				'description'      => 'Imports extended description → post_content',
				'excerpt'          => 'Imports teaser description → post_excerpt',
				'gallery'          => 'Imports main image array → gallery meta',
				'category'         => 'Sets accommodation-type taxonomy term',
				'location'         => 'Imports GPS/address data → location meta',
				'destination'      => 'Connects to matching destination post',
				'rating'           => 'Imports star rating integer',
				'rooms'            => 'Imports room/unit data → units meta',
				'checkin'          => 'Imports check-in and check-out times',
				'facilities'       => 'Imports facility taxonomy terms',
				'friendly'         => 'Imports suggested_visitor_types meta',
				'special_interests' => 'Imports special_interests meta array',
				'spoken_languages' => 'Imports spoken_languages meta array',
				'videos'           => 'Imports YouTube video URLs → videos meta',
				'featured_image'   => 'Downloads and sets post featured image (_thumbnail_id)',
				'banner_image'     => 'Downloads and sets banner_image meta',
			),
		),
		'developer_notes' => array(
			'meta_storage'         => 'All multi-value meta (gallery, itinerary, units, etc.) is stored as PHP serialized arrays via update_post_meta(). Retrieve with get_post_meta($id, "gallery", true) — WordPress automatically unserializes.',
			'relationship_storage' => 'Relationships are stored bidirectionally: a tour stores accommodation_to_tour[], and the linked accommodation stores tour_to_accommodation[] with matching post IDs.',
			'wetu_sync_detection'  => 'Use lsx_wetu_id meta to identify Wetu-sourced posts. lsx_wetu_modified_date tracks when the last sync ran.',
			'modal_template_area'  => 'Modal template parts must be registered under the "modals" template part area in block themes.',
			'acf_compatibility'    => 'The get-cpt-items ability includes ACF fields automatically when ACF is active. Tour Operator meta is stored via CMB2, so it appears under the standard meta key names listed above rather than ACF field keys.',
			'pattern_file_header'  => 'Theme pattern PHP files must start with a header comment block. Use create-pattern to generate the header automatically, or supply the full file content to update-pattern.',
		),
	);
}
