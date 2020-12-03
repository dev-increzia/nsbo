<?php

namespace AppBundle\Form;

use AppBundle\Entity\ReportingObjectHeading;
use AppBundle\Repository\InterestCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\MapHeading;
use AppBundle\Form\ImageType;

class ReportingObjectHeadingType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('objet', TextType::class)
                ->add('recipient', TextType::class,array('label'=>'adresse email du contact'));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ReportingObjectHeading::class
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_mapheading';
    }
}
