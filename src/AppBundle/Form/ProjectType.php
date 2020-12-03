<?php

namespace AppBundle\Form;

use AppBundle\Repository\ArticleHeadingRepository;
use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Article;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProjectType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $community = $options['community'];
        $builder->add('title', TextType::class)
                ->add('images', CollectionType::class, array(
                    'entry_type' => ImageType::class,
                    'entry_options' => array('label' => false),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label'=> false
                ))
                ->add('description', TextareaType::class, array('required' => false))
                ->add('articleHeading', EntityType::class, array(
                    'class' => 'AppBundle:ArticleHeading',
                    'choice_label' => 'title',
                    'placeholder' => 'Publication à garder sur la page dédiée',
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'query_builder' => function (ArticleHeadingRepository $repo) use ($community) {
                        return $repo->findAllArticleHeadingByCommunity($community);
                    },
                ))
                ->add('city', TextType::class, array('attr' => ['data-select-city' => 'true'], 'required' => true, 'mapped' => false))
                ->add('categories', EntityType::class, array(
                'class' => 'AppBundle:Category',
                'attr' => ['data-select' => 'true'],
                'placeholder' => 'Choisir des thèmes',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'query_builder' => function (CategoryRepository $repo) use ($community) {
                    return $repo->findCatAssociationByCommunity($community);
                }
                ))
                ->add('enabled', CheckboxType::class, array(
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
            'data_class' => Article::class,
            'community'=>false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_project';
    }
}
