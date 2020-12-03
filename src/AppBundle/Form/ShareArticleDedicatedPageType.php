<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Collections\ArrayCollection;

class ShareArticleDedicatedPageType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $options['user'];
        $adminCommunities = new ArrayCollection(
            array_merge($user->getAdminCommunities()->toArray(),$user->getSuAdminCommunities()->toArray())
        );
        $articleHeading = new ArrayCollection();
        foreach ($adminCommunities as $adminCommunity) {
            $articleHeading =  new ArrayCollection(
                array_merge($articleHeading->toArray(), $adminCommunity->getArticleHeadings()->toArray())
            );
        }
        $builder->add('articleHeading', EntityType::class, array(
            'class' => 'AppBundle:ArticleHeading',
            'choices' => $articleHeading,
            'choice_label' => 'title',
            'placeholder' => 'Grand projet de ville',
            'multiple' => false,
            'expanded' => false,
            'required' => true,
            'mapped' => false,

        ))
        ->add('save', SubmitType::class);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null,
        ));
    }
    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_comment_delete';
    }
}
