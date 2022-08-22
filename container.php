<?php

use DI\ContainerBuilder;

return (new ContainerBuilder())
->addDefinitions(require __DIR__ . '/config/app.php')
->addDefinitions(require __DIR__ . '/config/config.php')
->addDefinitions(require __DIR__ . '/config/dependencies.php')
->build();
