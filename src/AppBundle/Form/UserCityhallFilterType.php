<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class UserCityhallFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lastname', TextType::class, array('required' => false))
                ->add('firstname', TextType::class, array('required' => false))
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
        return 'appbundle_user_cityhall_filter';
    }
}
