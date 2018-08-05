<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GiftType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthdate', HiddenType::class, array(
                'data' => $options['data']['birthdate']
            ))
            ->add('number', HiddenType::class, array(
                'data' => $options['data']['number']
            ))
            ->add('gift', HiddenType::class)
            ->add('next', SubmitType::class, array(
                'label' => 'Volgende',
                'attr' => array(
                    'class' => 'form--submit'
                )
            ))
        ;
    }
}
