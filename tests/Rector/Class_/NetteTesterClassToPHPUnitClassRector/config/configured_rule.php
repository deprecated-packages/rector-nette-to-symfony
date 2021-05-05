<?php

declare(strict_types=1);

use Rector\NetteToSymfony\Rector\Class_\NetteTesterClassToPHPUnitClassRector;
use Rector\NetteToSymfony\Rector\StaticCall\NetteAssertToPHPUnitAssertRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../../../../../config/config.php');

    $services = $containerConfigurator->services();
    $services->set(NetteAssertToPHPUnitAssertRector::class);
    $services->set(NetteTesterClassToPHPUnitClassRector::class);
};
