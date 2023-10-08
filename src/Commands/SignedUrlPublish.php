<?php

namespace Michalsn\CodeIgniterSignedUrl\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Publisher\Publisher;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;
use Throwable;

class SignedUrlPublish extends BaseCommand
{
    protected $group       = 'SignedUrl';
    protected $name        = 'signedurl:publish';
    protected $description = 'Publish SignedUrl config file into the current application.';

    /**
     * @return void
     */
    public function run(array $params)
    {
        $source = service('autoloader')->getNamespace('Michalsn\\CodeIgniterSignedUrl')[0];

        $publisher = new Publisher($source, APPPATH);

        try {
            $publisher->addPaths([
                'Config/SignedUrl.php',
            ])->merge(false);
        } catch (Throwable $e) {
            $this->showError($e);

            return;
        }

        foreach ($publisher->getPublished() as $file) {
            $contents = file_get_contents($file);
            $contents = str_replace('namespace Michalsn\\CodeIgniterSignedUrl\\Config', 'namespace Config', $contents);
            $contents = str_replace('use CodeIgniter\\Config\\BaseConfig', 'use Michalsn\\CodeIgniterSignedUrl\\Config\\SignedUrl as BaseSignedUrl', $contents);
            $contents = str_replace('class SignedUrl extends BaseConfig', 'class SignedUrl extends BaseSignedUrl', $contents);
            file_put_contents($file, $contents);
        }

        CLI::write(CLI::color('  Published! ', 'green') . 'You can customize the configuration by editing the "app/Config/SignedUrl.php" file.');
    }
}
