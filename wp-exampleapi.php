<?php
/*
Plugin Name: Example API
Plugin URI: https://exampleapi.com
Description: Just another example API plugin
Text Domain: example-api
Version: 1.0
"namespaces": [
    "example-api/v1"
  ]
*/

define( 'EXA_VERSION', '5.2.2' );
define( 'EXA_REQUIRED_WP_VERSION', '5.3' );
define( 'EXA_PLUGIN', __FILE__ );
define( 'EXA_PLUGIN_BASENAME', plugin_basename( EXA_PLUGIN ) );
define( 'EXA_PLUGIN_NAME', trim( dirname( EXA_PLUGIN_BASENAME ), '/' ) );
define( 'EXA_PLUGIN_DIR', untrailingslashit( dirname( EXA_PLUGIN ) ) );
define( 'EXA_PLUGIN_MODULES_DIR', EXA_PLUGIN_DIR . '/modules' );


if ( ! defined( 'EXA_VERIFY_NONCE' ) ) {
	define( 'EXA_VERIFY_NONCE', false );
}

// Deprecated, not used in the plugin core. Use EXA_plugin_url() instead.
define( 'EXA_PLUGIN_URL',
	untrailingslashit( plugins_url( '', EXA_PLUGIN ) ) );

require_once EXA_PLUGIN_DIR . '/settings.php';