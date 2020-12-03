<?php
/**
 * Created by PhpStorm.
 * User: medamine.ab
 * Date: 04/12/2018
 * Time: 16:19
 */

namespace AppBundle\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\User;
use UserBundle\Repository\UserRepository;

class AddSuAdminCommunityType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $community= $options['community'];

        $builder->add('user', EntityType::class, array(
                'class' => 'UserBundle:User',
                'choice_label' => 'email',
                'placeholder' => 'Choisir un citoyen',
                'multiple' => false,
                'expanded' => false,
                'required' => true,
                'mapped' => false,
                'query_builder' => function (UserRepository $repo) use ($community) {
                    return $repo->findAllSuAdmins($community);
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


            'community' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_add_user_cityhall';
    }

}