<?php

namespace AppBundle\Form;

use AppBundle\Repository\CategoryRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
use AppBundle\Entity\Article;
use UserBundle\Repository\UserRepository;
use AppBundle\Repository\AssociationRepository;

class ArticleType extends AbstractType
{
    protected $_isAdmin = false;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->_isAdmin = $options['isAdmin'];
        $this->_community = $options['community'];

        $community = $options['community'];
        $builder->add('title', TextType::class)
            ->add('image', ImageType::class, array('required' => false))
            ->add('description', TextareaType::class, array('required' => false))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('pushEnabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('push', ArticlePushType::class, array('required' => false))
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
            ->add('city', TextType::class, array('attr' => ['data-select-city' => 'true'], 'required' => true, 'mapped' => false))
            ->add('save', SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $article = $event->getData();
            if (null === $article) {
                return;
            }
            if (null !== $article->getId()) {
                /*if ($article->getType() !== 'user') {
                    $event->getForm()->remove('category');
                    $event->getForm()->remove('city');
                }*/
            } else {
                if ($this->_isAdmin) {
                    $community = $this->_community;
                    $event->getForm()->add('type', ChoiceType::class, array(
                        'choices' => array(
                            'Article Groupe / Association' => 'association',
                            'Article Individu' => 'user',
                        ),
                        'expanded' => true,
                        'multiple' => false,
                        'required' => true,
                        'mapped' => true,
                    ))
                        ->add('association', EntityType::class, array(
                            'class' => 'AppBundle:Association',
                            'choice_label' => 'name',
                            'placeholder' => 'Choisir le groupe/l’association',
                            'multiple' => false,
                            'expanded' => false,
                            'required' => false,
                            'query_builder' => function (AssociationRepository $repo) use ($community) {
                                return $repo->findAllByCommunity($community);
                            },
                        ))
                        ->add('user', EntityType::class, array(
                            'class' => 'UserBundle:User',
                            'choice_label' => function ($entity) {
                                return $entity->getLastname() . ' ' . $entity->getFirstname() . ' (' . $entity->getRoleText() . ')';
                            },
                            'query_builder' => function (UserRepository $repo) use ($community) {
                                return $repo->findAllCitizensByCommunity($community);
                            },
                            'placeholder' => 'Choisir un citoyen',
                            'multiple' => false,
                            'expanded' => false,
                            'required' => false,
                        ));
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
            'data_class' => Article::class,
            'isAdmin' => false,
            'community' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_article';
    }
}
