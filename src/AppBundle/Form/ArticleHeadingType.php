<?php

namespace AppBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\ArticleHeading;
use AppBundle\Form\ImageType;
use UserBundle\Repository\UserRepository;

class ArticleHeadingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $community = $options['community'];
        $this->_isUpdate=$options['update'];
        $builder->add('title', TextType::class)
                //->add('emailAdmin', TextType::class)
                ->add('enabled', CheckboxType::class, array(
                    'required' => false,
                ))
                ->add('save', SubmitType::class)
                ->add('admins', EntityType::class, array(
                'class' => 'UserBundle:User',
                'attr' => ['data-select' => 'true'],
                'placeholder' => 'Choisir des thèmes',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'query_builder' => function (UserRepository $repo) use ($community) {
                    return $repo->findAdmins($community);
                }
            ));

            $builder->add('saveAndAdd', SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            if($this->_isUpdate)
            {
                $event->getForm()->remove('saveAndAdd');
            }
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ArticleHeading::class,
            'update' => false,
            'community' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_articleheading';
    }
}
