<?php

namespace AppBundle\Form;

use AppBundle\Entity\Community;
use AppBundle\Repository\InterestCategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterestFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $community = $options['community'];

        $builder->add('dateBefore', TextType::class, array('required' => false))
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
                ->add('category', EntityType::class, array(
                    'class' => 'AppBundle:InterestCategory',
                    'choice_label' => 'name',
                    'placeholder' => 'Toutes',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (InterestCategoryRepository $repo) use ($community) {
                        $qb = $repo->createQueryBuilder('a')
                            ->innerJoin('a.mapHeading', 'm');

                        if ($community instanceof Community)
                            $qb->where('m.community = :community')->setParameter('community', $community);

                        return $qb;
                    }
                ))
                ->add('save', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'community' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_interest_filter';
    }
}
