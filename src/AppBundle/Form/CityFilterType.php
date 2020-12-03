<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class CityFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateBefore', TextType::class, array('required' => false))
                ->add('dateAfter', TextType::class, array('required' => false))
                ->add('name', TextType::class, array('required' => false))
                ->add('save', SubmitType::class);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_city_filter';
    }
}
