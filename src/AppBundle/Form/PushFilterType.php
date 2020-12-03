<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Repository\EventRepository;

class PushFilterType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $cityhall = $options['cityhall'];
        $builder->add('dateBefore', TextType::class, array('required' => false))
                ->add('dateAfter', TextType::class, array('required' => false))
                ->add('eventType', ChoiceType::class, array(
                    'choices' => array(
                        'Tous' => '',
                        'Evénement "Association"' => 'association',
                        'Evénement "Commerçant"' => 'merchant',
                        'Evénement "Communauté"' => 'cityhall',
                    ),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
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
                ->add('category', EntityType::class, array(
                    'class' => 'AppBundle:Category',
                    'choice_label' => 'name',
                    'placeholder' => 'Toutes',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
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
        return 'appbundle_push_filter';
    }
}
