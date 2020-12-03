<?php

namespace AppBundle\Form;

use AppBundle\Entity\CategoryPhoneBookHeading;
use AppBundle\Entity\PhoneBookHeading;
use AppBundle\Repository\CategoryPhoneBookHeadingRepository;
use AppBundle\Repository\PhoneBookHeadingRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Number;

class NumberType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->community = $options['community'];
        $builder->add('title', TextType::class)
                ->add('document', DocumentType::class, array('required' => false))
                ->add('description', TextareaType::class, array('required' => false))
                ->add('address', TextType::class)
                ->add('phone', TextareaType::class)


                ->add('save', SubmitType::class);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, PhoneBookHeading $phoneBookHeading = null) {
        // 4. Add the province element
        $community= $this->community;
        $form->add('phoneBookHeading', EntityType::class, array(
            'required' => true,
            'data' => $phoneBookHeading,
            'placeholder' => 'Selectionner une rubrique',
            'class' => 'AppBundle:PhoneBookHeading',
            'query_builder' => function (PhoneBookHeadingRepository $repo) use ($community) {
                return $repo->findPhoneBookHeadingByCommunity($community);
            },
            'mapped' => false,
        ));

        // Neighborhoods empty, unless there is a selected City (Edit View)
        $categories = array();

        // If there is a city stored in the Person entity, load the neighborhoods of it
        if ($phoneBookHeading) {
            // Fetch Neighborhoods of the City if there's a selected city
            /** @var CategoryPhoneBookHeadingRepository $repoCategory */
            $repoCategory = $this->em->getRepository('AppBundle:CategoryPhoneBookHeading');

            $categories = $repoCategory->createQueryBuilder("q")
                ->where("q.phoneBookHeading = :phoneBookHeadingId")
                ->andWhere('q.name != :com')->setParameter('com','Commerces')
                ->andWhere('q.name != :asso')->setParameter('asso','Associations')
                ->setParameter("phoneBookHeadingId", $phoneBookHeading->getId())
                ->getQuery()
                ->getResult();
        }

        // Add the Neighborhoods field with the properly data
        $form->add('categoryPhoneBookHeading', EntityType::class, array(
            'required' => true,
            'placeholder' => '',
            'class' => 'AppBundle:CategoryPhoneBookHeading',
            'choices' => $categories
        ));
    }

    function onPreSubmit(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        // Search for selected City and convert it into an Entity
        $phoneBookHeading = $this->em->getRepository('AppBundle:PhoneBookHeading')->find($data['phoneBookHeading']);

        $this->addElements($form, $phoneBookHeading);
    }

    function onPreSetData(FormEvent $event) {
        $number = $event->getData();
        $form = $event->getForm();

        // When you create a new person, the City is always empty
        /** @var Number $number */
        if($number->getCategoryPhoneBookHeading()){
            $phoneBookHeading = $number->getCategoryPhoneBookHeading()->getPhoneBookHeading() ? $number->getCategoryPhoneBookHeading()->getPhoneBookHeading() : null;

        }else{
            $phoneBookHeading=null;
        }

        $this->addElements($form, $phoneBookHeading);
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Number::class,
            'em' =>false,
            'community'=>false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_number';
    }
}
