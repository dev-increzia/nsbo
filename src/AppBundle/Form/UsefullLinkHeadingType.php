<?php

namespace AppBundle\Form;

use AppBundle\Entity\UsefullLinkHeading;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\MapHeading;
use AppBundle\Form\ImageType;

class UsefullLinkHeadingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('title', TextType::class)
                ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                ))
                ->add('url', TextType::class)
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => UsefullLinkHeading::class

        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_usefulllinkheading';
    }
}
