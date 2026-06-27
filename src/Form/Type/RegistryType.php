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

use jonasarts\Bundle\RegistryBundle\Entity\RegistryKey;
use jonasarts\Bundle\RegistryBundle\Enum\RegistryKeyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Symfony Form.
 *
 * @extends AbstractType<RegistryKey>
 */
class RegistryType extends AbstractType
{
    /**
     * @param array{mode: 'new'|'edit'|null} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $read_only = 'edit' == $options['mode'];

        $builder
            ->add('user_id', IntegerType::class, [
                'required' => true,
                'attr' => $read_only ? ['readonly' => true] : [],
            ])
            ->add('key', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
                'required' => true,
                'attr' => $read_only ? ['readonly' => true] : [],
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
                'required' => true,
                'attr' => $read_only ? ['readonly' => true] : [],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Integer' => RegistryKeyType::INTEGER->value,
                    'Boolean' => RegistryKeyType::BOOLEAN->value,
                    'String' => RegistryKeyType::STRING->value,
                    'Float' => RegistryKeyType::FLOAT->value,
                    'DateTime' => RegistryKeyType::DATE->value,
                    'Time' => RegistryKeyType::TIME->value,
                    'Array' => RegistryKeyType::ARRAY->value,
                ],
                'required' => true,
                'disabled' => $read_only,
            ])
            ->add('value', TextareaType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistryKey::class,
            'mode' => null,
        ]);
    }
}
