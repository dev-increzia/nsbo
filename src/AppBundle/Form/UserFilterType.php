<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Repository\MerchantRepository;
use AppBundle\Repository\AssociationRepository;

class UserFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityhall = $options['cityhall'];
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
                ->add('role', ChoiceType::class, array(
                    'choices' => array(
                        'Tous les rôles' => '',
                        'Asso super-admin' => 'associationSuAdmin',
                        'Asso admin' => 'associationAdmin',
                        'Commerçant super-admin' => 'merchantSuAdmin',
                        'Commerçant admin' => 'merchantAdmin',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false
                ))
                ->add('association', EntityType::class, array(
                    'class' => 'AppBundle:Association',
                    'choice_label' => 'name',
                    'placeholder' => 'Toutes les associations',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (AssociationRepository $repo) use ($cityhall) {
                        return $repo->findAllByCommunity($cityhall);
                    },
                ))
                ->add('merchant', EntityType::class, array(
                    'class' => 'AppBundle:Merchant',
                    'choice_label' => 'name',
                    'placeholder' => 'Tous les commerçants',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (MerchantRepository $repo) use ($cityhall) {
                        return $repo->findAllByCommunity($cityhall);
                    },
                ))
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'cityhall' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_user_filter';
    }
}
