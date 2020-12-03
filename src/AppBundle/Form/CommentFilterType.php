<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Repository\MerchantRepository;
use AppBundle\Repository\AssociationRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\ArticleRepository;

class CommentFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityhall = $options['cityhall'];
        $builder->add('search', TextType::class, array('required' => false))
                ->add('type', ChoiceType::class, array(
                    'choices' => array(
                        'Tous' => '',
                        'Evènements' => 'event',
                        'Articles' => 'article',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false
                ))
                ->add('event', EntityType::class, array(
                    'class' => 'AppBundle:Event',
                    'choice_label' => 'title',
                    'placeholder' => 'Tous les évènements',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (EventRepository $repo) use ($cityhall) {
                        return $repo->findAllByCommunity($cityhall);
                    },
                ))
                ->add('article', EntityType::class, array(
                    'class' => 'AppBundle:Article',
                    'choice_label' => 'title',
                    'placeholder' => 'Tous les articles',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (ArticleRepository $repo) use ($cityhall) {
                        return $repo->findAllByCommunity($cityhall);
                    },
                ))
                ->add('role', ChoiceType::class, array(
                    'choices' => array(
                        'Tous les rôles' => '',
                        'Asso super-admin' => 'associationSuAdmin',
                        'Asso admin' => 'associationAdmin',
                        'Commerçant super-admin' => 'merchantSuAdmin',
                        'Commerçant admin' => 'merchantAdmin',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false
                ))
                ->add('association', EntityType::class, array(
                    'class' => 'AppBundle:Association',
                    'choice_label' => 'name',
                    'placeholder' => 'Toutes les associations',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (AssociationRepository $repo) use ($cityhall) {
                        return $repo->findAllByCommunity($cityhall);
                    },
                ))
                ->add('merchant', EntityType::class, array(
                    'class' => 'AppBundle:Merchant',
                    'choice_label' => 'name',
                    'placeholder' => 'Tous les commerçants',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (MerchantRepository $repo) use ($cityhall) {
                        return $repo->findAllByCommunity($cityhall);
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
            'cityhall' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_comment_filter';
    }
}
