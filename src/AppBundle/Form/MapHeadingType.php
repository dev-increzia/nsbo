<?php

namespace AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\MapHeading;
use AppBundle\Form\ImageType;

class MapHeadingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mapHeading=$options['mapHeading'];
        $builder->add('title', TextType::class)
                ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                ))
            ->add('interestCategories', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, array(
                    'class' => 'AppBundle:InterestCategory',
                    'attr' => ['data-select' => 'true'],
                    'placeholder' => 'Choisir catégorie',
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false,
                    'mapped' => true,
                    'query_builder' => function (\AppBundle\Repository\InterestCategoryRepository $repo) use ($mapHeading){
                                    return $repo->findAllwithoutHeading($mapHeading);
                                },
                            ))
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MapHeading::class,
            'mapHeading'=>false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_mapheading';
    }
}
