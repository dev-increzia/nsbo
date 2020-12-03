<?php

namespace AppBundle\Form;

use AppBundle\Entity\MapHeading;
use AppBundle\Repository\MapHeadingRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Work;

class WorkType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $community = $options['community'];

        $builder->add('title', TextType::class)
                ->add('description', TextareaType::class, array('required' => false))
                ->add('address', TextType::class)
                ->add('longitude', TextType::class, array('required' => false))
                ->add('latitude', TextType::class, array('required' => false))
                ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                ))
                ->add('mapHeading', EntityType::class, array(
                    'required' => true,
                    'class' => 'AppBundle:MapHeading',
                    'choice_label' => function (MapHeading $entity) {
                        return $entity->getTitle();
                    },
                    'multiple' => false,
                    'query_builder' => function (MapHeadingRepository $repo) use ($community) {
                        return $repo->findAllByCommunity($community);
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
            'data_class' => Work::class,
            'community' => null
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_work';
    }
}
