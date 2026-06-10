<?php
/**
 * Admin page: Tools > LightSpeed MCP
 *
 * @package LightSpeed\MCP_UI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LSX_MCP_UI_Admin_Page
 */
class LSX_MCP_UI_Admin_Page {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function register_menu() {
		add_management_page(
			'LightSpeed MCP',
			'LightSpeed MCP',
			'manage_options',
			'lsx-mcp',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( 'tools_page_lsx-mcp' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'lsx-mcp-admin',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/admin.css',
			array(),
			LSXMCPUI_VERSION
		);
	}

	// ── Page render ───────────────────────────────────────────────────────────

	public static function render_page() {
		// Handle settings form submission before any output.
		if ( isset( $_POST['lsx_mcp_save_settings'] ) ) {
			$result   = LSX_MCP_UI_Settings::save_from_post();
			$redirect = admin_url( 'tools.php?page=lsx-mcp&tab=settings' );
			if ( is_wp_error( $result ) ) {
				$redirect = add_query_arg( 'lsx_mcp_error', rawurlencode( $result->get_error_message() ), $redirect );
			} else {
				$redirect = add_query_arg( 'lsx_mcp_saved', '1', $redirect );
			}
			wp_safe_redirect( $redirect );
			exit;
		}

		// ── Gather state ─────────────────────────────────────────────────────
		$env          = LSX_MCP_UI_Environment::get_environment_type();
		$host         = LSX_MCP_UI_Environment::get_current_host();
		$op_env       = LSX_MCP_UI_Environment::get_operational_environment();
		$allowed_envs = LSX_MCP_UI_Environment::get_allowed_environments();
		$is_env_ok    = LSX_MCP_UI_Environment::is_dev_environment();
		$host_ok      = LSX_MCP_UI_Environment::is_allowed_host();
		$prod_in_list = LSX_MCP_UI_Environment::is_production_in_allowed_list();
		$mcp_on       = LSX_MCP_UI_Environment::is_mcp_enabled();
		$app_pass     = LSX_MCP_UI_Environment::is_safe_for_application_passwords();
		$app_en       = LSX_MCP_UI_Environment::is_application_passwords_enabled();
		$adapter      = LSX_MCP_UI_Environment::is_adapter_available();
		$custom_srv   = LSX_MCP_UI_Environment::is_safe_for_custom_server();
		$dedicated    = LSX_MCP_UI_Environment::get_dedicated_user_login();
		$dev_suffix   = LSX_MCP_UI_Environment::get_allowed_dev_suffix();
		$default_url  = LSX_MCP_UI_Config_Generator::get_default_server_rest_url();
		$ls_url       = LSX_MCP_UI_Config_Generator::get_lightspeed_server_rest_url();
		$app_pass_wp  = function_exists( 'wp_is_application_passwords_available' ) ? wp_is_application_passwords_available() : false;
		$studio_path   = untrailingslashit( ABSPATH );
		$current_user  = wp_get_current_user();
		$wp_user       = $current_user->exists() ? $current_user->user_login : 'admin';
		$is_ls_domain  = ( 'lightspeed-dev-domain' === $op_env );

		// Plugin integration detection.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$has_tour_op    = class_exists( 'LSX_TO' ) || is_plugin_active( 'tour-operator/tour-operator.php' );
		$tour_op_ver    = $has_tour_op && defined( 'LSX_TO_VER' ) ? LSX_TO_VER : null;
		$has_woocommerce = class_exists( 'WooCommerce' );
		$woo_ver         = $has_woocommerce && defined( 'WC_VERSION' ) ? WC_VERSION : null;

		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'status';
		?>
		<div class="wrap lsx-mcp-wrap">

			<div class="lsx-mcp-header">
				<h1>LightSpeed MCP</h1>
				<span class="lsx-mcp-badge">v<?php echo esc_html( LSXMCPUI_VERSION ); ?></span>
			</div>

			<?php
			// Settings save notices.
			if ( ! empty( $_GET['lsx_mcp_saved'] ) ) {
				echo '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
			}
			if ( ! empty( $_GET['lsx_mcp_error'] ) ) {
				echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> ' . esc_html( urldecode( $_GET['lsx_mcp_error'] ) ) . '</p></div>';
			}

			// Environment / feature notices.
			if ( 'blocked' === $op_env ) {
				if ( $prod_in_list ) {
					echo '<div class="notice notice-error"><p><strong>Critical security warning:</strong> <code>production</code> is listed in <code>LSX_MCP_ALLOWED_ENVIRONMENTS</code>. MCP is now active on a production environment. Review all exposed abilities and transport security before proceeding.</p></div>';
				} else {
					echo '<div class="notice notice-error"><p><strong>Blocked:</strong> This host (<code>' . esc_html( $host ) . '</code>) is not a recognised LightSpeed dev domain and the WordPress environment type is <code>' . esc_html( $env ) . '</code>. LightSpeed MCP features will not activate. If this is a development or test site, update the settings on the <strong>Settings</strong> tab or set <code>WP_ENVIRONMENT_TYPE</code> in <code>wp-config.php</code>.</p></div>';
				}
			} elseif ( $is_ls_domain && 'production' === $env ) {
				echo '<div class="notice notice-info"><p><strong>Note:</strong> This host (<code>' . esc_html( $host ) . '</code>) ends in <code>' . esc_html( $dev_suffix ) . '</code> and is recognised as a <strong>LightSpeed dev domain</strong>, even though WordPress reports the environment type as <code>production</code>. MCP features can be enabled here — the host pattern overrides the environment type.</p></div>';
			} elseif ( 'staging' === $op_env && ! $is_env_ok ) {
				echo '<div class="notice notice-warning"><p><strong>Staging detected.</strong> MCP is blocked on staging by default. To allow it, add to <code>wp-config.php</code>: <code>define( \'LSX_MCP_ALLOWED_ENVIRONMENTS\', array( \'local\', \'development\', \'staging\' ) );</code></p></div>';
			} elseif ( ! $is_env_ok ) {
				echo '<div class="notice notice-warning"><p>Environment <code>' . esc_html( $env ) . '</code> (operational: <code>' . esc_html( $op_env ) . '</code>) is not allowed. Allowed list: <code>' . esc_html( implode( ', ', $allowed_envs ) ) . '</code>.</p></div>';
			} elseif ( ! $mcp_on ) {
				$settings_url = admin_url( 'tools.php?page=lsx-mcp&tab=settings' );
				echo '<div class="notice notice-warning"><p>MCP is <strong>not enabled</strong>. <a href="' . esc_url( $settings_url ) . '">Enable it in Settings</a>, or add <code>define( \'LSX_MCP_ENABLED\', true );</code> to <code>wp-config.php</code>.</p></div>';
			} elseif ( ! $adapter ) {
				echo '<div class="notice notice-warning"><p><strong>WordPress MCP Adapter</strong> is not active. Install and activate it to use MCP servers.</p></div>';
			} elseif ( $mcp_on && $adapter ) {
				echo '<div class="notice notice-success"><p>MCP is enabled and the adapter is active. This site is ready for MCP connections.</p></div>';
			}
			?>

			<?php
			// Redirect legacy tab IDs so old bookmarks still work.
			$tab_aliases = array( 'server' => 'reference', 'troubleshoot' => 'reference', 'local' => 'connect' );
			if ( isset( $tab_aliases[ $active_tab ] ) ) {
				$active_tab = $tab_aliases[ $active_tab ];
			}
			?>
			<?php /* ── TABS ── */ ?>
			<nav class="lsx-mcp-tabs" aria-label="Dashboard sections">
				<button type="button" class="lsx-mcp-tab<?php echo 'status' === $active_tab ? ' active' : ''; ?>" data-tab="status">Status</button>
				<button type="button" class="lsx-mcp-tab<?php echo 'settings' === $active_tab ? ' active' : ''; ?>" data-tab="settings">Settings</button>
				<button type="button" class="lsx-mcp-tab<?php echo 'connect' === $active_tab ? ' active' : ''; ?>" data-tab="connect">Local Setup</button>
				<button type="button" class="lsx-mcp-tab<?php echo 'devsite' === $active_tab ? ' active' : ''; ?>" data-tab="devsite">Dev Site</button>
				<button type="button" class="lsx-mcp-tab<?php echo 'security' === $active_tab ? ' active' : ''; ?>" data-tab="security">Security</button>
				<button type="button" class="lsx-mcp-tab<?php echo 'reference' === $active_tab ? ' active' : ''; ?>" data-tab="reference">Reference</button>
				<button type="button" class="lsx-mcp-tab<?php echo 'docs' === $active_tab ? ' active' : ''; ?>" data-tab="docs">Docs</button>
			</nav>

			<?php /* ──────────────────── STATUS ──────────────────── */ ?>
			<div id="lsx-mcp-tab-status" class="lsx-mcp-panel<?php echo 'status' === $active_tab ? ' active' : ''; ?>">
				<h2>Status</h2>

				<table class="widefat lsx-mcp-status-table">
					<thead>
						<tr><th>Check</th><th>Result</th><th>Notes</th></tr>
					</thead>
					<tbody>
						<tr>
							<td>WordPress environment type</td>
							<td><code><?php echo esc_html( $env ); ?></code></td>
							<td>From <code>wp_get_environment_type()</code> / <code>WP_ENVIRONMENT_TYPE</code></td>
						</tr>
						<tr>
							<td>Operational MCP environment</td>
							<td>
								<?php
								$op_labels = array(
									'local'                 => '<span class="lsx-mcp-badge-ok">local</span>',
									'lightspeed-dev-domain' => '<span class="lsx-mcp-badge-ok">lightspeed-dev-domain</span>',
									'development'           => '<span class="lsx-mcp-badge-ok">development</span>',
									'staging'               => '<span class="lsx-mcp-badge-warn">staging</span>',
									'blocked'               => '<span class="lsx-mcp-badge-off">blocked</span>',
								);
								echo $op_labels[ $op_env ] ?? esc_html( $op_env );
								?>
							</td>
							<td>
								<?php
								switch ( $op_env ) {
									case 'local':
										echo 'Host is localhost or ends in .local / .test / .localhost';
										break;
									case 'lightspeed-dev-domain':
										echo 'Host ends in <code>' . esc_html( $dev_suffix ) . '</code> — recognised as LightSpeed dev domain regardless of WP environment type';
										break;
									case 'development':
										echo 'WP environment type is <code>local</code> or <code>development</code>';
										break;
									case 'staging':
										echo $is_env_ok
											? 'Staging allowed via <code>LSX_MCP_ALLOWED_ENVIRONMENTS</code>'
											: 'Staging is blocked by default — add staging to <code>LSX_MCP_ALLOWED_ENVIRONMENTS</code> to allow';
										break;
									case 'blocked':
										echo 'Public host with production environment type — MCP will not activate';
										break;
								}
								?>
							</td>
						</tr>
						<tr>
							<td>Host allowed</td>
							<td><?php echo self::badge( $host_ok ); ?></td>
							<td>
								<code><?php echo esc_html( $host ); ?></code>
								<?php if ( ! $host_ok ) {
									echo ' — must be local-like or end in <code>' . esc_html( $dev_suffix ) . '</code>';
								} ?>
							</td>
						</tr>
						<tr>
							<td>MCP enabled</td>
							<td><?php echo self::badge( $mcp_on ); ?></td>
							<td>
								<?php
								if ( LSX_MCP_UI_Settings::is_constant_override( 'enabled' ) ) {
									echo $mcp_on ? 'Set by <code>LSX_MCP_ENABLED</code> constant' : 'Disabled by <code>LSX_MCP_ENABLED</code> constant';
								} elseif ( $mcp_on ) {
									echo 'Enabled via plugin settings';
								} else {
									echo 'Enable in <a href="' . esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ) . '">Settings</a> or set <code>LSX_MCP_ENABLED</code>';
								}
								?>
							</td>
						</tr>
						<tr>
							<td>App Password compatibility</td>
							<td>
								<?php
								if ( $app_pass ) {
									echo '<span class="lsx-mcp-badge-ok">Yes</span>';
								} elseif ( 'local' === $op_env && ! $app_en ) {
									echo '<span class="lsx-mcp-badge-na">N/A</span>';
								} else {
									echo self::badge( $app_pass );
								}
								?>
							</td>
							<td>
								<?php
								if ( $app_pass ) {
									echo 'Enabled — Application Passwords are available for HTTP transport';
								} elseif ( 'local' === $op_env && ! $app_en ) {
									echo 'Not required for local STDIO transport. Only needed when connecting via HTTP from a <a href="' . esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=devsite' ) ) . '">dev site</a>.';
								} elseif ( 'blocked' === $op_env ) {
									echo 'Blocked: not an allowed host/environment';
								} elseif ( ! $mcp_on ) {
									echo 'Requires MCP to be enabled first';
								} elseif ( ! $app_en ) {
									if ( LSX_MCP_UI_Settings::is_constant_override( 'enable_application_passwords' ) ) {
										echo 'Disabled by <code>LSX_MCP_ENABLE_APPLICATION_PASSWORDS</code> constant';
									} else {
										echo 'Enable in <a href="' . esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ) . '">Settings</a> or set <code>LSX_MCP_ENABLE_APPLICATION_PASSWORDS</code>';
									}
								} elseif ( ! $host_ok ) {
									echo 'Host <code>' . esc_html( $host ) . '</code> does not match any allowed host pattern';
								} else {
									echo 'Check environment and host settings';
								}
								?>
							</td>
						</tr>
						<tr>
							<td>Custom Testing MCP Server</td>
							<td><?php echo $custom_srv ? '<span class="lsx-mcp-badge-ok">Registered</span>' : '<span class="lsx-mcp-badge-off">Not registered</span>'; ?></td>
							<td>
								<?php
								if ( $custom_srv ) {
									echo 'Available at <code>' . esc_html( $ls_url ) . '</code>';
								} elseif ( ! $mcp_on ) {
									echo 'Requires MCP to be enabled';
								} elseif ( ! $adapter ) {
									echo 'MCP Adapter plugin not active';
								} elseif ( ! $is_env_ok ) {
									echo 'Environment not allowed (<code>' . esc_html( $op_env ) . '</code>)';
								} else {
									echo 'Not enabled — check <a href="' . esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ) . '">Settings</a>';
								}
								?>
							</td>
						</tr>
						<tr>
							<td>MCP Adapter available</td>
							<td><?php echo self::badge( $adapter ); ?></td>
							<td><?php echo $adapter ? '<code>WP\MCP\Core\McpAdapter</code> found' : 'Install and activate the <strong>WordPress MCP Adapter</strong> plugin'; ?></td>
						</tr>
						<tr>
							<td>App Passwords (WordPress core)</td>
							<td><?php echo self::badge( $app_pass_wp ); ?></td>
							<td>From <code>wp_is_application_passwords_available()</code></td>
						</tr>
					</tbody>
				</table>

				<h3>Endpoints</h3>
				<table class="widefat">
					<tr><td>Default MCP server</td><td><code><?php echo esc_html( $default_url ); ?></code></td></tr>
					<tr><td>LightSpeed testing server</td><td><code><?php echo esc_html( $ls_url ); ?></code></td></tr>
					<tr><td>STDIO server ID (WP-CLI)</td><td><code>lightspeed-testing-mcp-server</code></td></tr>
					<tr><td>Dedicated MCP username</td><td><code><?php echo esc_html( $dedicated ); ?></code></td></tr>
				</table>

				<h3>Plugin Integrations</h3>
				<table class="widefat lsx-mcp-status-table">
					<thead>
						<tr><th>Plugin</th><th>Status</th><th>Context ability</th></tr>
					</thead>
					<tbody>
						<tr>
							<td>LSX Tour Operator</td>
							<td>
								<?php if ( $has_tour_op ) : ?>
									<span class="lsx-mcp-badge-ok">Active<?php echo $tour_op_ver ? ' v' . esc_html( $tour_op_ver ) : ''; ?></span>
								<?php else : ?>
									<span class="lsx-mcp-badge-na">Not active</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $has_tour_op ) : ?>
									<code>lsxmcpui/get-tour-operator-context</code> — enable in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp-ui' ) ); ?>">Abilities</a>
								<?php else : ?>
									<span style="color:#787c82;">Install Tour Operator to unlock this ability.</span>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<td>WooCommerce</td>
							<td>
								<?php if ( $has_woocommerce ) : ?>
									<span class="lsx-mcp-badge-ok">Active<?php echo $woo_ver ? ' v' . esc_html( $woo_ver ) : ''; ?></span>
								<?php else : ?>
									<span class="lsx-mcp-badge-na">Not active</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $has_woocommerce ) : ?>
									<code>lsxmcpui/get-woocommerce-context</code> — enable in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp-ui' ) ); ?>">Abilities</a>
								<?php else : ?>
									<span style="color:#787c82;">Install WooCommerce to unlock this ability.</span>
								<?php endif; ?>
							</td>
						</tr>
					</tbody>
				</table>

				<p style="margin-top:1em;">
					Manage which MCP abilities (read/write per content type) are enabled at
					<a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp-ui' ) ); ?>">MCP UI &rarr; Abilities</a>.
				</p>
			</div>

			<?php /* ──────────────────── SETTINGS ──────────────────── */ ?>
			<div id="lsx-mcp-tab-settings" class="lsx-mcp-panel<?php echo 'settings' === $active_tab ? ' active' : ''; ?>">
				<h2>Settings</h2>
				<p>Configure LightSpeed MCP for this site. Constants defined in <code>wp-config.php</code> take precedence over these settings — fields controlled by a constant are shown as read-only.</p>

				<?php if ( $is_ls_domain ) : ?>
				<div class="notice notice-info inline">
					<p>This site is on <strong><?php echo esc_html( $host ); ?></strong>, which is recognised as a LightSpeed dev domain. You can enable MCP here without editing <code>wp-config.php</code>.</p>
				</div>
				<?php endif; ?>

				<form method="post" action="">
					<?php wp_nonce_field( LSX_MCP_UI_Settings::NONCE_ACTION, LSX_MCP_UI_Settings::NONCE_NAME ); ?>
					<input type="hidden" name="lsx_mcp_save_settings" value="1">

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">Enable MCP for this site</th>
							<td>
								<?php self::settings_checkbox( 'lsx_mcp_enabled', 'enabled', 'Activate all LightSpeed MCP features on this site' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row">Application Password compatibility</th>
							<td>
								<?php self::settings_checkbox( 'lsx_mcp_enable_application_passwords', 'enable_application_passwords', 'Restore Application Password availability for HTTP MCP transport (required for dev site connections)' ); ?>
								<p class="description">Has no effect on local STDIO connections.</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Enable LightSpeed Testing MCP Server</th>
							<td>
								<?php self::settings_checkbox( 'lsx_mcp_enable_custom_server', 'enable_custom_server', 'Register the read-only <code>lightspeed-testing-mcp-server</code>' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="lsx_mcp_dedicated_user_login">Dedicated MCP username</label></th>
							<td>
								<?php
								$k        = 'dedicated_user_login';
								$override = LSX_MCP_UI_Settings::is_constant_override( $k );
								$val      = $override ? LSX_MCP_UI_Settings::get( $k ) : ( LSX_MCP_UI_Settings::get_raw( $k ) ?? 'mcp-dev-agent' );
								?>
								<input
									type="text"
									id="lsx_mcp_dedicated_user_login"
									name="lsx_mcp_dedicated_user_login"
									value="<?php echo esc_attr( $val ); ?>"
									class="regular-text"
									<?php echo $override ? 'disabled readonly' : ''; ?>
								>
								<?php if ( $override ) echo '<p class="description">Controlled by <code>LSX_MCP_DEDICATED_USER_LOGIN</code> constant.</p>'; ?>
								<p class="description">WordPress user login for the dedicated MCP agent. Default: <code>mcp-dev-agent</code>.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="lsx_mcp_allowed_dev_suffix">Allowed dev domain suffix</label></th>
							<td>
								<?php
								$k        = 'allowed_dev_suffix';
								$override = LSX_MCP_UI_Settings::is_constant_override( $k );
								$val      = $override ? LSX_MCP_UI_Settings::get( $k ) : ( LSX_MCP_UI_Settings::get_raw( $k ) ?? '.lightspeedwp.dev' );
								?>
								<input
									type="text"
									id="lsx_mcp_allowed_dev_suffix"
									name="lsx_mcp_allowed_dev_suffix"
									value="<?php echo esc_attr( $val ); ?>"
									class="regular-text"
									<?php echo $override ? 'disabled readonly' : ''; ?>
								>
								<?php if ( $override ) echo '<p class="description">Controlled by <code>LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX</code> constant.</p>'; ?>
								<p class="description">Hosts ending with this suffix are treated as LightSpeed dev domains regardless of <code>WP_ENVIRONMENT_TYPE</code>. Default: <code>.lightspeedwp.dev</code>.</p>
							</td>
						</tr>
					</table>

					<?php submit_button( 'Save Settings' ); ?>
				</form>

				<hr>
				<h3>Using wp-config.php constants instead</h3>
				<p>Constants always override these settings. You can mix and match — for example, set the username via settings and lock the enable flags via constants.</p>
				<?php self::code_block( LSX_MCP_UI_Config_Generator::wp_config_constants(), 'wp-config.php' ); ?>
			</div>

			<?php /* ──────────────────── LOCAL SETUP ──────────────────── */ ?>
			<div id="lsx-mcp-tab-connect" class="lsx-mcp-panel<?php echo 'connect' === $active_tab ? ' active' : ''; ?>">
				<h2>Local Studio Setup (STDIO)</h2>
				<p>Local WordPress Studio sites use <strong>STDIO transport</strong> via WP-CLI. No Application Password is required, no HTTP endpoint is exposed, and the agent runs as a local WordPress user directly.</p>

				<h3>Requirements</h3>
				<ul class="lsx-mcp-list">
					<li>WordPress Studio site running locally</li>
					<li>WP-CLI available (<code>wp --info</code> should work in the terminal)</li>
					<li>WordPress MCP Adapter plugin active on the local site</li>
					<li>This plugin active on the local site</li>
					<li>MCP enabled — set in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings</a> or via <code>define( 'LSX_MCP_ENABLED', true );</code></li>
					<li>An admin user (or dedicated <code><?php echo esc_html( $dedicated ); ?></code> user)</li>
				</ul>

				<h3>VS Code — Default Server</h3>
				<p>Create or update <code>.vscode/mcp.json</code> in your project root:</p>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::vscode_stdio_default( $studio_path, $wp_user ),
					'.vscode/mcp.json'
				); ?>

				<h3>VS Code — LightSpeed Testing Server</h3>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::vscode_stdio_lightspeed( $studio_path, $wp_user ),
					'.vscode/mcp.json'
				); ?>

				<h3>Claude Code — Default Server</h3>
				<p>Create <code>.mcp.json</code> at the project root (not inside <code>.claude/</code>):</p>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::claude_stdio_default( $studio_path, $wp_user ),
					'.mcp.json'
				); ?>

				<h3>Claude Code — LightSpeed Testing Server</h3>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::claude_stdio_lightspeed( $studio_path, $wp_user ),
					'.mcp.json'
				); ?>

				<div class="lsx-mcp-note">
					<strong>Config is pre-filled:</strong> Path and username are auto-detected from this WordPress installation and the current logged-in user. Copy and use directly.
				</div>
			</div>

			<?php /* ──────────────────── DEV SITE ──────────────────── */ ?>
			<div id="lsx-mcp-tab-devsite" class="lsx-mcp-panel<?php echo 'devsite' === $active_tab ? ' active' : ''; ?>">
				<h2>Development Site Setup (<code><?php echo esc_html( $dev_suffix ); ?></code>)</h2>
				<p>Shared development sites use <strong>HTTP transport</strong> via <code>mcp-remote</code> — an npm stdio-to-HTTP bridge that handles the Streamable HTTP session protocol required by MCP Adapter v0.5.0+. You need Node.js and an Application Password for the MCP user.</p>

				<?php if ( $is_ls_domain ) : ?>
				<div class="notice notice-success inline">
					<p><strong>This site is a LightSpeed dev domain.</strong> Enable MCP and Application Password compatibility in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings</a>, then follow the steps below. No <code>wp-config.php</code> editing required.</p>
				</div>
				<?php endif; ?>

				<div class="notice notice-warning inline">
					<p><strong>Cloudflare Access:</strong> If this dev site is behind Cloudflare Access, MCP clients must include <code>CF-Access-Client-Id</code> and <code>CF-Access-Client-Secret</code> headers in every request. <code>mcp-remote</code> does not natively support Cloudflare Access headers — ensure the MCP endpoint is not gated by a Cloudflare Access policy, or create a service token bypass rule for your MCP client IP.</p>
				</div>

				<h3>Requirements</h3>
				<ul class="lsx-mcp-list">
					<li>Site URL ending in <code><?php echo esc_html( $dev_suffix ); ?></code></li>
					<li>HTTPS working on the dev site</li>
					<li>WordPress MCP Adapter plugin active on the dev site</li>
					<li>This plugin active with MCP enabled — set in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings</a> or via <code>define( 'LSX_MCP_ENABLED', true );</code></li>
					<li>Application Password compatibility enabled — set in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings</a> or via <code>define( 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS', true );</code></li>
					<li>Node.js available locally (<code>node --version</code> should work in the terminal)</li>
					<li>A dedicated <a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><code><?php echo esc_html( $dedicated ); ?></code></a> user (recommended) or admin with an Application Password</li>
				</ul>

				<h3>Step 1 — Enable MCP on the dev site</h3>
				<p>In <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings</a>, enable <em>Enable MCP for this site</em> and <em>Application Password compatibility</em>. No <code>wp-config.php</code> changes needed on <code><?php echo esc_html( $dev_suffix ); ?></code> sites. If you prefer constants:</p>
				<?php self::code_block( LSX_MCP_UI_Config_Generator::wp_config_constants(), 'wp-config.php' ); ?>

				<h3>Step 2 — Create an Application Password</h3>
				<ol class="lsx-mcp-list">
					<li>Log in as <a href="<?php echo esc_url( admin_url( 'users.php' ) ); ?>"><code><?php echo esc_html( $dedicated ); ?></code></a> (or admin if no dedicated user exists).</li>
					<li>Go to <strong>Users &rarr; Profile</strong>, scroll to <strong>Application Passwords</strong>.</li>
					<li>Enter a name (e.g. <code>Claude Code</code>) and click <strong>Add New Application Password</strong>.</li>
					<li>Copy the password — it is shown <strong>only once</strong>.</li>
					<li>Generate your Base64 auth token in your terminal:<br>
						<?php self::code_block( 'echo -n "' . esc_html( $dedicated ) . ':your-app-password" | base64', 'Terminal' ); ?>
					</li>
				</ol>

				<h3>Step 3 — Configure your MCP client</h3>
				<p>Replace <code>site-name</code> with your dev site subdomain, and the <code>BASE64_&hellip;</code> placeholder with the value from Step 2. Both entries use the same credentials — they only differ in the server endpoint.</p>

				<h4>Claude Code (<code>.mcp.json</code> — LightSpeed Testing Server)</h4>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::claude_http(
						'wordpress-dev-testing',
						'https://site-name' . $dev_suffix . '/wp-json/lightspeed-testing-mcp-server/mcp',
						$dedicated
					),
					'.mcp.json'
				); ?>

				<h4>Claude Code (<code>.mcp.json</code> — Default Server)</h4>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::claude_http(
						'wordpress-dev-default',
						'https://site-name' . $dev_suffix . '/wp-json/mcp/mcp-adapter-default-server',
						$dedicated
					),
					'.mcp.json'
				); ?>

				<h4>VS Code (<code>.vscode/mcp.json</code> — LightSpeed Testing Server)</h4>
				<?php self::code_block(
					LSX_MCP_UI_Config_Generator::vscode_http(
						'wordpress-dev-testing',
						'https://site-name' . $dev_suffix . '/wp-json/lightspeed-testing-mcp-server/mcp',
						$dedicated
					),
					'.vscode/mcp.json'
				); ?>

				<h3>Step 4 — Restart your MCP client</h3>
				<div class="notice notice-warning inline">
					<p>Claude Code reads <code>.mcp.json</code> only at startup. After creating or editing the file, <strong>close and reopen</strong> the VS Code window for the server to connect.</p>
				</div>

				<div class="lsx-mcp-note">
					<strong>Why <code>mcp-remote</code>?</strong> MCP Adapter v0.5.0+ uses the Streamable HTTP transport (protocol <code>2025-06-18</code>) which requires per-session <code>Mcp-Session-Id</code> headers. <code>mcp-remote</code> handles this correctly. Claude Code's native <code>"type": "http"</code> config and the <code>@automattic/mcp-wordpress-remote</code> npm package both use the older transport and return an empty tool list.
				</div>
			</div>

			<?php /* ──────────────────── SECURITY ──────────────────── */ ?>
			<div id="lsx-mcp-tab-security" class="lsx-mcp-panel<?php echo 'security' === $active_tab ? ' active' : ''; ?>">
				<h2>Security Checklist</h2>
				<ul class="lsx-mcp-checklist">
					<li class="ok">MCP clients act as authenticated WordPress users — they inherit the permissions of the account they authenticate as.</li>
					<li class="ok">Use a dedicated MCP user (<code><?php echo esc_html( $dedicated ); ?></code>) with the minimum useful capability.</li>
					<li class="ok">Start with read-only abilities. Avoid write abilities on shared or publicly accessible servers.</li>
					<li class="ok">Application Password compatibility is gated by the MCP enabled flag, the enable flag, the operational environment, and the host pattern — never active on production by default.</li>
					<li class="ok">Wordfence is <strong>not disabled globally</strong>. Only the WordPress Application Password availability filter is restored for allowed environments and users.</li>
					<li class="ok">Do not store Application Passwords in WordPress options, the database, or this plugin's settings.</li>
					<li class="ok">Revoke Application Passwords from the user profile when they are no longer needed.</li>
					<li class="ok">Do not enable MCP on production without a separate review of the abilities and transport.</li>
					<li class="warn">Never commit Application Passwords to version control (.env files, config files, etc.).</li>
					<li class="warn">The default server uses <code>execute-ability</code> which can run any enabled ability — prefer the custom testing server for CI/testing agents.</li>
					<li class="warn">If the dev site is behind Cloudflare Access, ensure MCP client requests carry valid Service Token headers, or create a bypass rule for the MCP endpoint.</li>
				</ul>
			</div>

			<?php /* ──────────────────── REFERENCE (Custom Server + Troubleshooting) ──────────────────── */ ?>
			<div id="lsx-mcp-tab-reference" class="lsx-mcp-panel<?php echo 'reference' === $active_tab ? ' active' : ''; ?>">
				<h2>Reference</h2>
				<p>Custom server details and troubleshooting steps.</p>

				<h3>LightSpeed Custom Testing Server</h3>
				<p>The custom LightSpeed MCP server exposes a curated set of <strong>read-only diagnostic abilities</strong>. It is separate from the default adapter server and only registers when MCP is enabled and the environment is local or development.</p>

				<table class="widefat">
					<tr><td>Server ID</td><td><code>lightspeed-testing-mcp-server</code></td></tr>
					<tr><td>HTTP endpoint</td><td><code><?php echo esc_html( $ls_url ); ?></code></td></tr>
					<tr><td>STDIO server ID</td><td><code>lightspeed-testing-mcp-server</code></td></tr>
					<tr><td>Capability required</td><td><code>manage_options</code> (filterable via <code>lsx_mcp_testing_server_capability</code>)</td></tr>
					<tr><td>Status</td><td><?php echo $custom_srv ? '<span class="lsx-mcp-badge-ok">Registered</span>' : '<span class="lsx-mcp-badge-off">Not registered</span>'; ?></td></tr>
				</table>

				<h3>Abilities exposed</h3>
				<table class="widefat striped">
					<thead>
						<tr><th>Ability</th><th>Description</th></tr>
					</thead>
					<tbody>
						<tr><td><code>lightspeed/site-summary</code></td><td>Site name, URLs, WP version, PHP version, environment, active theme, plugin count, MCP availability.</td></tr>
						<tr><td><code>lightspeed/plugin-inventory</code></td><td>Active (and optionally inactive) plugin list with name, version, URI, author.</td></tr>
						<tr><td><code>lightspeed/theme-audit</code></td><td>Active theme details, block theme status, theme.json existence, template/pattern/style counts.</td></tr>
						<tr><td><code>lightspeed/url-inventory</code></td><td>Public URLs across all post types — ID, type, title, status, URL, modified date.</td></tr>
						<tr><td><code>lightspeed/content-readiness</code></td><td>QA signals per post: empty title/content/excerpt, missing featured image, missing SEO meta.</td></tr>
						<tr><td><code>lightspeed/block-theme-audit</code></td><td>Block theme file inventory: template/part/pattern/variation filenames, theme.json section detection.</td></tr>
					</tbody>
				</table>

				<h3>Intentionally not exposed</h3>
				<ul class="lsx-mcp-list">
					<li>Any ability that creates, updates, or deletes content</li>
					<li>Database credentials, secrets, salts, or environment variables</li>
					<li>Absolute filesystem paths (unless <code>debug_paths=true</code> is passed to theme-audit)</li>
					<li>User passwords or Application Password values</li>
					<li>License keys or plugin option values</li>
				</ul>

				<h3>Troubleshooting</h3>

				<dl class="lsx-mcp-faq">
					<dt>MCP features are blocked and I can't see why</dt>
					<dd>Check the Status tab. The <strong>Operational MCP environment</strong> row shows how the plugin classified this site. If it says <code>blocked</code>, the host (<code><?php echo esc_html( $host ); ?></code>) is a public domain and WordPress env type is <code>production</code>. If it should be a dev site, update the Allowed dev domain suffix in <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings</a>.</dd>

					<dt>Site is <code><?php echo esc_html( $dev_suffix ); ?></code> but shows as blocked</dt>
					<dd>Confirm the <strong>Allowed dev domain suffix</strong> in Settings matches your site's domain. The default is <code>.lightspeedwp.dev</code>. You can also hard-code it with <code>define( 'LSX_MCP_ALLOWED_DEV_DOMAIN_SUFFIX', '.yourdomain.dev' );</code>.</dd>

					<dt><code>wp</code> command not found (STDIO)</dt>
					<dd>WP-CLI is not installed or not in your PATH. Install from <code>wp-cli.org</code> and verify with <code>wp --info</code>.</dd>

					<dt>Wrong Studio path</dt>
					<dd>The <code>--path</code> argument must point to the WordPress root (the folder containing <code>wp-config.php</code>).</dd>

					<dt>MCP Adapter inactive</dt>
					<dd>Install and activate the <strong>WordPress MCP Adapter</strong> plugin. Without it, the <code>wp mcp-adapter serve</code> command and the HTTP endpoint do not exist.</dd>

					<dt>Server starts but shows zero tools</dt>
					<dd>MCP is enabled but no abilities are registered for it. For the LightSpeed server: check Status tab. For the default server: go to <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp-ui' ) ); ?>">MCP UI &rarr; Abilities</a> and enable the abilities you need.</dd>

					<dt>Application Passwords not visible (HTTP transport)</dt>
					<dd>All conditions must be true: MCP enabled, App Password compatibility enabled, operational environment not blocked, host ends in <code><?php echo esc_html( $dev_suffix ); ?></code>. Check the Status tab for which condition is failing.</dd>

					<dt>Wordfence blocks Application Passwords</dt>
					<dd>This plugin restores Application Password availability via filters at <code>PHP_INT_MAX</code> priority. If Wordfence still blocks, check <strong>Wordfence &rarr; Login Security</strong>. The LightSpeed MCP plugin does not and will not globally disable Wordfence.</dd>

					<dt>HTTP 401 from MCP client</dt>
					<dd>Wrong username or Application Password. Re-generate one from <strong>Users &rarr; Profile &rarr; Application Passwords</strong>.</dd>

					<dt>HTTP 403 from MCP client</dt>
					<dd>User lacks <code>manage_options</code>. Promote the MCP user to Administrator, or change the <code>lsx_mcp_testing_server_capability</code> filter.</dd>

					<dt>REST endpoint 404</dt>
					<dd>Flush permalinks at <strong>Settings &rarr; Permalinks</strong>. Confirm MCP Adapter is active. For the LightSpeed server, confirm Status tab shows it as registered.</dd>

					<dt>Site is staging but MCP is blocked</dt>
					<dd>Staging is blocked by default. Add <code>define( 'LSX_MCP_ALLOWED_ENVIRONMENTS', array( 'local', 'development', 'staging' ) );</code> to <code>wp-config.php</code>.</dd>

					<dt>Cloudflare Access blocks MCP requests</dt>
					<dd>The <code>@automattic/mcp-wordpress-remote</code> package does not add Cloudflare Access headers. Either create a service token bypass rule for the MCP endpoint in Cloudflare Access, or run a local proxy that injects the <code>CF-Access-Client-Id</code> and <code>CF-Access-Client-Secret</code> headers before forwarding to the dev site.</dd>
				</dl>
			</div>

			<?php /* ──────────────────── DOCS ──────────────────── */ ?>
			<div id="lsx-mcp-tab-docs" class="lsx-mcp-panel<?php echo 'docs' === $active_tab ? ' active' : ''; ?>">
				<h2>Documentation</h2>
				<p>Complete reference for setting up and using LightSpeed MCP with Claude Code, VS Code, and Claude Desktop.</p>

			<nav class="lsx-mcp-doc-toc">
				<strong>Jump to:</strong>
				<a href="#doc-how-it-works">How it works</a> &middot;
				<a href="#doc-claude-code">Claude Code setup</a> &middot;
				<a href="#doc-vscode">VS Code setup</a> &middot;
				<a href="#doc-claude-desktop">Claude Desktop</a> &middot;
				<a href="#doc-verifying">Verifying connection</a> &middot;
				<a href="#doc-default-abilities">Default server abilities</a> &middot;
				<a href="#doc-lightspeed-abilities">LightSpeed abilities</a> &middot;
				<a href="#doc-workflows">Example workflows</a> &middot;
				<a href="#doc-gotchas">Common gotchas</a>
			</nav>

			<?php /* ── HOW IT WORKS ── */ ?>
			<h3 id="doc-how-it-works">How it works</h3>
			<p>This plugin exposes two MCP servers, each suited to a different transport:</p>

			<table class="widefat striped">
				<thead>
					<tr><th>Server</th><th>ID</th><th>Transport</th><th>Best for</th></tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Default adapter server</strong></td>
						<td><code>mcp-adapter-default-server</code></td>
						<td>STDIO (local) or HTTP (dev site)</td>
						<td>Content read/write abilities (<code>lsxmcpui/*</code>), Yoast SEO, custom CPTs</td>
					</tr>
					<tr>
						<td><strong>LightSpeed testing server</strong></td>
						<td><code>lightspeed-testing-mcp-server</code></td>
						<td>STDIO (local) or HTTP (dev site)</td>
						<td>Read-only site diagnostics: plugin inventory, theme audit, content readiness, block theme audit</td>
					</tr>
				</tbody>
			</table>

			<p>Both servers run independently. Your MCP client config must include <strong>one entry per server</strong>. Each entry spawns a separate process and appears as a separate tool namespace in your AI client.</p>

			<div class="lsx-mcp-note">
				<strong>Local vs. dev site:</strong> On a local WordPress Studio site use <strong>STDIO transport</strong> (WP-CLI command, no HTTP, no Application Password needed). On a shared <code><?php echo esc_html( $dev_suffix ); ?></code> site use <strong>HTTP transport</strong> via <code>@automattic/mcp-wordpress-remote</code> and an Application Password.
			</div>

			<?php /* ── CLAUDE CODE ── */ ?>
			<h3 id="doc-claude-code">Claude Code — Local Studio setup</h3>

			<h4>1. Find your project root</h4>
			<p>The config file must live at <strong><code>.mcp.json</code> in the root of your project directory</strong> — the same folder Claude Code opens as its working directory. This is <em>not</em> inside <code>.claude/</code> and is <em>not</em> <code>~/.claude/claude_desktop_config.json</code>.</p>

			<table class="widefat striped" style="margin-bottom:1em;">
				<thead><tr><th>Path</th><th>Effect</th></tr></thead>
				<tbody>
					<tr><td><code>/your-project/.mcp.json</code></td><td class="lsx-mcp-badge-ok">Correct — Claude Code reads this</td></tr>
					<tr><td><code>/your-project/.claude/claude_desktop_config.json</code></td><td><span class="lsx-mcp-badge-off">Wrong — not read by Claude Code CLI</span></td></tr>
					<tr><td><code>~/.claude/claude_desktop_config.json</code></td><td>Global fallback — works but applies to every project</td></tr>
				</tbody>
			</table>

			<h4>2. Create <code>.mcp.json</code> with both servers</h4>
			<p>Replace <code>/Users/YOUR_USER/Studio/YOUR_SITE</code> with the real path to your WordPress installation root (the folder that contains <code>wp-config.php</code>).</p>
			<?php
			$mc = json_encode( array(
				'mcpServers' => array(
					'wordpress-local-default' => array(
						'command' => 'wp',
						'args'    => array(
							'--path=' . $studio_path,
							'mcp-adapter',
							'serve',
							'--server=mcp-adapter-default-server',
							'--user=' . $wp_user,
						),
					),
					'wordpress-local-lightspeed' => array(
						'command' => 'wp',
						'args'    => array(
							'--path=' . $studio_path,
							'mcp-adapter',
							'serve',
							'--server=lightspeed-testing-mcp-server',
							'--user=' . $wp_user,
						),
					),
				),
			), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			self::code_block( $mc, '.mcp.json' );
			?>

			<h3 id="doc-claude-code-devsite">Claude Code — Dev Site HTTP setup</h3>
			<p>For a shared development site (e.g. <code><?php echo esc_html( $dev_suffix ); ?></code>), use the <code>mcp-remote</code> npm package as a stdio-to-HTTP bridge. It correctly implements the Streamable HTTP transport required by MCP Adapter v0.5.0+ and handles <code>Mcp-Session-Id</code> session management automatically.</p>

			<p>Add the following entry to your <code>.mcp.json</code> alongside (or instead of) the local STDIO entries:</p>
			<?php
			$dev_http = json_encode( array(
				'mcpServers' => array(
					'wordpress-dev-testing' => array(
						'command' => 'npx',
						'args'    => array(
							'mcp-remote',
							'https://your-site.lightspeedwp.dev/wp-json/lightspeed-testing-mcp-server/mcp',
							'--header',
							'Authorization:Basic BASE64_OF_USERNAME_COLON_PASSWORD',
						),
					),
				),
			), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			self::code_block( $dev_http, '.mcp.json' );
			?>

			<p>Generate the Base64 value with:</p>
			<?php self::code_block( 'echo -n "username:application-password" | base64', 'Terminal' ); ?>

			<div class="notice notice-info inline">
				<p><strong>Why <code>mcp-remote</code>?</strong> MCP Adapter v0.5.0+ uses the Streamable HTTP transport (protocol <code>2025-06-18</code>) which requires per-session <code>Mcp-Session-Id</code> headers. The <code>mcp-remote</code> package (official Anthropic stdio bridge) handles this correctly. Claude Code's native <code>"type": "http"</code> config does not currently support this transport. Do not use <code>@automattic/mcp-wordpress-remote</code> — it uses the old pre-session transport and returns an empty tool list.</p>
			</div>

			<h4>3. Enable MCP on the local site</h4>
			<p>MCP must be enabled before the servers are registered. Either:</p>
			<ul class="lsx-mcp-list">
				<li>Go to <strong>Settings</strong> tab → check <em>Enable MCP for this site</em> → Save, or</li>
				<li>Add <code>define( 'LSX_MCP_ENABLED', true );</code> to <code>wp-config.php</code></li>
			</ul>

			<h4>4. Restart Claude Code</h4>
			<div class="notice notice-warning inline">
				<p><strong>Important:</strong> Claude Code reads <code>.mcp.json</code> only at startup. After creating or changing the file, you must <strong>close and reopen Claude Code</strong> (or the VS Code window containing it) for the servers to connect. Saving the file alone is not enough.</p>
			</div>

			<h4>5. Verify</h4>
			<p>After restarting, ask Claude: <em>"Discover the available WordPress abilities."</em> Claude will call <code>mcp-adapter-discover-abilities</code> and list all registered tools. You should see both <code>lsxmcpui/*</code> tools (from the default server) and <code>lightspeed/*</code> tools (from the LightSpeed server) as separate namespaces.</p>

			<?php /* ── VSCODE ── */ ?>
			<h3 id="doc-vscode">VS Code — MCP Servers panel</h3>
			<p>VS Code reads from <code>.vscode/mcp.json</code> in the project root. The format differs slightly from Claude Code — it uses a <code>"servers"</code> key instead of <code>"mcpServers"</code>.</p>

			<?php
			$vsc = json_encode( array(
				'servers' => array(
					'wordpress-local-default' => array(
						'command' => 'wp',
						'args'    => array(
							'--path=' . $studio_path,
							'mcp-adapter',
							'serve',
							'--server=mcp-adapter-default-server',
							'--user=' . $wp_user,
						),
					),
					'wordpress-local-lightspeed' => array(
						'command' => 'wp',
						'args'    => array(
							'--path=' . $studio_path,
							'mcp-adapter',
							'serve',
							'--server=lightspeed-testing-mcp-server',
							'--user=' . $wp_user,
						),
					),
				),
			), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
			self::code_block( $vsc, '.vscode/mcp.json' );
			?>

			<p>VS Code reloads MCP servers when the file is saved — a full restart is usually not required. Use the <strong>MCP Servers</strong> panel in the VS Code activity bar to confirm the servers show as connected.</p>

			<?php /* ── CLAUDE DESKTOP ── */ ?>
			<h3 id="doc-claude-desktop">Claude Desktop</h3>
			<p>Claude Desktop uses a global config file. It uses the same JSON format as Claude Code (<code>"mcpServers"</code> key).</p>

			<table class="widefat striped" style="margin-bottom:1em;">
				<thead><tr><th>OS</th><th>Config file path</th></tr></thead>
				<tbody>
					<tr><td>macOS</td><td><code>~/Library/Application Support/Claude/claude_desktop_config.json</code></td></tr>
					<tr><td>Windows</td><td><code>%APPDATA%\Claude\claude_desktop_config.json</code></td></tr>
				</tbody>
			</table>

			<p>After editing the file, <strong>fully quit and relaunch Claude Desktop</strong>. The servers will appear in the tools panel on the left side of a new conversation.</p>

			<div class="lsx-mcp-note">
				The path <code>.claude/claude_desktop_config.json</code> inside a project directory is <strong>not</strong> read by either Claude Desktop or Claude Code. It is a non-standard location that has no effect.
			</div>

			<?php /* ── VERIFYING ── */ ?>
			<h3 id="doc-verifying">Verifying the connection</h3>

			<p>Once the servers are connected, ask your AI client to discover what abilities are available:</p>

			<?php self::code_block( "Discover the available WordPress abilities and list them for me.", 'Prompt' ); ?>

			<p>A working connection returns a list of ability names grouped by server. You should see at least:</p>
			<ul class="lsx-mcp-list">
				<li><strong>Default server</strong> — <code>lsxmcpui/get-site-info</code>, <code>lsxmcpui/get-posts</code>, <code>lsxmcpui/get-pages</code>, <code>lsxmcpui/get-post-types</code>, and others</li>
				<li><strong>LightSpeed server</strong> — <code>lightspeed/site-summary</code>, <code>lightspeed/plugin-inventory</code>, <code>lightspeed/theme-audit</code>, <code>lightspeed/url-inventory</code>, <code>lightspeed/content-readiness</code>, <code>lightspeed/block-theme-audit</code></li>
			</ul>

			<p>You can also run a quick smoke test:</p>
			<?php self::code_block( "Run the lightspeed/site-summary ability and tell me the result.", 'Prompt' ); ?>

			<?php /* ── DEFAULT ABILITIES ── */ ?>
			<h3 id="doc-default-abilities">Default server abilities (<code>mcp-adapter-default-server</code>)</h3>
			<p>These abilities are managed at <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp-ui' ) ); ?>">Tools → MCP Abilities</a>. Enable or disable each one from that page.</p>

			<table class="widefat striped">
				<thead>
					<tr><th>Ability</th><th>Access</th><th>Default</th><th>Description</th></tr>
				</thead>
				<tbody>
					<tr><td><code>lsxmcpui/get-site-info</code></td><td>Read</td><td>On</td><td>Site name, URL, tagline, WP version, language</td></tr>
					<tr><td><code>lsxmcpui/get-posts</code></td><td>Read</td><td>On</td><td>Blog posts with metadata, excerpt, categories, tags</td></tr>
					<tr><td><code>lsxmcpui/get-pages</code></td><td>Read</td><td>On</td><td>Published pages with title, URL, parent, status</td></tr>
					<tr><td><code>lsxmcpui/get-post-types</code></td><td>Read</td><td>On</td><td>All registered public post types with labels and REST base</td></tr>
					<tr><td><code>lsxmcpui/get-cpt-items</code></td><td>Read</td><td>On</td><td>Query any post type by slug; includes ACF fields when active</td></tr>
					<tr><td><code>lsxmcpui/get-categories</code></td><td>Read</td><td>On</td><td>Post categories with IDs, slugs, post counts</td></tr>
					<tr><td><code>lsxmcpui/get-tags</code></td><td>Read</td><td>On</td><td>Post tags with IDs, slugs, post counts</td></tr>
					<tr><td><code>lsxmcpui/get-patterns</code></td><td>Read</td><td>On</td><td>All registered block patterns with name, title, categories, content</td></tr>
					<tr><td><code>lsxmcpui/search</code></td><td>Read</td><td>On</td><td>Full-text search across all post types</td></tr>
					<tr><td><code>lsxmcpui/get-tour-operator-context</code></td><td>Read</td><td>Off</td><td>LSX Tour Operator developer context: CPT slugs, meta keys, taxonomies, modal system, Wetu field mappings</td></tr>
					<tr><td><code>lsxmcpui/get-comments</code></td><td>Read</td><td>Off</td><td>Comments with author, status, content snippet</td></tr>
					<tr><td><code>lsxmcpui/get-media</code></td><td>Read</td><td>Off</td><td>Media library items with title, URL, MIME type</td></tr>
					<tr><td><code>lsxmcpui/get-users</code></td><td>Read</td><td>Off</td><td>Users with display name, email, role</td></tr>
					<tr><td><code>lsxmcpui/get-plugins</code></td><td>Read</td><td>Off</td><td>All active plugins with name, version, author</td></tr>
					<tr><td><code>lsxmcpui/create-post</code></td><td><strong>Write</strong></td><td>Off</td><td>Create a new blog post</td></tr>
					<tr><td><code>lsxmcpui/update-post</code></td><td><strong>Write</strong></td><td>Off</td><td>Update an existing post by ID</td></tr>
					<tr><td><code>lsxmcpui/delete-post</code></td><td><strong>Write</strong></td><td>Off</td><td>Move a post to trash</td></tr>
					<tr><td><code>lsxmcpui/create-page</code></td><td><strong>Write</strong></td><td>Off</td><td>Create a new page</td></tr>
					<tr><td><code>lsxmcpui/update-page</code></td><td><strong>Write</strong></td><td>Off</td><td>Update a page by ID</td></tr>
					<tr><td><code>lsxmcpui/delete-page</code></td><td><strong>Write</strong></td><td>Off</td><td>Move a page to trash</td></tr>
					<tr><td><code>lsxmcpui/create-category</code></td><td><strong>Write</strong></td><td>Off</td><td>Create a new category</td></tr>
					<tr><td><code>lsxmcpui/create-tag</code></td><td><strong>Write</strong></td><td>Off</td><td>Create a new tag</td></tr>
					<tr><td><code>lsxmcpui/approve-comment</code></td><td><strong>Write</strong></td><td>Off</td><td>Approve a pending comment</td></tr>
					<tr><td><code>lsxmcpui/delete-comment</code></td><td><strong>Write</strong></td><td>Off</td><td>Move a comment to trash</td></tr>
					<tr><td><code>lsxmcpui/create-pattern</code></td><td><strong>Write</strong></td><td>Off</td><td>Write a new PHP pattern file to the active theme</td></tr>
					<tr><td><code>lsxmcpui/update-pattern</code></td><td><strong>Write</strong></td><td>Off</td><td>Overwrite an existing pattern file in the active theme</td></tr>
					<tr><td><code>lsxmcpui/delete-pattern</code></td><td><strong>Write</strong></td><td>Off</td><td>Delete a pattern PHP file from the active theme</td></tr>
				</tbody>
			</table>

			<div class="lsx-mcp-note">
				<strong>Write abilities are off by default.</strong> Enable them individually at <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp-ui' ) ); ?>">Tools → MCP Abilities</a> only when you need them. Never enable write abilities on a shared or publicly accessible server.
			</div>

			<?php /* ── LIGHTSPEED ABILITIES ── */ ?>
			<h3 id="doc-lightspeed-abilities">LightSpeed testing server abilities (<code>lightspeed-testing-mcp-server</code>)</h3>
			<p>These are always read-only. They require <code>manage_options</code> and are only accessible when MCP is enabled and the operational environment is not blocked.</p>

			<table class="widefat striped">
				<thead>
					<tr><th>Ability</th><th>Parameters</th><th>Returns</th></tr>
				</thead>
				<tbody>
					<tr>
						<td><code>lightspeed/site-summary</code></td>
						<td><em>none</em></td>
						<td>Site name, URL, home URL, WP version, PHP version, environment type, active theme (name/version/parent/block-theme), multisite, debug mode, search engine visibility, permalink structure, active plugin count, WooCommerce status, MCP adapter availability</td>
					</tr>
					<tr>
						<td><code>lightspeed/plugin-inventory</code></td>
						<td><code>include_inactive</code> (bool, default false)</td>
						<td>Active plugins (and optionally inactive) with name, version, plugin URI, author. Does not expose license keys or plugin options.</td>
					</tr>
					<tr>
						<td><code>lightspeed/theme-audit</code></td>
						<td><code>debug_paths</code> (bool, default false)</td>
						<td>Active theme info, parent theme info, block theme status, theme.json existence, WP feature support flags, counts of templates / template parts / PHP patterns / style variations. Absolute paths only returned when <code>debug_paths: true</code> and user has <code>manage_options</code>.</td>
					</tr>
					<tr>
						<td><code>lightspeed/url-inventory</code></td>
						<td><code>post_types</code> (array), <code>limit</code> (int, max 500), <code>status</code> (default "publish")</td>
						<td>Post ID, type, title, status, URL, modified date for each item. Includes count by type and a limit-reached flag. Private statuses require <code>read_private_posts</code>.</td>
					</tr>
					<tr>
						<td><code>lightspeed/content-readiness</code></td>
						<td><code>post_types</code> (array), <code>limit</code> (int)</td>
						<td>Per-post QA signals: missing title, empty content, missing excerpt, missing featured image, missing SEO title, missing meta description. Works with Yoast SEO, Rank Math, AIOSEO — falls back gracefully when none is present. Returns per-item list plus summary counts.</td>
					</tr>
					<tr>
						<td><code>lightspeed/block-theme-audit</code></td>
						<td><em>none</em></td>
						<td>Template filenames, template part filenames, pattern filenames, style variation filenames. Reports theme.json existence and which top-level sections are present (<code>settings.color.palette</code>, <code>settings.typography</code>, <code>settings.spacing</code>). Reports dark mode variation (<code>styles/dark.json</code>). Reads first 64 KB of theme.json only.</td>
					</tr>
				</tbody>
			</table>

			<?php /* ── WORKFLOWS ── */ ?>
			<h3 id="doc-workflows">Example workflows</h3>

			<h4>Site audit before handoff</h4>
			<?php self::code_block(
				"Run lightspeed/site-summary, lightspeed/plugin-inventory, and lightspeed/theme-audit.\n" .
				"Give me a brief summary of the site's technology stack and flag anything that looks unusual.",
				'Prompt'
			); ?>

			<h4>Content QA check</h4>
			<?php self::code_block(
				"Run lightspeed/content-readiness on the 'tour' post type.\n" .
				"List all tours that are missing a featured image or SEO meta description, sorted by post ID.",
				'Prompt'
			); ?>

			<h4>Block theme inventory</h4>
			<?php self::code_block(
				"Run lightspeed/block-theme-audit and lightspeed/theme-audit.\n" .
				"List all template parts, patterns, and style variations. Note any missing theme.json sections.",
				'Prompt'
			); ?>

			<h4>URL map for QA testing</h4>
			<?php self::code_block(
				"Run lightspeed/url-inventory with post_types=[\"tour\",\"destination\",\"accommodation\"] and limit=200.\n" .
				"Return a markdown table of the URLs grouped by post type.",
				'Prompt'
			); ?>

			<h4>Tour Operator developer context</h4>
			<?php self::code_block(
				"Run lsxmcpui/get-tour-operator-context.\n" .
				"I need to know the CPT slugs, all available meta keys for the 'tour' post type,\n" .
				"and how the modal system works for accommodation listings.",
				'Prompt'
			); ?>

			<?php /* ── GOTCHAS ── */ ?>
			<h3 id="doc-gotchas">Common gotchas</h3>

			<dl class="lsx-mcp-faq">
				<dt><code>.mcp.json</code> is in the wrong location</dt>
				<dd>
					The most common mistake. Claude Code reads <code>.mcp.json</code> from the <strong>project root only</strong>. Placing it inside <code>.claude/</code> does nothing. The project root is the folder Claude Code opened — the same folder that contains your <code>wp-config.php</code> or <code>package.json</code>.<br><br>
					To verify: in the Claude Code terminal, run <code>ls .mcp.json</code> — it should exist in the current directory.
				</dd>

				<dt>Servers show as connected but no tools appear</dt>
				<dd>Each entry in <code>.mcp.json</code> spawns a separate <code>wp mcp-adapter serve</code> process. If the <code>wp</code> command fails (WP-CLI not in PATH, wrong <code>--path</code>, plugin not active), the server silently fails to start. Test the command directly in your terminal: <code>wp --path=/your/site mcp-adapter serve --server=lightspeed-testing-mcp-server --user=admin</code> — you should see <code>[MCP STDIO Bridge] MCP STDIO Bridge started</code>.</dd>

				<dt>Only one server's tools are visible</dt>
				<dd>You need a <strong>separate entry</strong> in <code>.mcp.json</code> for each server. A single entry can only connect to one <code>--server</code> value. Copy the entry, change the key name, and change the <code>--server</code> argument. After saving, restart Claude Code.</dd>

				<dt>Claude Code needs to be restarted after changing <code>.mcp.json</code></dt>
				<dd>Unlike VS Code, Claude Code does not hot-reload MCP servers. Close and reopen the window (or close the entire app and relaunch). You will see the connected servers listed in the session startup output.</dd>

				<dt>Wrong <code>--user</code> value</dt>
				<dd>The <code>--user</code> argument must be a valid WordPress username that exists on the local site. The user must have <code>manage_options</code> (Administrator). Using a user that does not exist causes the process to start but all ability calls return permission errors.</dd>

				<dt>MCP tools are available but LightSpeed abilities return errors</dt>
				<dd>
					MCP is not enabled on this site, or the operational environment is blocked. Check the <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=status' ) ); ?>">Status tab</a>. The most common fix for a local Studio site is to enable MCP in the <a href="<?php echo esc_url( admin_url( 'tools.php?page=lsx-mcp&tab=settings' ) ); ?>">Settings tab</a>.
				</dd>

				<dt><code>wp mcp-adapter serve</code> with no <code>--server</code> flag defaults to the LightSpeed server</dt>
				<dd>When no <code>--server</code> is specified, the MCP Adapter uses the first registered server — on this site that is <code>lightspeed-testing-mcp-server</code> because the LightSpeed MCP plugin registers it. Always specify <code>--server</code> explicitly in your config to avoid ambiguity.</dd>

				<dt>The <code>.mcp.json</code> config I wrote only had one entry</dt>
				<dd>Add a second entry for the LightSpeed testing server alongside the first. Both entries use the same <code>wp</code> command and <code>--path</code> — they only differ in the <code>--server</code> value and the JSON key name (which becomes the server's display name in Claude Code). See the config example above.</dd>

				<dt><code>@automattic/mcp-wordpress-remote</code> or native <code>"type": "http"</code> returns no tools for dev sites</dt>
				<dd>MCP Adapter v0.5.0+ uses the Streamable HTTP transport (protocol <code>2025-06-18</code>) which requires per-session <code>Mcp-Session-Id</code> headers. The <code>@automattic/mcp-wordpress-remote</code> npm package uses the older pre-session transport and is incompatible. Claude Code's native <code>"type": "http"</code> config also does not support this transport yet. Use <code>mcp-remote</code> (the official Anthropic stdio bridge) instead — it handles session management correctly. See the <a href="#doc-claude-code-devsite">dev site config example above</a>.</dd>
			</dl>
		</div>

		</div>

		<script>
		document.addEventListener( 'DOMContentLoaded', function () {
			var initialTab = <?php echo json_encode( $active_tab ); ?>;

			function showTab( tabId, pushState ) {
				document.querySelectorAll( '.lsx-mcp-tab' ).forEach( function ( b ) { b.classList.remove( 'active' ); } );
				document.querySelectorAll( '.lsx-mcp-panel' ).forEach( function ( p ) { p.classList.remove( 'active' ); } );
				var btn   = document.querySelector( '[data-tab="' + tabId + '"]' );
				var panel = document.getElementById( 'lsx-mcp-tab-' + tabId );
				if ( btn )   btn.classList.add( 'active' );
				if ( panel ) panel.classList.add( 'active' );
				if ( pushState !== false && history.replaceState ) {
					var url = new URL( window.location.href );
					url.searchParams.set( 'tab', tabId );
					history.replaceState( null, '', url.toString() );
				}
			}

			showTab( initialTab );

			document.querySelectorAll( '.lsx-mcp-tab' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					showTab( btn.dataset.tab );
				} );
			} );

			document.querySelectorAll( '.lsx-mcp-copy' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var pre = btn.closest( '.lsx-mcp-code-wrap' ).querySelector( 'pre' );
					if ( ! pre ) return;
					navigator.clipboard.writeText( pre.innerText ).then( function () {
						var orig = btn.textContent;
						btn.textContent = 'Copied!';
						btn.style.color = '#00a32a';
						setTimeout( function () { btn.textContent = orig; btn.style.color = ''; }, 2500 );
					} ).catch( function () {
						alert( 'Copy failed — select and copy the text manually.' );
					} );
				} );
			} );
		} );
		</script>
		<?php
	}

	// ── Rendering helpers ─────────────────────────────────────────────────────

	private static function badge( $bool ) {
		return $bool
			? '<span class="lsx-mcp-badge-ok">Yes</span>'
			: '<span class="lsx-mcp-badge-off">No</span>';
	}

	/**
	 * Renders a settings checkbox, marking it read-only when a constant overrides it.
	 */
	private static function settings_checkbox( $name, $settings_key, $label ) {
		$override = LSX_MCP_UI_Settings::is_constant_override( $settings_key );
		$checked  = $override
			? LSX_MCP_UI_Settings::get( $settings_key )
			: (bool) LSX_MCP_UI_Settings::get_raw( $settings_key, LSX_MCP_UI_Settings::get( $settings_key ) );

		$const_map = array(
			'enabled'                      => 'LSX_MCP_ENABLED',
			'enable_application_passwords' => 'LSX_MCP_ENABLE_APPLICATION_PASSWORDS',
			'enable_custom_server'         => 'LSX_MCP_ENABLE_CUSTOM_SERVER',
		);

		echo '<label>';
		echo '<input type="checkbox" name="' . esc_attr( $name ) . '" value="1"';
		echo checked( $checked, true, false );
		if ( $override ) {
			echo ' disabled readonly';
		}
		echo '> ';
		echo $label; // Already safe — caller controls this string.
		echo '</label>';

		if ( $override && isset( $const_map[ $settings_key ] ) ) {
			echo '<p class="description">Controlled by <code>' . esc_html( $const_map[ $settings_key ] ) . '</code> constant in wp-config.php.</p>';
		}
	}

	private static function code_block( $code, $filename = '' ) {
		echo '<div class="lsx-mcp-code-wrap">';
		echo '<div class="lsx-mcp-code-header">';
		if ( $filename ) {
			echo '<span class="lsx-mcp-code-filename">' . esc_html( $filename ) . '</span>';
		}
		echo '<button type="button" class="lsx-mcp-copy button button-small">Copy</button>';
		echo '</div>';
		echo '<pre class="lsx-mcp-code">' . esc_html( $code ) . '</pre>';
		echo '</div>';
	}
}
