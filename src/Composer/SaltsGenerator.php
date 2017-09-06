<?php
namespace isaactorresmichel\WordPress\Composer;


class SaltsGenerator
{
    private $options = [];

    public function __construct($options = [])
    {
        $this->options += $options;
    }

    private function getFromApi()
    {
        if (PHP_SAPI !== 'cli') {
            throw new \Exception('SaltsGenerator should be invoked via the CLI version of PHP, not the ' . PHP_SAPI . ' SAPI' . PHP_EOL);
        }

        $response = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');

        if (!$response) {
            throw new \Exception("Couldn't generate salts keys from API.");
        }

        $file = getcwd() . '/config/wp-salts.php';

        if (file_exists($file)) {
            unlink($file);
        }

        if (file_put_contents($file, "<?php\n" . $response)) {
            echo "Salts generated.\n";
            return;
        }

        throw new \Exception("Couldn't generate salts keys from API.");
    }

    public static function generate()
    {
        $instance = new static();
        $instance->getFromApi();
    }

}