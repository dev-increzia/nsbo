<?php

namespace AppBundle\Form;

use AppBundle\Entity\Community;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AppBundle\Form\ImageType;
use AppBundle\Form\EventPushType;
use AppBundle\Entity\Event;
use AppBundle\Repository\MerchantRepository;
use AppBundle\Repository\AssociationRepository;

class EventType extends AbstractType
{
    protected $_isAdmin = false;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->_isAdmin = $options['isAdmin'];
        $this->_cityhall = $options['cityhall'];

        $community = $options['cityhall'];

        $builder->add('title', TextType::class)
            ->add('place', TextType::class)
            ->add('startAt', DateTimeType::class, array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
            ))
            ->add('endAt', DateTimeType::class, array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy HH:mm',
            ))
            ->add('image', ImageType::class, array('required' => false))
            ->add('description', TextareaType::class, array('required' => false))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))

            ->add('categories', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'attr' => ['data-select' => 'true'],
                'placeholder' => 'Choisir des thèmes',
                'multiple' => true,
                'expanded' => false,
                'required' => true,
                'query_builder' => function (CategoryRepository $repo) use ($community) {
                    return $repo->findCatAssociationByCommunity($community);
                }
            ))
            ->add('pushEnabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('push', EventPushType::class, array('required' => false))
            ->add('city', TextType::class, array('attr' => ['data-select-city' => 'true'], 'required' => true, 'mapped' => false))
            ->add('save', SubmitType::class);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            if (null === $entity) {
                return;
            }
            if (null !== $entity->getId()) {
                if ($entity->getModerate() != 'wait') {
                    //$event->getForm()->remove('moderate');
                }
            } else {
                //$event->getForm()->remove('moderate');
                if ($this->_isAdmin) {
                    /** @var Community $cityhall */
                    $cityhall = $this->_cityhall;
                    if($cityhall->hasSetting('activate_articles')) {
                        $event->getForm()->add('type', ChoiceType::class, array(
                            'choices' => array(
                                'Evénement de groupe/association' => 'association',
                                'Evénement d’une communauté' => '',
                            ),
                            'expanded' => true,
                            'multiple' => false,
                            'required' => false,
                            'mapped' => true,
                        ))
                            ->add('association', EntityType::class, array(
                                'class' => 'AppBundle:Association',
                                'choice_label' => 'name',
                                'placeholder' => 'Choisir le groupe/l’association',
                                'multiple' => false,
                                'expanded' => false,
                                'required' => false,
                                'query_builder' => function (AssociationRepository $repo) use ($cityhall) {
                                    return $repo->findAllByCommunity($cityhall);
                                },
                            ));
                    }else{
                        $event->getForm()->add('type', HiddenType::class, array(
                            'data'=>'association',
                            'label' => false
                        ))
                        ->add('association', EntityType::class, array(
                                'class' => 'AppBundle:Association',
                                'choice_label' => 'name',
                                'placeholder' => 'Choisir le groupe/l’association',
                                'multiple' => false,
                                'expanded' => false,
                                'required' => false,
                                'query_builder' => function (AssociationRepository $repo) use ($cityhall) {
                                    return $repo->findAllByCommunity($cityhall);
                                },
                            ));
                    }

                }
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Event::class,
            'isAdmin' => false,
            'cityhall' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_event';
    }
}
