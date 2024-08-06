<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\DBAL\Configuration;
use EonX\EasyBugsnag\Tests\Stub\Connection\ConnectionStub;
use Symfony\Config\EasyBugsnagConfig;

return static function (EasyBugsnagConfig $easyBugsnagConfig, ContainerConfigurator $containerConfigurator): void {
    $easyBugsnagConfig->apiKey('my-api-key');

    $easyBugsnagConfig->doctrineDbal()
        ->enabled(true);

    $services = $containerConfigurator->services();

    $services->set('doctrine.dbal.default_connection.configuration')
        ->class(Configuration::class);

    $services->set('doctrine.dbal.default_connection')
        ->class(ConnectionStub::class);
};
