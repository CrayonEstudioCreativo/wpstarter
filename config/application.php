<?php
/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);

/** @var string Document Root */
$webroot_dir = $root_dir . '/public';

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = new Dotenv\Dotenv($root_dir);
if (file_exists($root_dir . '/.env') && $dotenv->load()) {
    if(env('USE_MYSQL')){
      $dotenv->required([
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
        'WP_HOME',
        'WP_SITEURL'
      ]);
    } else {
      $dotenv->required([
        'DB_FILE',
        'WP_HOME',
        'WP_SITEURL'
      ]);
    }
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
define('WP_ENV', env('WP_ENV') ?: 'production');
$env_config = __DIR__ . '/env/' . WP_ENV . '.php';
if (file_exists($env_config)) {
    require_once $env_config;
}

/**
 * URLs
 */
define('WP_HOME', env('WP_HOME'));
define('WP_SITEURL', env('WP_SITEURL'));

/**
 * Custom Content Directory
 */
define('CONTENT_DIR', '/content');
define('WP_CONTENT_DIR', $webroot_dir . CONTENT_DIR);
define('WP_CONTENT_URL', WP_HOME . CONTENT_DIR);

/**
 * DB settings
 */
define('USE_MYSQL', env('USE_MYSQL'));
if (USE_MYSQL) {
    define('DB_NAME', env('DB_NAME'));
    define('DB_USER', env('DB_USER'));
    define('DB_PASSWORD', env('DB_PASSWORD'));
    define('DB_HOST', env('DB_HOST') ?: 'localhost');
    define('DB_CHARSET', 'utf8mb4');
    define('DB_COLLATE', '');
} else {
    // Use SQLite
    define('FQDBDIR', WP_CONTENT_DIR . '/database/');
    define('DB_FILE', env('DB_FILE'));
}
$table_prefix = env('DB_PREFIX') ?: 'wp_';


/**
 * Salts
 */
include_once(__DIR__ . '/wp-salts.php');

/**
 * Custom Settings
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ?: false);
define('DISALLOW_FILE_EDIT', true);

/**
 * Bootstrap WordPress
 */
if (!defined('ABSPATH')) {
    define('ABSPATH', $webroot_dir . '/app/');
}