<?php

namespace AppBundle\Form;

use AppBundle\Repository\CityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\User;

class CityhallAdminType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('civility', ChoiceType::class, array(
                    'choices' => array(
                        'Monsieur' => 'male',
                        'Madame' => 'female',
                    ),
                    'expanded' => true,
                    'multiple' => false
                ))
                ->add('username', TextType::class)
                ->add('lastname', TextType::class)
                ->add('firstname', TextType::class)
                ->add('phone', TextType::class, array(
                    'attr' => array('pattern' => "^([-. ]?[0-9]{2}){5}", 'title' => "Numéro de téléphone invalid")
                ))
            ->add('city', TextType::class, array('attr' => ['data-CityAutoCompleteSuAdminInput-select-city' => 'true'], 'required' => true, 'mapped' => false))
                ->add('email', EmailType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_cityhall_admin';
    }
}
