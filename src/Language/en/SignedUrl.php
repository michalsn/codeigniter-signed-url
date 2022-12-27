<?php

return [
    'incorrectAlgorithm' => 'Algorithm is incorrect, please run command: "php spark signedurl:algorithms" to see available options.',
    'invalidAlgorithm'   => 'Algorithm is invalid or not supported.',
    'emptyExpirationKey' => 'Expiration key cannot be empty.',
    'emptySignatureKey'  => 'Signature key cannot be empty.',
    'emptyAlgorithmKey'  => 'Algorithm key cannot be empty.',
    'sameKeyNames'       => 'Expiration, Signature or Algorithm keys cannot share the same name.',
    'emptyEncryptionKey' => 'Encryption key is missing, please run command: "php spark key:generate"',
    'missingSignature'   => 'This URL have to be signed.',
    'urlNotValid'        => 'This URL is not valid.',
    'urlExpired'         => 'This URL has expired.',
];
