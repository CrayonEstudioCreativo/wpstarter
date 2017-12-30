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
        $ns = new Filesystem();

        $file = getcwd() . '/config/.env';
        $config = getcwd() . '/config/.env.example';

        $io->write('<info>Checking if environment configuration file exists.</info>');
        if (!$ns->exists($file)) {
            $io->write('<warning>The file does not exists. New .env configuration file is created.</warning>');
            $ns->copy($config, $file);
        }
    }

    public static function copyContentFiles(Event $event)
    {
        $io = $event->getIO();
        $ns = new Filesystem();

        $origin = getcwd() . '/public/app/wp-content';
        $dest = getcwd() . '/public/content';
        $io->write('<info>Copying wp-content files to public...</info>');
        $ns->mirror($origin, $dest, null, ['override' => true]);
        $io->write('<info> Done</info>');
    }
}
