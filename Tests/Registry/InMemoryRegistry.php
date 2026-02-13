<?php

declare(strict_types=1);

namespace jonasarts\Bundle\RegistryBundle\Tests\Registry;

use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;
use jonasarts\Bundle\RegistryBundle\Tests\Engine\InMemoryRegistryEngine;

/**
 * Concrete AbstractRegistry using InMemoryRegistryEngine for testing.
 */
class InMemoryRegistry extends AbstractRegistry
{
    private InMemoryRegistryEngine $memoryEngine;

    public function __construct(?string $default_values_filename = null, string $delimiter = ':')
    {
        parent::__construct($default_values_filename);

        $this->memoryEngine = new InMemoryRegistryEngine();
        $this->engine = $this->memoryEngine;
        $this->delimiter = $delimiter;
    }

    public function getEngine(): InMemoryRegistryEngine
    {
        return $this->memoryEngine;
    }
}
