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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

use jonasarts\Bundle\RegistryBundle\Entity\SystemKey;

/**
 * Symfony Form
 *
 * @extends AbstractType<SystemKey>
 */
class SystemType extends AbstractType
{
    /**
     * @param array{mode: 'new'|'edit'|null} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $read_only = $options['mode'] == 'edit';

        $builder
            ->add('key', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(max: 255),
                ),
                'required' => true,
                'attr' => $read_only ? ['readonly' => true] : [],
            ))
            ->add('name', TextType::class, array(
                'constraints' => array(
                    new NotBlank(),
                    new Length(max: 255),
                ),
                'required' => true,
                'attr' => $read_only ? ['readonly' => true] : [],
            ))
            ->add('type', ChoiceType::class, array(
                'choices' => array('i' => 'Integer', 'b' => 'Boolean', 's' => 'String', 'f' => 'Float', 'd' => 'DateTime'),
                'required' => true,
                'disabled' => $read_only,
            ))
            ->add('value', TextareaType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => SystemKey::class,
            'mode' => null,
        ));
    }
}
