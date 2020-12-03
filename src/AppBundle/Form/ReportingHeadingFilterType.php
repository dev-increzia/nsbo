<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ReportingHeadingFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateBefore', TextType::class, array('required' => false))
                ->add('dateAfter', TextType::class, array('required' => false))
                ->add('enabled', ChoiceType::class, array(
                    'choices' => array(
                        'Tous' => '',
                        'Actif' => 1,
                        'Inactif' => 0,
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false
                ))
                ->add('save', SubmitType::class);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_mapheading_filter';
    }
}
