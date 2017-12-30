<?php

namespace isaactorresmichel\WordPress\WPstart\Commands;

use Joli\JoliNotif\Notification;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use isaactorresmichel\WordPress\WPstart\Utils;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeployerCommand extends DefaultCommand
{
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setHelp("This command is an auxiliary to quickly deploy wordpress"
                . "sites in L[A/E]MP environments. Creating the database and config files"
                . "for your web server.")
            ->setDescription("This command is an auxiliary to quickly deploy wordpress"
                . "sites in L[A/E]MP environments. Creating the database and config files"
                . "for your web server.")
            ->setAliases(['d'])
            ->addOption('apache', 'a', InputOption::VALUE_OPTIONAL, 'Deploy on apache env.', false)
            ->addOption('nginx', 'e', InputOption::VALUE_OPTIONAL, 'Deploy on nginx env.', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        if ($this->input->getOption('apache') !== false && $this->input->getOption('nginx') !== false) {
            $message = 'Error: Invalid option, only NGINX or APACHE should be selected when deploying the server.';
            $this->notify($message);
            throw new \InvalidArgumentException($message);
        } elseif ($this->input->getOption('apache') === false && $this->input->getOption('nginx') === false) {
            $input->setOption('nginx', null);
        }

        if ($this->input->getOption('apache') !== false) {
            $output->writeln("<b>Deploying on APACHE</b>");
            $output->writeln($formattedLine);

            $this->deployApacheWebServer($this->input->getOption('apache'));
        }

        if ($this->input->getOption('nginx') !== false) {
            $output->writeln("<b>Deploying on NGINX</b>");
            $this->deployNginxWebServer($this->input->getOption('nginx'));
        }

        $this->appendSiteToHostsFile();

        if (Utils::getEnv('USE_MYSQL')) {
            $this->createDatabase();
        }
    }

    private function createDatabase()
    {
        $question_helper = $this->getHelper('question');
        $this->output->writeln(['', '<b>Creating database.</b>']);
        try {
            $question = new Question("Enter MySQL root username: ");
            $username = $question_helper->ask($this->input, $this->output, $question);

            $question = new Question("Enter MySQL root password: ");
            $question->setHidden(true);
            $password = $question_helper->ask($this->input, $this->output, $question);

            $host = Utils::getEnv('DB_HOST');

            // Create connection
            $conn = new \mysqli($host, $username, $password);

            $controlador = new \mysqli_driver();
            $controlador->report_mode = MYSQLI_REPORT_ALL;

            // Create database
            $db = Utils::getEnv('DB_NAME');
            $dbuser = Utils::getEnv('DB_USER');
            $dbpwd = Utils::getEnv('DB_PASSWORD');
            $charset = Utils::getEnv('DB_CHARSET');
            $collation = Utils::getEnv('DB_COLLATE');


            $query = "DROP DATABASE IF EXISTS {$db};";
            $conn->query($query);

            $query = "CREATE DATABASE {$db} CHARACTER SET '{$charset}' COLLATE '{$collation}';";

            if ($conn->query($query) === true) {
                $this->output->writeln("<info>Database created.</info>");
            }

            $query = "DROP USER IF EXISTS '{$dbuser}'@'{$host}';";
            $conn->query($query);

            $query = "CREATE USER '{$dbuser}'@'{$host}' IDENTIFIED BY '{$dbpwd}'";
            if ($conn->query($query) === true) {
                $this->output->writeln("<info>Database user created.</info>");
            }

            $query = "GRANT ALL PRIVILEGES ON `{$db}`.* to '{$dbuser}'@'{$host}'";
            if ($conn->query($query) === true) {
                $this->output->writeln("<info>Granted all privileges to user.</info>");
            }

            $conn->close();
        } catch (\Exception $e) {
            $this->notify("Error: {$e->getMessage()}");
            throw $e;
        }
    }

    private function appendSiteToHostsFile()
    {
        try {
            if (!$hosts = file_get_contents('/etc/hosts')) {
                throw new \Exception('Error while trying to read hosts file.');
            }

            $this->output->writeln(['', '<b>Adding site to hosts file.</b>']);

            if (strpos($hosts, "\t{$this->getSiteName()}\n") === false) {
                $prefix = substr($hosts, -1) != "\n" ? "\n" : '';
                $this->fs->appendToFile('/etc/hosts', "{$prefix}127.0.0.1 \t{$this->getSiteName()}\n");
                $this->output->writeln("<info>Site {$this->getSiteName()} added to hosts file.</info>");
                return;
            }
            $this->output->writeln("<info>Skipped. Site {$this->getSiteName()} was already added on hosts file.</info>");
        } catch (\Exception $e) {
            $this->notify("Error: {$e->getMessage()}");
            throw $e;
        }
    }

    private function deployApacheWebServer($path)
    {
        $message = 'APACHE deployment has not been implemented.';
        $this->notify($message);
        $this->output->writeln("<error>{$message}</error>");
    }

    private function deployNginxWebServer($path)
    {
        $cwd = getcwd();

        // TODO: Add better directory detection for the sites-enabled dir.
        // For the moment assume by default that we're on a unix/ubuntu system.
        $path = !empty(trim($path)) ? trim($path) : "/etc/nginx/sites-enabled";


        /** @var QuestionHelper $question_helper */
        $question_helper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Is the NGINX <comment>sites-enabled</comment> path "
            . "directory correct? (Directory: <comment>{$path}</comment>) [Y/n]: ", true);

        try {
            if (!$question_helper->ask($this->input, $this->output, $question)) {
                $question = new Question("Enter the <comment>sites-enabled<comment> directory for the system: ");

                $fs = $this->fs;

                $question->setNormalizer(function ($value) {
                    return $value ? trim($value) : '';
                });

                $path = $question_helper->ask($this->input, $this->output, $question);
            }

            if (!$this->fs->exists($path) || !is_writable($path)) {
                $message = 'NGINX sites-enabled directory doesn\'t exists or is not writable by current user.';
                throw new \Exception($message);
            }

            if (!$pairs = $this->getConfigPairs()) {
                $message = 'Configuration pairs couldn\'t be loaded from config file.';
                throw new \Exception($message);
            }

            if (!$config = file_get_contents($cwd . '/config/ngix.conf.example')) {
                $message = 'Configuration pairs couldn\'t be loaded from default file.';
                throw new \Exception($message);
            }

            $config_dir = $cwd . "/config/wpstart.{$this->getSiteName()}.conf";

            $this->output->write("<info>Creating local NGINX config file...</info>");
            if (file_put_contents($config_dir, strtr($config, $pairs)) === false) {
                $message = 'Configuration file couldn\'t be created locally.';
                throw new \Exception($message);
            }
            $this->output->writeln(" <info>DONE.</info>");

            $this->fs->symlink($config_dir, "{$path}/wpstart.{$this->getSiteName()}.conf");

            $this->output->write("<info>Validating NGINX config...</info>");
            if ($error = $this->invalidNginxTest()) {
                throw new \Exception($error);
            }
            $this->output->writeln(" <info>DONE.</info>");

            $this->output->write("<info>Restarting NGINX...</info>");
            $this->restartNginx();
            $this->output->writeln([
                " <info>DONE.</info>",
                '',
                "<b>Site deployed to</b> <comment>http://{$this->getSiteName()}</comment>"
            ]);
        } catch (\Exception $e) {
            $this->notify("Error: {$e->getMessage()}");
            throw $e;
        }
    }

    private function restartNginx()
    {
        exec('service nginx restart 2>&1', $output, $result);
        if ($result) {
            throw new \Exception(reset($output));
        }
    }

    private function invalidNginxTest()
    {
        $configtest = exec('nginx -tq 2>&1');
        if (empty($configtest)) {
            return false;
        }

        return $configtest;
    }

    private function getConfigPairs()
    {
        $pairs = Utils::getConfigPairs(getcwd() . "/config");
        $pairs["%root_dir%"] = getcwd() . "/public";

        return $pairs;
    }

    private function getSiteName()
    {
        return Utils::getEnv('SERVER_NAME');
    }
}
