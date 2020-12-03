<?php

namespace AppBundle\Form;

use AppBundle\Entity\Community;
use AppBundle\Entity\MapHeading;
use AppBundle\Repository\MapHeadingRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Interest;
use AppBundle\Form\ImageType;

class InterestType extends AbstractType
{

    /** @var Community */
    private $_community;

    /** @var EntityManager */
    private $em;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->_community = $options['community'];

        $builder->add('title', TextType::class)
            ->add('description', TextareaType::class, array('required' => false))
            ->add('address', TextType::class)
            ->add('longitude', TextType::class, array('required' => false))
            ->add('latitude', TextType::class, array('required' => false))
            ->add('enabled', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('image', ImageType::class, array('required' => false))
            ->add('email', EmailType::class, array('required' => false))
            ->add('phone', TextType::class, array('required' => false))
            ->add('monday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('mondayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('mondayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('tuesday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('tuesdayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('tuesdayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('wednesday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('wednesdayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('wednesdayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('thursday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('thursdayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('thursdayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('friday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('fridayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('fridayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('saturday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('saturdayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('saturdayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('sunday', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('sundayHour', TextType::class, array(
                'required' => false,
            ))
            ->add('sundayHourEnd', TextType::class, array(
                'required' => false,
            ))
            ->add('save', SubmitType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }


    protected function addElements(FormInterface $form, MapHeading $mapheading = null)
    {

        $community = $this->_community;

        // 4. Add the province element
        $form->add('mapHeading', EntityType::class, array(
            'required' => true,
            'data' => $mapheading,
            'placeholder' => 'Selectionner une rubrique',
            'class' => 'AppBundle:MapHeading',
            'mapped' => false,
            'query_builder' => function (MapHeadingRepository $repo) use ($community) {
                return $repo->findAllByCommunity($community);
            }
        ));

        // Neighborhoods empty, unless there is a selected City (Edit View)
        $categories = array();

        // If there is a city stored in the Person entity, load the neighborhoods of it
        if ($mapheading) {
            // Fetch Neighborhoods of the City if there's a selected city
            $repoCategory = $this->em->getRepository('AppBundle:InterestCategory');

            $categories = $repoCategory->createQueryBuilder("q")
                ->where("q.mapHeading = :mapHaedingId")
                ->setParameter("mapHaedingId", $mapheading->getId())
                ->getQuery()
                ->getResult();
        }

        // Add the Neighborhoods field with the properly data
        $form->add('category', EntityType::class, array(
            'required' => true,
            'placeholder' => 'Sélectionnez une rubrique avant de sélectionner une catégorie',
            'class' => 'AppBundle:InterestCategory',
            'choices' => $categories
        ));
    }

    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        // Search for selected City and convert it into an Entity
        $mapHeading = $this->em->getRepository('AppBundle:MapHeading')->find($data['mapHeading']);

        $this->addElements($form, $mapHeading);
    }

    function onPreSetData(FormEvent $event)
    {
        $interest = $event->getData();
        $form = $event->getForm();

        // When you create a new person, the City is always empty
        /** @var Interest $interest */
        if ($interest->getCategory()) {
            $mapHeading = $interest->getCategory()->getMapHeading() ? $interest->getCategory()->getMapHeading() : null;

        } else {
            $mapHeading = null;
        }

        $this->addElements($form, $mapHeading);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Interest::class,
            'community' => false,
            'em' => false
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_interest';
    }
}
