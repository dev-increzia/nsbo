<?php

namespace AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class CommunityUpdateType extends CommunityType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('suAdmin');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_cityhall_update';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_community';
    }
}
