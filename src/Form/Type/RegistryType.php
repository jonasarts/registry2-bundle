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

namespace jonasarts\Bundle\RegistryBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey;

/**
 * Symfony Form
 */
class RegistryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $read_only = $options['mode'] == 'edit';

        $builder
            ->add('userid', 'integer', array(
                'required' => true,
                'read_only' => $read_only,
            ))
            ->add('key', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255)),
                ),
                'required' => true,
                'read_only' => $read_only,
            ))
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(array('max' => 255)),
                ),
                'required' => true,
                'read_only' => $read_only,
            ))
            ->add('type', 'choice', array(
                'choices' => array('i' => 'Integer', 'b' => 'Boolean', 's' => 'String', 'f' => 'Float', 'd' => 'DateTime'),
                'required' => true,
                'disabled' => $read_only,
            ))
            ->add('value', 'textarea');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => RegistryKey::class,
            'mode' => null,
        ));
    }

    public function getName(): string
    {
        return 'registry_registry';
    }
}
