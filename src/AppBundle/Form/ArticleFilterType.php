<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateBefore', TextType::class, array('required' => false))
            ->add('dateAfter', TextType::class, array('required' => false))
            ->add('title', TextType::class, array('required' => false))
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
            ->add('type', ChoiceType::class, array(
                'choices' => array(
                    'Tous' => '',
                    'Associations' => 'association',
                    'Commerçants' => 'merchant',
                    'Citoyens' => 'user',
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
        return 'appbundle_article_filter';
    }
}
