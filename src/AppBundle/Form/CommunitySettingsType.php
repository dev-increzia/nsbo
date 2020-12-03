<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\AccessAdminCommunity;

class CommunitySettingsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                
                ->add('settings', EntityType::class, array(
                                'class' => 'AppBundle:CommunitySetting',
                                'choice_label' => 'name',
                                'placeholder' => '',
                                'multiple' => true,
                                'expanded' => true,
                                'required' => false,
                                'mapped' => false,
                                'query_builder' => function (\AppBundle\Repository\CommunitySettingRepository $repo) {
                                    return $repo->findAllSettings();
                                },
                            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_community_settings';
    }
}
