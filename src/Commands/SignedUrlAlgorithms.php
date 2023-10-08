<?php

namespace Michalsn\CodeIgniterSignedUrl\Commands;

use PHPUnit\Framework\Attributes\CodeCoverageIgnore;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

#[CodeCoverageIgnore]
class SignedUrlAlgorithms extends BaseCommand
{
    protected string $group       = 'SignedUrl';
    protected string $name        = 'signedurl:algorithms';
    protected string $description = 'Show the list of algorithms that can be used to sign the URL.';

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
