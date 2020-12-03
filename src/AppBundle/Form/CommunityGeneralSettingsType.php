<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Community;
use AppBundle\Repository\CityRepository;
use Vich\UploaderBundle\Form\Type\VichImageType;


class CommunityGeneralSettingsType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
                ->add('city', TextType::class, array('attr' => ['data-select-city' => 'true'], 'required' => true, 'mapped' => false))
                ->add('images', CollectionType::class, array(
                    'entry_type' => ImageType::class,
                    'entry_options' => array('label' => false),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label'=> false
                ))
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
                ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                ))
                ->add('presentation_title', TextType::class, array('required' => false))
                ->add('presentation_description', TextareaType::class, array('required' => false))
                ->add('video', VideoType::class, array('required' => false))
                ->add('image', ImageType::class, array('required' => false))
                ->add('help_page_content', TextareaType::class, array('required' => false))
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Community::class,
            
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
