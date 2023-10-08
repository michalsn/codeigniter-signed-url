<?php

namespace Michalsn\CodeIgniterSignedUrl\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

class SignedUrlAlgorithms extends BaseCommand
{
    protected $group       = 'SignedUrl';
    protected $name        = 'signedurl:algorithms';
    protected $description = 'Show the list of algorithms that can be used to sign the URL.';

    /**
     * @return void
     */
    public function run(array $params)
    {
        $thead = ['#', 'Algorithm'];
        $tbody = [];

        $list = hash_hmac_algos();

        foreach ($list as $key => $item) {
            $tbody[] = [++$key, $item];
        }

        CLI::table($tbody, $thead);
    }
}
