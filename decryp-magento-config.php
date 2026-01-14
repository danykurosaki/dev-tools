<?php

/*================================================================
#- IMPLEMENTATION
#-    author          DanyKurosaki
#-    github          https://github.com/danykurosaki
#-    Purpose         Get stored encrypted keys in Magento database
#-    Usage           php decrypt-magento-config.php
#================================================================*/

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorFactory;

require __DIR__ . '/app/bootstrap.php';

// CONSTANTS: replace with your info
// Available in app/etc/env.php under 'crypt' => ['key' => '...']
const CRYPT_KEY = '';          // <- fill here your crypt key
const PATH_CORE_CONFIG = '';   // <- ej: 'vendor/api/key'

// Scope config
const CONFIG_SCOPE    = 'default'; // default | websites | stores
const CONFIG_SCOPE_ID = 0;

if (CRYPT_KEY === '' || PATH_CORE_CONFIG === '') {
    fwrite(STDERR, "Please set CRYPT_KEY and PATH_CORE_CONFIG constants.\n");
    exit(1);
}

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

/** @var ResourceConnection $resourceConnection */
$resourceConnection = $objectManager->get(ResourceConnection::class);
$connection = $resourceConnection->getConnection();

// Resolve table name with prefix
$table = $resourceConnection->getTableName('core_config_data');

// Fetch encrypted value (with scope)
$query = $connection->select()
    ->from($table, 'value')
    ->where('path = ?', PATH_CORE_CONFIG)
    ->where('scope = ?', CONFIG_SCOPE)
    ->where('scope_id = ?', CONFIG_SCOPE_ID);

$encryptedValue = $connection->fetchOne($query);

if ($encryptedValue === false) {
    fwrite(
        STDERR,
        "No value found for path: " . PATH_CORE_CONFIG .
        " (scope: " . CONFIG_SCOPE . ", scope_id: " . CONFIG_SCOPE_ID . ")\n"
    );
    exit(1);
}

/** @var EncryptorFactory $encryptorFactory */
$encryptorFactory = $objectManager->get(EncryptorFactory::class);

/** @var Encryptor $encryptor */
$encryptor = $encryptorFactory->create([
    'data' => ['key' => CRYPT_KEY],
]);

$decrypted = $encryptor->decrypt($encryptedValue);

echo "Decrypted value for '" . PATH_CORE_CONFIG . "': '" . $decrypted . "'" . PHP_EOL;
