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

class CategoryType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
                ->add('type', ChoiceType::class, array(
                    'choices' => array(
                        'Pour les écrans A la Une et Agenda' => 'Activité groupe / association',
                        'Pour l’écran des Bons Plans' => 'Thème commerce / partenaire',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                ))
                
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Category::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_category';
    }
}
