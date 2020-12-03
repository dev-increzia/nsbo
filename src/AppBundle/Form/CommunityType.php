<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Entity\Community;
use AppBundle\Form\ImageType;
use AppBundle\Form\CityhallAdminType;
use AppBundle\Repository\CityRepository;
use AppBundle\Form\CommunitySettingsType;

class CommunityType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
            ->add('city', TextType::class, array('attr' => ['data-select-city' => 'true'], 'required' => true, 'mapped' => false))
            ->add('phone', TextType::class)
            ->add('email', EmailType::class)
            ->add('enabled', ChoiceType::class, array(
                'choices' => array(
                    'Actif' => 1,
                    'Inactif' => 0,
                ),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ))
            ->add('categories', CollectionType::class, array(
                'entry_type' => CommunityCategoryType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false
            ))
            ->add('image', ImageType::class, array('required' => false))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('suAdmin', CityhallAdminType::class, array(
                'required' => true,
            ))
            ->add('isPrivate', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('settings', CommunitySettingsType::class, array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('save', SubmitType::class);


        if ($options['isAdmin']) {
            $builder->add('gaApplication', TextType::class, array(
                'required' => false,
            ))
                ->add('gaApplicationProfileIDMOBILE', TextType::class, array(
                    'required' => false,
                ))
                ->add('gaApplicationProfileIDWEB', TextType::class, array(
                    'required' => false,
                ))
                ->add('gaBackoffice', TextType::class, array(
                    'required' => false,
                ))
                ->add('gaBackofficeProfileID', TextType::class, array(
                    'required' => false,
                ));
        }

        /*if(($options['update'] && $options['showMDP'] && $options['isPrivate']) || ($options['isAdmin'] && $options['update'] && $options['isPrivate'])) {
            $builder->add('password', TextType::class, array(
                'attr' => ['readonly' => true],
                'required' => false,
            ))
                ->add('expirationDate', DateType::class, array(
                    'attr' => ['readonly' => true],
                    'required' => false,
                ));
        }*/
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Community::class,
            'isAdmin' => false,

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_community';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_community';
    }
}
