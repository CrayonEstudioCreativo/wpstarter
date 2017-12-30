<?php

namespace isaactorresmichel\WordPress\WPstart\Commands;

use Joli\JoliNotif\NotifierFactory;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends DefaultCommand
{
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setHelp('Serve the application on the PHP development server')
            ->setDescription('Serve the application on the PHP development server')
            ->setAliases(['s'])
            ->addOption(
                'host',
                'H',
                InputOption::VALUE_OPTIONAL,
                'The host address to serve the application on.',
                'localhost'
            )
            ->addOption(
                'port',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The port to serve the application on.',
                8000
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $host = $this->input->getOption('host');
        $port = $this->input->getOption('port');
        $root = getcwd();
        $base = ProcessUtils::escapeArgument(__DIR__);
        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false));

        $this->notify("WPstart server started on http://{$host}:{$port}/");
        $this->output->writeln("<info> WPstart server started on http://{$host}:{$port}/ </info>");
        passthru("{$binary} -S {$host}:{$port} -t '{$root}/public' {$base}/server.php");
    }
}
