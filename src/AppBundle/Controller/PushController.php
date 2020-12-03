<?php

namespace AppBundle\Controller;

use AppBundle\Repository\PushRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Form\PushFilterType;
use AppBundle\Form\PushCityhallFilterType;
use AppBundle\Form\PushType;
use AppBundle\Entity\Push;

class PushController extends Controller
{
    public function indexAction()
    {
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PushFilterType::class, null, array(
            'cityhall' => $cityhall
        ));
        return $this->render('AppBundle:Push:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function indexGridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $start = $request->get('start');
        $length = $request->get('length');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('sendAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('sendAt' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        //$order = array('parent' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '3') {
                        //$order = array('author' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '4') {
                        //$order = array('category' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '5') {
                        //$order = array('event' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '6') {
                        $order = array('content' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '7') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var PushRepository $pushRepository */
        $pushRepository = $em->getRepository('AppBundle:Push');

        $entities = $pushRepository->search($page, $order, $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('category'), 'event', $request->get('eventType'), $request->get('event'));
        $countEntities = intval($pushRepository->count($cityhall, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('category'), 'event', $request->get('eventType'), $request->get('event')));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        foreach ($entities as $entity) {
            $parent = null;
            if ($entity->getEvent() && $entity->getEvent()->getType() == 'association') {
                $parent = $entity->getEvent()->getAssociation();
            }

            $output['data'][] = [
                'id' => $entity->getId(),
                'sendAt' => $entity->getSendAt() ? $entity->getSendAt()->format('d/m/Y H:i') : '',
                'parent' => $parent ? $parent->getName() : null,
                'author' => $entity->getCreateBy() ? $entity->getCreateBy()->getLastname() . ' ' . $entity->getCreateBy()->getFirstname() : '',
                'category' => $parent && $parent->getCategory() ? $parent->getCategory()->getName() : null,
                'event' => $entity->getEvent() ? $entity->getEvent()->getTitle() : '',
                'content' => $entity->getContent(),
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_push_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_push_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Push $entity */
        $entity = $em->getRepository('AppBundle:Push')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Push entity.');
        }


        //check
        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PushType::class, $entity);
        $form->get('dateAt')->setData($entity->getSendAt());
        $form->get('hourAt')->setData($entity->getSendAt()->format('H:i'));
        if ($form->handleRequest($request)->isValid()) {
            $entity->setUpdateBy($this->getUser());

            $dateAt = $form['dateAt']->getData();
            $hourAt = explode(':', $form['hourAt']->getData());
            $date = $dateAt->setTime($hourAt[0], $hourAt[1]);
            $entity->setSendAt($date);
            //todo mail ?

            if ($entity->getType() == 'event' && $entity->getEvent()) {
                $entity->getEvent()->setUpdateBy($this->getUser());
            }


            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Push modifié avec succès");
            return $this->redirect($this->generateUrl('app_push'));
        }

        return $this->render('AppBundle:Push:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Push $entity */
        $entity = $em->getRepository('AppBundle:Push')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Push entity.');
        }

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }
        if ($entity->getType() == 'event' && $entity->getEvent()) {
            $entity->getEvent()->setPush(null);
        }
        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Push supprimé avec succès");

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl('app_push'));
    }

    public function cityhallAction()
    {

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PushCityhallFilterType::class);
        return $this->render('AppBundle:Push:cityhall.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function cityhallGridAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $start = $request->get('start');
        $length = $request->get('length');
        $page = ($start != 0) ? $start / $length : 0;
        $orders = $request->get('order');
        $order = array('sendAt' => 'DESC');
        if (is_array($orders)) {
            foreach ($orders as $v) {
                if (isset($v['column']) && isset($v['dir'])) {
                    if ($v['column'] == '0') {
                        $order = array('id' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '1') {
                        $order = array('sendAt' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '2') {
                        //$order = array('author' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '3') {
                        $order = array('content' => strtoupper($v['dir']));
                    } elseif ($v['column'] == '4') {
                        $order = array('createAt' => strtoupper($v['dir']));
                    }
                }
            }
        }
        $cityhall = $this->container->get('session.community')->getCommunity();


        /** @var PushRepository $pushRepository */
        $pushRepository = $em->getRepository('AppBundle:Push');

        $entities = $pushRepository->search($page, $order, $cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, 'community', null);
        $countEntities = intval($pushRepository->count($cityhall, $request->get('dateBefore'), $request->get('dateAfter'), null, 'community', null));
        $output = array(
            'data' => array(),
            'recordsFiltered' => $countEntities,
            'recordsTotal' => $countEntities
        );

        $outputScreen = $this->container->get('outputScreen');
        foreach ($entities as $entity) {
            $output['data'][] = [
                'id' => $entity->getId(),
                'sendAt' => $entity->getSendAt() ? $entity->getSendAt()->format('d/m/Y H:i') : '',
                'author' => $entity->getCreateBy() ? $entity->getCreateBy()->getLastname() . ' ' . $entity->getCreateBy()->getFirstname() : '',
                'content' => $entity->getContent(),
                'createAt' => $entity->getCreateAt() ? $entity->getCreateAt()->format('d/m/Y H:i') : '',
                'updateAt' => $entity->getUpdateAt() ? $entity->getUpdateAt()->format('d/m/Y H:i') : '',
                'actions' => $outputScreen->outPutUpdate($this->generateUrl('app_push_cityhall_update', array('id' => $entity->getId())))
                    . $outputScreen->outPutDelete($this->generateUrl('app_push_cityhall_delete', array('id' => $entity->getId()))),
            ];
        }

        return new JsonResponse($output);
    }

    public function addCityhallAction(Request $request)
    {
        $entity = new Push();
        $entity->setType('community');

        $community = $this->container->get('session.community')->getCommunity(true);
        $entity->setCommunity($community);

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PushType::class, $entity);
        if ($form->handleRequest($request)->isValid()) {
            $entity->setCreateBy($this->getUser());
            $entity->setUpdateBy($this->getUser());
            $dateAt = $form['dateAt']->getData();
            $hourAt = explode(':', $form['hourAt']->getData());
            $date = $dateAt->setTime($hourAt[0], $hourAt[1]);
            $entity->setSendAt($date);
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Push communauté ajouté avec succès");
            return $this->redirect($this->generateUrl('app_push_cityhall'));
        }

        return $this->render('AppBundle:Push:addCityhall.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function updateCityhallAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Push $entity */
        $entity = $em->getRepository('AppBundle:Push')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Push entity.');
        }


        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(PushType::class, $entity);
        $form->get('dateAt')->setData($entity->getSendAt());
        $form->get('hourAt')->setData($entity->getSendAt()->format('H:i'));
        if ($form->handleRequest($request)->isValid()) {
            $entity->setUpdateBy($this->getUser());

            $dateAt = $form['dateAt']->getData();
            $hourAt = explode(':', $form['hourAt']->getData());
            $date = $dateAt->setTime($hourAt[0], $hourAt[1]);
            $entity->setSendAt($date);


            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Push communauté modifié avec succès");
            return $this->redirect($this->generateUrl('app_push_cityhall'));
        }

        return $this->render('AppBundle:Push:updateCityhall.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
        ));
    }

    public function deleteCityhallAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Push $entity */
        $entity = $em->getRepository('AppBundle:Push')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Push entity.');
        }

        if (false === $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($entity->getCommunity() != $this->container->get('session.community')->getCommunity()) {
                throw new AccessDeniedException();
            }
        }

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Push communauté supprimé avec succès");

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirect($this->generateUrl('app_push_cityhall'));
    }
}
