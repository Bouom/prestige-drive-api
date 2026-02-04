<?php

echo "Starting key generation..." . PHP_EOL;

try {
    $config = [
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];
    
    echo "Creating new key..." . PHP_EOL;
    $res = openssl_pkey_new($config);
    
    if ($res === false) {
        echo 'Error: Unable to create key. OpenSSL Error: ' . openssl_error_string() . PHP_EOL;
        exit(1);
    }
    
    echo "Exporting private key..." . PHP_EOL;
    openssl_pkey_export($res, $privKey);
    
    echo "Getting public key..." . PHP_EOL;
    $pubKey = openssl_pkey_get_details($res);
    
    if ($pubKey === false) {
        echo 'Error: Unable to get public key' . PHP_EOL;
        exit(1);
    }
    
    echo "Writing private key to storage/oauth-private.key..." . PHP_EOL;
    $written = file_put_contents('storage/oauth-private.key', $privKey);
    echo "Wrote $written bytes" . PHP_EOL;
    
    echo "Writing public key to storage/oauth-public.key..." . PHP_EOL;
    $written = file_put_contents('storage/oauth-public.key', $pubKey['key']);
    echo "Wrote $written bytes" . PHP_EOL;
    
    chmod('storage/oauth-private.key', 0600);
    chmod('storage/oauth-public.key', 0644);
    
    echo 'Keys generated successfully' . PHP_EOL;
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
