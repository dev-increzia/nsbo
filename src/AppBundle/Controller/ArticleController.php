<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Community;
use AppBundle\Entity\Push;
use AppBundle\Entity\PushLog;
use AppBundle\Repository\ArticleRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ArticleFilterType;
use AppBundle\Form\ArticleType;
use AppBundle\Form\ShareArticleDedicatedPageType;
use AppBundle\Entity\Article;

/**
 * Class ArticleController
 * @package AppBundle\Controller
 */
class ArticleController extends Controller
{
    /**
     * @param null $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($type = null)
    {
        $community = $this->getAllowedCommunity();
        if ($community === false) {
            return $this->render('AppBundle:Article:no_access.html.twig');
        }

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ArticleFilterType::class);
        if ($type) {
            $form->get('type')->setData($type);
        }
        return $this->render('AppBundle:Article:index.html.twig', array(
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
        if ($community === false) {
            return new JsonResponse(array());
        }

        $em = $this->getDoctrine()->getManager();
        $page = (int)$request->get('page');
        $type = $request->get('type') == '' ? false : $request->get('type');

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');

        $entities = $articleRepository->search($page, array('createAt' => 'DESC'), $community, $request->get('dateBefore'), $request->get('dateAfter'), $request->get('title'), $type, $request->get('enabled'));
        $content = $this->renderView('AppBundle:Article:articles.html.twig', array(
            'articles' => $entities,
        ));

        return new JsonResponse(array('content' => $content, 'count' => count($entities)));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request)
    {
        $entity = new Article();

        $community = $this->getAllowedCommunity(null, true);
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_article'));
        }

        $entity->setType('association');
        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? true : false;

        /** @var Form $form */
        $form = $this->get('form.factory')->create(ArticleType::class, $entity, array(
            'isAdmin' => $isAdmin,
            'community' => $community
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {

            $entity->setCreateBy($this->getUser());
            $entity->setUpdateBy($this->getUser());
            $entity->setCommunity($community);
            $entity->setPublicAt(new \DateTime('now'));

            if ($entity->getPushEnabled()) {
                $entity->getPush()->setCommunity($community);
                $entity->getPush()->setArticle($entity);
                $entity->getPush()->setCreateBy($this->getUser());
                $entity->getPush()->setUpdateBy($this->getUser());

                $dateAt = $form['push']['dateAt']->getData();
                $hourAt = explode(':', $form['push']['hourAt']->getData());
                $date = $dateAt->setTime($hourAt[0], $hourAt[1]);
                $entity->getPush()->setSendAt($date);
            } else {
                $entity->setPush(null);
            }
            if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                $association = $entity->getAssociation();
                $merchant = $entity->getMerchant();
                $user = $entity->getUser();
                //reset
                $entity->setAssociation(null);
                $entity->setMerchant(null);
                $entity->setUser(null);

                switch ($entity->getType()) {
                    case 'association':
                        $entity->setAssociation($association);
                        $entity->setCommunity($association->getCommunity());
                        break;
                    case 'merchant':
                        $entity->setMerchant($merchant);
                        $entity->setCommunity($merchant->getCommunity());
                        break;
                    case 'user':
                        $entity->setUser($user);
                        $entity->setCommunity($community);
                        break;
                    default:
                        break;
                }
            }


            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);
            $this->notifyUsers($em, $entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'Article ajouté avec succès');
            return $this->redirect($this->generateUrl('app_article'));
        }

        return $this->render('AppBundle:Article:add.html.twig', array(
            'form' => $form->createView(),
            'entity' => null,
            'community' => $community
        ));
    }

    private function notifyUsers($em, $article)
    {
        $users = $em->getRepository('UserBundle:User')->search(false, array(), null, array('ROLE_CITIZEN'), null, null, null, null, null, null);
        if ($article->getType() == 'merchant') {
            if ($article->getAssociation() && $article->getAssociation()->getEnabled() && $article->getAssociation()->getModerate() == 'accepted') {
                $category = $article->getAssociation()->getCategory() ? $article->getAssociation()->getCategory() : false;
                $merchantUsers = $article->getMerchant()->getUsers();
                foreach ($merchantUsers as $merchantUser) {
                    if($merchantUser->getType() == 'approved') {
                        $user = $merchantUser->getUser();
                        $joinedMerchants = $em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
                        if (in_array($article->getMerchant(), $joinedMerchants)) {
                            $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'articleCounter', false,$article);
                            $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter', false, $article);
                        }
                    }
                }
            }
        } else if ($article->getType() == 'article' || $article->getType() == 'association') {
            if ($article && $article->getEnabled()) {
                if ($article->getAssociation() && $article->getAssociation()->getEnabled() && $article->getAssociation()->getModerate() == 'accepted') {
                    foreach ($users as $user) {
                        if ($article->getPrivate()) {
                            $joinedAssociations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
                            if (in_array($article->getAssociation(), $joinedAssociations)) {
                                $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'articleCounter', false,$article);
                                $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter', false, $article);
                            }
                        } else {
                            $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
                            if (in_array($article->getCommunity(), $followedCommunities)) {
                                $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'articleCounter', false,$article);
                                $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter', false, $article);
                            }
                        }
                    }
                }
            }
        }

    }


    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function viewAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            return new JsonResponse(array());
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return new JsonResponse(array());
        }

        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Article:view.html.twig', array(
                'entity' => $entity,
            ));

            return new JsonResponse(array('content' => $content));
        } else {
            throw $this->createNotFoundException('');
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_article'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_article'));
        }

        if ($entity->getType() == 'user') {
            $categories = $entity->getCategories();
            foreach ($categories as $category) {
                $entity->setCategory($category);
                break;
            }
        }

        /* Route inexistante =>
         * TODO : Delete
        if ($entity->getType() == 'cityhall') {
            return $this->redirect($this->generateUrl('app_project_update', array('id' => $entity->getId())));
        }
        */

        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN') ? true : false;

        $form = $this->get('form.factory')->create(ArticleType::class, $entity, array(
            'isAdmin' => $isAdmin,
            'community' => $entity->getCommunity()
        ));

        if ($entity->getPushEnabled() && $entity->getPush() && $entity->getPush()->getSendAt()) {
            $form->get('push')['dateAt']->setData($entity->getPush()->getSendAt());
            $form->get('push')['hourAt']->setData($entity->getPush()->getSendAt()->format('H:i'));
        }
        $enabled = $entity->getEnabled();

        /** @var Form $form */
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $city = $em->getRepository('AppBundle:City')->findOneById($form->get('city')->getData());
            $entity->setCity($city);
        }
        if ($form->isValid()) {

            $entity->setUpdateBy($this->getUser());

            if ($enabled != $entity->getEnabled()) {
                $this->_activate($entity);
            }

            if ($entity->getPushEnabled()) {
                $entity->getPush()->setCommunity($entity->getCommunity());
                $entity->getPush()->setArticle($entity);
                $entity->getPush()->setUpdateBy($this->getUser());

                $dateAt = $form['push']['dateAt']->getData();
                $hourAt = explode(':', $form['push']['hourAt']->getData());
                $date = $dateAt->setTime($hourAt[0], $hourAt[1]);

                $entity->getPush()->setSendAt($date);
            } else {
                if ($entity->getPush()) {
                    $em->remove($entity->getPush());
                }
                $entity->setPush(null);
            }
            //mail update
            $content = $this->renderView('AppBundle:Mail:updateArticle.html.twig', array(
                'entity' => $entity,
                'sender' => $this->getUser(),
            ));

            if ($entity->getCreateBy()) {
                $this->container->get('mail')->updateArticle($entity->getCreateBy(), $content, $entity);
            }

            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Article modifié avec succès");
            return $this->redirect($this->generateUrl('app_article'));
        }

        return $this->render('AppBundle:Article:update.html.twig', array(
            'form' => $form->createView(),
            'entity' => $entity,
            'community' => $entity->getCommunity()
        ));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_article'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_article'));
        }

        /* Route inexistante =>
         * TODO : Delete
        if ($entity->getType() == 'cityhall') {
            return $this->redirect($this->generateUrl('app_delete_update', array('id' => $entity->getId())));
        }
        */

        $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', "Article supprimé avec succès");
        return $this->redirect($this->generateUrl('app_article'));
    }

    /**
     * @param $articleId
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function shareDedicatedPageAction($articleId, Request $request) {
        $em = $this->getDoctrine()->getManager();
        /** @var Article $article */
        $article = $em->getRepository('AppBundle:Article')->findOneById($articleId);
        if (!$article) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe pas");
            return $this->redirect($this->generateUrl('app_article'));
        }
        /** @var Form $form */
        $form = $this->get('form.factory')->create(ShareArticleDedicatedPageType::class, null, array(
            'user' => $this->getUser(),
        ));
        if ($request->isXmlHttpRequest()) {
            $content = $this->renderView('AppBundle:Article:share_dedicated_page.html.twig', array(
                'article' => $article,
                'form' => $form->createView(),
            ));
            return new JsonResponse(array('content' => $content));
        } else {
            $referer = $request->headers->get('referer');
            if ($request->isMethod('POST')) {
                if ($form->handleRequest($request)->isValid()) {
                    if ($form->get('articleHeading')->getData()) {
                        $articleHeading = $form->get('articleHeading')->getData();
                        $entity = new Article();
                        if( $article->getImages()) {
                            $imagesCounter = 0;
                            foreach ($article->getImages() as $image) {
                                if (is_object($image)) {
                                    $imagesCounter++;
                                    $image = (array)$image;
                                    $imageId = $image['id'];
                                    $img = $this->em->getRepository('AppBundle:File')->findOneById($imageId);
                                    $path = __DIR__.'/../../../public/upload/'.$img->getFilename();
                                    $path = str_replace(" ", "\ ", $path);
                                    $pictureType = mime_content_type($path);
                                    $imgData = file_get_contents($path);
                                    $base64 = 'data:'.$pictureType. ';base64,' . base64_encode($imgData);
                                    $currentImage = new File();
                                    $currentImage->base64($base64);
                                    $entity->addImage($currentImage);
                                }
                            }
                        }
                        $entity ->setEnabled($article->getEnabled())
                                ->setState($article->getState())
                                ->setCreateBy($this->getUser())
                                ->setUser($this->getUser())
                                ->setParent($article)
                                ->setPublishing($article->getPublishing())
                                ->setPrivate($article->getPrivate())
                                ->setCommunity($articleHeading->getCommunity())
                                ->setArticleHeading($articleHeading)
                                ->setDescription($article->getDescription())
                                ->setAssociation($article->getAssociation())
                                ->setMerchant($article->getMerchant())
                                ->setPublicAt(new \DateTime('now'))
                                ->setCity($article->getCity())
                                ->setType($article->getType())
                                ->setTitle($article->getTitle());
                        $image = $article->getImage();
                        if (is_object($image)) {
                            $path = __DIR__.'/../../../public/upload/'.$image->getFilename();
                            $path = str_replace(" ", "\ ", $path);
                            $pictureType = mime_content_type($path);
                            $imgData = file_get_contents($path);
                            $base64 = 'data:'.$pictureType. ';base64,' . base64_encode($imgData);
                            $currentImage = new File();
                            $currentImage->base64($base64);
                            $entity->setImage($currentImage);
                        }
                        $video = $article->getVideo();
                        if (is_object($video)) {
                            $path = __DIR__.'/../../../public/upload/'.$video->getFilename();
                            $path = str_replace(" ", "\ ", $path);
                            $pictureType = mime_content_type($path);
                            $imgData = file_get_contents($path);
                            $base64 = 'data:'.$pictureType. ';base64,' . base64_encode($imgData);
                            $currentVideo = new File();
                            $currentVideo->base64($base64);
                            $entity->setVideo($currentVideo);
                        }
                        $document = $article->getDocument();
                        if (is_object($document)) {
                            $path = __DIR__.'/../../../public/upload/'.$document->getFilename();
                            $path = str_replace(" ", "\ ", $path);
                            $pictureType = mime_content_type($path);
                            $imgData = file_get_contents($path);
                            $base64 = 'data:'.$pictureType. ';base64,' . base64_encode($imgData);
                            $currentDocument = new File();
                            $currentDocument->base64($base64);
                            $entity->setDocument($currentDocument);
                        }
                        foreach ($article->getCategories() as $category) {
                            $entity->addCategory($category);
                        }
                        $em->persist($entity);
                        try {
                            $em->flush();
                            $this->get('session')->getFlashBag()->add('success', "Article partagé avec succès");
                    
                        } catch(\Exception $exception) {
                            $this->get('session')->getFlashBag()->add('danger', "Erreur lors de mises à jour dans la base de données");
                            if ($referer) {
                                return $this->redirect($referer);
                            }
                            return $this->redirect($this->generateUrl('app_article'));
                        }
                    }
                }
                if ($referer) {
                    return $this->redirect($referer);
                }
                return $this->redirect($this->generateUrl('app_article'));
            } else {
                $this->get('session')->getFlashBag()->add('danger', "Une erreur est survenue");
                if ($referer) {
                    return $this->redirect($referer);
                }
                return $this->redirect($this->generateUrl('app_article'));
            } 
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Article $entity */
        $entity = $em->getRepository('AppBundle:Article')->find($id);
        if (!$entity) {
            $this->get('session')->getFlashBag()->add('danger', "Cet article n'existe plus");
            return $this->redirect($this->generateUrl('app_article'));
        }

        $community = $this->getAllowedCommunity($entity->getCommunity());
        if ($community === false) {
            return $this->redirect($this->generateUrl('app_article'));
        }

        if ($entity->getType() == 'cityhall') {
            return $this->redirect($this->generateUrl('app_project_activate', array('id' => $entity->getId())));
        }

        $enabled = $entity->getEnabled();
        $entity->setEnabled($enabled ? false : true);
        $this->_activate($entity);
        $em->flush();

        if ($enabled) {
            $this->get('session')->getFlashBag()->add('success', "Article désactivé avec succès");
        } else {
            $this->get('session')->getFlashBag()->add('success', "Article activé avec succès");
        }

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        return $this->redirect($this->generateUrl('app_article'));
    }

    /**
     * @param Article $entity
     */
    private function _activate($entity)
    {
        $content = $this->renderView('AppBundle:Mail:enableArticle.html.twig', array(
            'entity' => $entity,
            'sender' => $this->getUser(),
        ));
        if ($entity->getCreateBy()) {
            $this->container->get('mail')->enableArticle($entity->getCreateBy(), $content, $entity->getEnabled());
        }

        $message = "Votre article " . $entity->getTitle() . ' a été ' . ($entity->getEnabled() ? 'activé' : 'désactivé') . '';
        $this->container->get('notification')->notify($entity->getCreateBy(), 'article', $message, false, $entity);
        $this->container->get('mobile')->pushNotification($entity->getCreateBy(), 'NOUS-ENSEMBLE ', "$message", false, false, 'on');
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
