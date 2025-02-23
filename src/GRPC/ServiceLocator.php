<?php

declare(strict_types=1);

namespace Spiral\RoadRunnerBridge\GRPC;

use Psr\Container\ContainerInterface;
use Spiral\RoadRunner\GRPC\ServiceInterface;
use Spiral\Tokenizer\ClassesInterface;

final class ServiceLocator implements LocatorInterface
{
    public function __construct(
        private readonly ClassesInterface $classes,
        private readonly ContainerInterface $container
    ) {
    }

    public function getServices(): array
    {
        $result = [];

        foreach ($this->classes->getClasses(ServiceInterface::class) as $service) {
            if (! $service->isInstantiable()) {
                continue;
            }

            $instance = $this->container->get($service->getName());

            foreach ($service->getInterfaces() as $interface) {
                if ($interface->isSubclassOf(ServiceInterface::class)) {
                    $result[$interface->getName()] = $instance;
                }
            }
        }

        return $result;
    }
}
