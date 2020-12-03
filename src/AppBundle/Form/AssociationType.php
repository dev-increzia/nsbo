<?php

namespace AppBundle\Form;

use AppBundle\Entity\Community;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Form\ImageType;
use AppBundle\Entity\Association;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class AssociationType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $community = $options['community'];

        $builder->add('name', TextType::class)
            ->add('category', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'choice_label' => 'name',
                'placeholder' => 'Choisir',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'query_builder' => function (CategoryRepository $repo) use ($community) {
                    return $repo->findCatAssociationByCommunity($community);
                },
            ))
            ->add('city', TextType::class, array('attr' => ['data-select-city' => 'true'], 'required' => true, 'mapped' => false))
            ->add('address', TextType::class, array('required' => false))
            ->add('description', TextareaType::class, array('required' => false))
            ->add('phone', TextType::class, array('required' => false))
            ->add('email', EmailType::class)
            ->add('image', ImageType::class, array('required' => false))
            ->add('timing', TextareaType::class, array('required' => false))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('privateContent', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('moderate', ChoiceType::class, array(
                'choices' => array(
                    'Accepter communauté' => 'accepted',
                    'Refuser communauté' => 'refuse',
                ),
                'expanded' => true,
                'multiple' => false,
                'required' => true,
            ))
            ->add('suAdminEmail', EmailType::class, array(
                'required' => true,
                'mapped' => false
            ))

            ->add('save', SubmitType::class);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $association = $event->getData();
            if (null === $association) {
                return;
            }
            if (null !== $association->getId() && $association->getModerate() != 'wait') {
                $event->getForm()->remove('moderate');
            } elseif ($association->getId() == null) {
                $event->getForm()->remove('moderate');
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Association::class,
            'community' => Community::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_association';
    }
}
