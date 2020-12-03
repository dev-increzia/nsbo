<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;

class ReportingFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateBefore', TextType::class, array('required' => false))
                ->add('dateAfter', TextType::class, array('required' => false))
                ->add('title', TextType::class, array('required' => false))
                ->add('category', EntityType::class, array(
                    'class' => 'AppBundle:ReportingCategory',
                    'choice_label' => 'name',
                    'placeholder' => 'Toutes',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                ))
                ->add('moderate', ChoiceType::class, array(
                    'choices' => array(
                        'Tous' => '',
                        'En cours' => 'wait',
                        'Traité' => 'on',
                        'Non traité' => 'off',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                ))
                ->add('save', SubmitType::class);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_reporting_filter';
    }
}
