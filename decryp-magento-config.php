<?php

/*================================================================
#- IMPLEMENTATION
#-    author          DanyKurosaki
#-    github          https://github.com/danykurosaki
#-    Purpose         Get the stored encrypted keys in magento database
#-
#================================================================*/

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterfaceFactory;

require __DIR__ . '/app/bootstrap.php';

// CONSTANTS: replace with your info
// Available in the file: app/etc/env.php crypt => key =>
const CRYPT_KEY = '';

// DATA path in table core_config_data example: vendor/api/key
const PATH_CORE_CONFIG = '';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get(State::class);
$state->setAreaCode('frontend');

// Dependency Injection
$encryptorFactory = $objectManager->get(EncryptorInterfaceFactory::class);

// Custom Deployment Configuration
class CustomDeploymentConfig extends \Magento\Framework\App\DeploymentConfig
{
    public function get($key = null, $defaultValue = null): string
    {
        return CRYPT_KEY;
    }
}

// Creating instances
$deploymentConfig = $objectManager->get(CustomDeploymentConfig::class);
$encryptor = $encryptorFactory->create(['deploymentConfig' => $deploymentConfig]);

// Database connection
$resourceConnection = $objectManager->get(ResourceConnection::class);
$connection = $resourceConnection->getConnection();

// Fetching encrypted data
$query = $connection->select()->from('core_config_data', 'value')->where('path = ?', PATH_CORE_CONFIG);
$result = $connection->fetchOne($query);

echo "Decrypted key: '", $encryptor->decrypt($result), "'\n";
