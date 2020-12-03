<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Push;

class GoodPlanPushType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateAt', DateTimeType::class, array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'mapped' => false,
            ))
            ->add('hourAt', TextType::class, array(
                'mapped' => false,
            ))
            ->add('content', TextareaType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Push::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_goodplan_push';
    }
}
