<?php

/**
 * Configures default environment files.
 *
 * @package isaactorresmichel\Wordpress\Utils
 */

namespace isaactorresmichel\WordPress\Composer;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use isaactorresmichel\WordPress\WPstart\Utils;

/**
 * Default Environmetn Configurator Class.
 */
class DefaultEnvironmentConfigurator
{
    /**
     * Creates the default config files for project.
     *
     * @param Event $event Composer command event.
     *
     * @return void
     */
    public static function createEnvFiles(Event $event)
    {
        $io = $event->getIO();
        $fs = new Filesystem();

        $file = getcwd() . '/config/.env';
        $config = getcwd() . '/config/.env.example';

        $io->write('<info>Copying default .env configuration file.</info>');
        if (!$fs->exists($file)) {
            $io->write('<warning>The file does not exists. New .env configuration file is created.</warning>');
            $fs->copy($config, $file);
            return;
        }
        $io->write('<info>Default .env file exists. Skipping.</info>');
    }

    public static function copyContentFiles(Event $event)
    {
        $io = $event->getIO();
        $fs = new Filesystem();

        $origin = getcwd() . '/public/app/wp-content';
        $dest = getcwd() . '/public/content';
        $io->write('<info>Copying wp-content files to public...</info>');
        $fs->mirror($origin, $dest, null, ['override' => true]);
        $io->write('<info>Done</info>');
    }

    public static function copySqliteDriver(Event $event)
    {
        $io = $event->getIO();
        $fs = new Filesystem();
        Utils::loadEnv(getcwd() . "/config");

        if (Utils::getEnv('USE_MYSQL')) {
            $io->write('<info>ENV uses MySQL deleting sqlite-integration.</info>');
            $fs->remove(getcwd() . '/public/content/plugins/sqlite-integration');
            return;
        }

        $io->write('<info>Copying SQLite driver to WP content dir.</info>');
        $fs->copy(
            getcwd() . '/public/content/plugins/sqlite-integration/db.php',
            getcwd() . '/public/content/db.php'
        );
    }
}
