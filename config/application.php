<?php

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

/** @var string Directory containing all of the site's files */
$root_dir = dirname(__DIR__);

/** @var string Document Root */
$public_dir = $root_dir . '/public';

/**
 * Expose global env() function from oscarotero/env
 */
Env::init();

/**
 * Use Dotenv to set required environment variables and load .env file in root
 */
$dotenv = new Dotenv\Dotenv($root_dir . '/config');
if ($dotenv->load()) {
    if (env('USE_MYSQL')) {
        $dotenv->required([
            'DB_NAME',
            'DB_USER',
            'DB_PASSWORD'
        ]);
    } else {
        $dotenv->required([
            'DB_FILE'
        ]);
    }
}

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
if (env('WP_ENV') === 'development'
    || php_sapi_name() == "cli"
    || $_SERVER['REMOTE_ADDR'] == '127.0.0.1'
    || $_SERVER['REMOTE_ADDR'] == "::1") {
    define('WP_ENV', 'development');
    define('WP_DEBUG', true);
} else {
    define('WP_ENV', 'production');
    define('WP_DEBUG', false);
}

/**
 * Set up our global environment constant and load its config first
 * Default: production
 */
$env_config = __DIR__ . '/config/env/' . WP_ENV . '.php';
if (file_exists($env_config)) {
    require_once $env_config;
}

/** Configuration definitions for project */
$definitions = \isaactorresmichel\WordPress\Utils\ServerPathDefinitions::instance($public_dir)
    ->setWpContentDir(dirname(__DIR__) . "/public/content")
    ->setWpApplicationDir(dirname(__DIR__) . "/public/app");

define('WP_DEFAULT_THEME', 'twentyseventeen');
define(
    'WP_HOME',
    $definitions->getBaseUrl()
);
define(
    'WP_SITEURL',
    "{$definitions->getBaseUrl()}{$definitions->getWordpressCodebaseRelativePath()}"
);
define(
    'WP_CONTENT_DIR',
    $definitions->getWpContentDir()
);
define(
    'WP_CONTENT_URL',
    "{$definitions->getBaseUrl()}{$definitions->getWordpressContentRelativePath()}"
);

/**
 * DB settings
 */
define('USE_MYSQL', env('USE_MYSQL'));
if (USE_MYSQL) {
    // ** MySQL settings - You can get this info from your web host ** //
    /** The name of the database for WordPress */
    define('DB_NAME', env('DB_NAME'));

    /** MySQL database username */
    define('DB_USER', env('DB_USER'));

    /** MySQL database password */
    define('DB_PASSWORD', env('DB_PASSWORD'));

    /** MySQL hostname */
    define('DB_HOST', env('DB_HOST'));

    /** Database Charset to use in creating database tables. */
    define('DB_CHARSET', env('DB_CHARSET'));

    /** The Database Collate type. Don't change this if in doubt. */
    define('DB_COLLATE', env('DB_COLLATE'));

} else {
    // Use SQLite
    define('DB_DIR', WP_CONTENT_DIR . '/database/');
    define('DB_FILE', env('DB_FILE'));
}

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = env('DB_PREFIX') ? : 'wp_';

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */

include_once(__DIR__ . '/wp-salts.php');

/**
 * Custom Settings
 */
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', env('DISABLE_WP_CRON') ? : false);

define('FS_METHOD', 'direct');
define('DISALLOW_FILE_EDIT', true);

define('WP_MEMORY_LIMIT', '128M');
define('WP_MAX_MEMORY_LIMIT', '256M');

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
    define('ABSPATH', $public_dir . '/app/');
}

/** Real absolute path to the site directory. */
if (!defined('REAL_ABSPATH')) {
    define('REAL_ABSPATH', dirname(__DIR__) . '/');
}
