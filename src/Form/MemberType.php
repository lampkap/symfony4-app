<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthdate', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd-MM-yyyy',
                'label' => 'Jouw geboortedatum',
                'label_attr' => array(
                    'class' => 'form--label'
                ),
                'attr' => array(
                    'class' => 'form--input form--input__date',
                    'autocomplete' => 'off'
                )
            ))
            ->add('number', NumberType::class, array(
                'label' => 'Jouw lidnummer',
                'label_attr' => array(
                    'class' => 'form--label'
                ),
                'attr' => array(
                    'class' => 'form--input',
                )
            ))
            ->add('next', SubmitType::class, array(
                'label' => 'Volgende',
                'attr' => array(
                    'class' => 'form--submit'
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
