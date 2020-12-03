<?php

namespace AppBundle\Form;

use AppBundle\Entity\CategoryPhoneBookHeading;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneBookHeadingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('title', TextType::class)
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))


            ->add('objects', CollectionType::class, array(
                'entry_type' => CategoryPhoneBookHeadingType::class,
                'entry_options' => array('label' => false),
                'by_reference' => false,
                'allow_add'    => true,
                'allow_delete'    => true,
                'label' => false,

            ))
            ->add('save', SubmitType::class);
        if($options['communityHavePredefinedObjects'] && $options['headingHavePredefinedObjects'])
        {
            $builder->add('havePredefinesdObjects', CheckboxType::class, array(
                'required' => false,
                'mapped' => false,
                'data' => true,

            ));
        }elseif($options['communityHavePredefinedObjects'] && !$options['headingHavePredefinedObjects']){
            $builder->add('havePredefinesdObjects', CheckboxType::class, array(
                'required' => false,
                'mapped' => false,
                'attr'=>array('disabled'=>true)

            ));
        }else{
            $builder->add('havePredefinesdObjects', CheckboxType::class, array(
                'required' => false,
                'mapped' => false

            ));
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\PhoneBookHeading',
            'communityHavePredefinedObjects' => false,
            'headingHavePredefinedObjects' => false,

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_phonebookheading';
    }


}
