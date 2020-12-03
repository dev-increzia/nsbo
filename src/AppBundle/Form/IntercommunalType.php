<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Intercommunal;

class IntercommunalType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class)
                ->add('cities', EntityType::class, array(
                    'required' => false,
                    'class' => 'AppBundle:City',
                    'choice_label' => function ($entity) {
                        return $entity->getName();
                    },
                    'multiple' => true,
                    'expanded' => true,
                ))
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Intercommunal::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_intercommunal';
    }
}
