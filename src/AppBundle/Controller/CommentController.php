<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Community;
use AppBundle\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\CommentFilterType;
use AppBundle\Form\CommentDeleteType;

class CommentController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $community = $this->getAllowedCommunity();
        /*if ($community === false) {
            return $this->render('AppBundle:Comment:no_access.html.twig');
        }*/

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommentFilterType::class, null, array(
            'cityhall' => $community
        ));
        return $this->render('AppBundle:Comment:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexEventAction($id)
    {
        $community = $this->getAllowedCommunity();
        /*if ($community === false) {
            return $this->render('AppBundle:Comment:no_access.html.twig');
        }*/

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommentFilterType::class, null, array(
            'cityhall' => $community
        ));

        $form->get('type')->setData('event');
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('AppBundle:Event')->find($id);
        $form->get('event')->setData($event);
        return $this->render('AppBundle:Comment:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexArticleAction($id)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Comment:no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommentFilterType::class, null, array(
            'cityhall' => $community
        ));
        $form->get('type')->setData('article');
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository('AppBundle:Article')->find($id);
        $form->get('article')->setData($article);
        return $this->render('AppBundle:Comment:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function indexGridAction(Request $request)
    {
        $community = $this->getAllowedCommunity();
        /*if ($community === false) {
            return new JsonResponse(array());
        }*/

        $em = $this->getDoctrine()->getManager();
        $page = (int)$request->get('page');
        $cityhall = $this->container->get('session.community')->getCommunity();

        /** @var CommentRepository $commentRepository */
        $commentRepository = $em->getRepository('AppBundle:Comment');

        $entities = $commentRepository->search($page, array('createAt' => 'DESC'), $cityhall, $request->get('search'), $request->get('type'), $request->get('role'), $request->get('event'), $request->get('article'), $request->get('association'), $request->get('merchant'));
        $content = $this->renderView('AppBundle:Comment:comments.html.twig', array(
            'comments' => $entities,
        ));

        return new JsonResponse(array('content' => $content, 'count' => count($entities)));
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function readAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Comment')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Comment entity.');
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return new JsonResponse(array());
        }

        $user = $this->getUser();

        if (!$user->isReadComment($entity->getId())) {
            $user->addCommentsRead($entity);
            $em->persist($user);
            $em->flush();
        }

        return new JsonResponse(array());
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Comment $entity */
        $entity = $em->getRepository('AppBundle:Comment')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Ce commentaire n'existe plus");
            return $this->redirect($this->generateUrl('app_comment'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_comment'));
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(CommentDeleteType::class);
        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Comment:delete.html.twig', array(
                'comment' => $entity,
                'form' => $form->createView(),
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            $referer = $request->headers->get('referer');
            if ($request->isMethod('POST')) {
                if ($form->handleRequest($request)->isValid()) {
                    if ($form->get('message')->getData()) {
                        //mail
                        $content = $this->renderView('AppBundle:Mail:commentDelete.html.twig', array(
                            'message' => $form->get('message')->getData(),
                        ));
                        $this->container->get('mail')->commentDelete(array($entity->getUser()->getEmail(), $this->getUser()->getEmail()), $content);
                    }

                    $em = $this->getDoctrine()->getManager();
                    $em->remove($entity);
                    $em->flush();


                    $this->get('session')->getFlashBag()->add('success', "Commentaire supprimé avec succès");
                    if ($referer) {
                        return $this->redirect($referer);
                    }

                    return $this->redirect($this->generateUrl('app_comment'));
                } else {
                    $this->get('session')->getFlashBag()->add('danger', "Une erreur est survenue");
                    if ($referer) {
                        return $this->redirect($referer);
                    }
                    return $this->redirect($this->generateUrl('app_comment'));
                }
            } else {
                if ($referer) {
                    return $this->redirect($referer);
                }
                return $this->redirect($this->generateUrl('app_comment'));
            }
        }
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

        return $community;
    }
}
