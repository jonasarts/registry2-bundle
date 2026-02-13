<?php

declare(strict_types=1);

/*
 * This file is part of the jonasarts Registry bundle package.
 *
 * (c) Jonas Hauser <symfony@jonasarts.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace jonasarts\Bundle\RegistryBundle\Registry;

use Doctrine\ORM\EntityManagerInterface;
use jonasarts\Bundle\RegistryBundle\Engine\DoctrineRegistryEngine;
use jonasarts\Bundle\RegistryBundle\Registry\AbstractRegistry;

/**
 * DoctrineRegistry.
 *
 * Implementation of AbstractRegistry using doctrine for persistence.
 */
class DoctrineRegistry extends AbstractRegistry implements DoctrineRegistryInterface
{
    /**
     * Constructor
     */
    public function __construct(EntityManagerInterface $em, ?string $default_values_filename = null)
    {
        parent::__construct($default_values_filename);

        // create the engine
        $this->engine = new DoctrineRegistryEngine($em);
    }
}
