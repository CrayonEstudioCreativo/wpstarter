<?php

namespace isaactorresmichel\WordPress\WPstart\Commands;

use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

abstract class DefaultCommand extends Command
{
    /**
     * Input handler.
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * Output handler.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Lib Notify Manager
     *
     * @var \Joli\JoliNotif\Notifier
     */
    protected $notifier;

    /**
     * Filesystem manager
     * @var \Symfony\Component\Filesystem\Filesystem;
     */
    protected $fs;

    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->notifier = NotifierFactory::create();
        $this->fs = new Filesystem();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setIO($input, $output);
    }

    protected function setIO(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->output->getFormatter()->setDecorated(true);
        $this->output->getFormatter()->setStyle(
            'b',
            new OutputFormatterStyle('green', 'black', array('bold', 'blink'))
        );
    }

    protected function notify($body, $icon = null)
    {
        if (!$this->notifier->isSupported()) {
            return false;
        }

        $notification = (new Notification())
            ->setBody($body)
            ->setTitle('WPstart');

        if ($icon && $this->fs->exists($icon)) {
            $notification->setIcon($_icon);
        }

        $this->notifier->send($notification);
    }
}
