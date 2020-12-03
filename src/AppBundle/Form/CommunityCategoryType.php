<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Category;
use AppBundle\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CommunityCategoryType extends CategoryType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('save');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_community_category';
    }
}
