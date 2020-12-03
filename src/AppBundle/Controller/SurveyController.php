<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\Survey;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\SurveyQuestionChoice;
use AppBundle\Entity\SurveyResponse;
use AppBundle\Form\SurveyType;
use Doctrine\Common\Util\Debug;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SurveyController extends Controller
{

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Survey:no_access.html.twig');
        }

        if ((!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->container->get('security.authorization_checker')->isGranted('ROLE_COMMUNITY_SU_ADMIN')) ) {
            if(!$this->getUser()->isCommunityAdmin($community)){
                $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
                return $this->redirect($this->generateUrl('app_homepage'));
            }

        }


        return $this->render('AppBundle:Survey:index.html.twig');
    }

    public function indexGridAction(Request $request)
    {
        $community = $this->container->get('session.community')->getCommunity();

        if ($community === false || $community == null) {
            return new JsonResponse(array(
                'data' => array(),
                'recordsFiltered' => 0,
                'recordsTotal' => 0
            ));
        }

        $em = $this->getDoctrine()->getManager();
        $communityId = $community->getId();
        $start = $request->get('start');
        $length = $request->get('length');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('createAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('name' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }
        $entities = $em->getRepository('AppBundle:Survey')->search($communityId, $page, $order);
        $countEntities = intval($em->getRepository('AppBundle:Survey')->count($communityId));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        /** @var Survey $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'question' => $entity->getQuestions()->first() ? $entity->getQuestions()->first()->getTitle() : '',
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'actions' => ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? $outputScreen->outPutUpdate($this->generateUrl('app_survey_update', array('id' => $entity->getId()))) : '')
                    . $outputScreen->outPutView($this->generateUrl('app_survey_results', array('id' => $entity->getId())))
                    . ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? $outputScreen->outPutDelete($this->generateUrl('app_survey_delete', array('id' => $entity->getId()))) : ''),
            ];
        }

        return new JsonResponse($output);
    }

    public function updateAction(Request $request, $id = null)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $community = $this->getAllowedCommunity();


        if ((!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) && !$community->hasSetting('activate_survey')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }

        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_survey'));

        }

        if ($id) {

            /** @var Survey $entity */
            $entity = $entityManager->getRepository('AppBundle:Survey')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Interest entity.');
            }

            $community = $this->getAllowedCommunity($entity->getCommunity(), true);
            if ($community === false) {
                return $this->redirect($this->generateUrl('app_survey'));
            }


        } else {
            $entity = new Survey();
            $entity->setCreateAt(new \Datetime('now'));
            $entity->setPublicAt(new \Datetime('now'));

            $community = $this->getAllowedCommunity(null, true);
            if ($community === false) {
                return $this->redirect($this->generateUrl('app_survey'));
            }

            $entity->setCommunity($community);
            $entity->setCreateBy($this->getUser());
        }



        $community = $this->getAllowedCommunity($entity->getCommunity(), true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_survey'));
        }

        /** @var Form $formSurvey */
        $formSurvey = $this->get('form.factory')->create(SurveyType::class, $entity);

        if ($formSurvey->handleRequest($request)->isValid()) {
            $entity->setUpdateAt(new \Datetime('now'));
            $entity->setUpdateBy($this->getUser());

            $error = false;
            if (!count($entity->getQuestions())) {
                $error = 'Veuillez sélectionner une question pour votre sondage';
            } else {

                $entity->setTitle($entity->getQuestions()->first()->getTitle());

                /** @var SurveyQuestion $question */
                foreach ($entity->getQuestions() as $question) {
                    if (count($question->getChoices()) < 2) {
                        $error = 'Votre question doit au moins 2 choix';
                    }
                }
            }

            if ($error) {
                $this->get('session')->getFlashBag()->add('danger', $error);
            } else {
                $entityManager->persist($entity);
                $entityManager->flush();
                if ($id) {
                    $this->get('session')->getFlashBag()->add('success', 'Sondage mis à jour avec succès');
                } else {
                    $this->get('session')->getFlashBag()->add('success', 'Sondage ajouté avec succès');
                }
                return $this->redirect($this->generateUrl('app_survey'));
            }

        }

        return $this->render($id ? 'AppBundle:Survey:update.html.twig' : 'AppBundle:Survey:add.html.twig', array(
            'formSurvey' => $formSurvey->createView(),
            'entity' => $entity,
        ));

    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $community = $this->container->get('session.community')->getCommunity();

        if (!$community) {
            return $this->render('AppBundle:Event:no_access.html.twig');
        }
        if ((!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) && !$community->hasSetting('activate_survey')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_homepage'));
        }

        /** @var Survey $entity */
        $entity = $em->getRepository('AppBundle:Survey')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce sondage n'existe plus");
            return $this->redirect($this->generateUrl('app_survey'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_survey'));
        }
        if(!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $this->get('session')->getFlashBag()->add('danger', 'Vous n\'avez pas accès à cette fonctionnalité' );
            return $this->redirect($this->generateUrl('app_survey'));

        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Sondage supprimé avec succès");
        return $this->redirect($this->generateUrl('app_survey'));
    }

    public function resultsAction(Request $request, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();
        /** @var Survey $entity */
        $entity = $entityManager->getRepository('AppBundle:Survey')->find($id);

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return new JsonResponse(array());
        }

        $data = array();
        $questions = $entity->getQuestions();
        /** @var SurveyQuestion $question */
        foreach ($questions as $question) {

            $total = $entityManager->getRepository('AppBundle:SurveyQuestion')->countResponses($question->getId());
            $i = 0;
            /** @var SurveyQuestionChoice $choice */
            foreach ($question->getChoices() as $choice) {
                $i++;
                $data[$question->getId()][$i] = array(
                    'count' => count($choice->getResponses()) && $total ? round(count($choice->getResponses()) / $total * 100, 2) : 0,
                    'choice' => $choice
                );
            }

        }

        return $this->render('AppBundle:Survey:results.html.twig', array(
            'entity' => $entity,
            'data' => $data
        ));

    }

    public function resultsGridAction(Request $request, $id)
    {

        if (!$this->isAllowed()) {
            return new JsonResponse(array(
                'data' => array(),
                'recordsFiltered' => 0,
                'recordsTotal' => 0
            ));
        }

        $em = $this->getDoctrine()->getManager();
        $start = $request->get('start');
        $length = $request->get('length');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('id' => 'ASC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('c.id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('cu.firstname' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        $order = array('cu.lastname' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '3') {
                        $order = array('cq.title' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '4') {
                        $order = array('cc.title' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '5') {
                        $order = array('cs.create_at' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '6') {
                        //$order = array('cu.last_name' => strtoupper($v['dir']));
                    }

                }
            }
        }

        $entities = $em->getRepository('AppBundle:SurveyResponse')->search($id, $page, $order);
        $countEntities = intval($em->getRepository('AppBundle:SurveyResponse')->count($id));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        /** @var SurveyResponse $entity */
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'first_name' => $entity->getUser()->getFirstname(),
                'last_name' => $entity->getUser()->getLastname(),
                'question' => $entity->getResponse()->getQuestion()->getTitle(),
                'response' => $entity->getResponse()->getTitle(),
                'created_at' => $entity->getResponse()->getQuestion()->getSurvey()->getCreateAt() ? $entity->getResponse()->getQuestion()->getSurvey()->getCreateAt()->format('d/m/Y H:i') : '',
                'response_at' => $entity->getAddedAt() ? $entity->getAddedAt()->format('d/m/Y H:i') : ''
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        return $community && $community->hasSetting('activate_survey') && ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') || $community->getCommunitySuadmins()->contains($this->getUser()));
    }

    /**
     * @param Community|null $entityCommunity
     * @param bool $communityRequired
     * @return Community|bool
     */
    protected function getAllowedCommunity(Community $entityCommunity = null, $communityRequired = false)
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();

        if (!$community && $communityRequired) {
            $this->get('session')->getFlashBag()->add('danger', "Vous devez sélectionner une communauté afin d'accéder à cette page");
            return false;
        }

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') === false) {
            if ($entityCommunity && $entityCommunity !== $community) {
                $this->get('session')->getFlashBag()->add('danger', "Vous n'avez pas accès à cette page");
                return false;
            }
        }

        return !$community || $community && $community->hasSetting('activate_survey') ? $community : false;
    }

}