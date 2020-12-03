<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use AppBundle\Entity\Article;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializerBuilder;
use AppBundle\Entity\File;
use AppBundle\Entity\Abus;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Comment;

class CommentController extends Controller
{

    /**
     * @ApiDoc(resource="/api/comment/article/{id}/{type}/new",
     * description="Ce webservice permet d'ajouter commentaire.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function addArticleCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        /** @var Article $article */
        $article = $em->getRepository("AppBundle:Article")->find($id);
        $comment = new Comment();
        if ($type == 'merchant') {
            $merchant = $article->getMerchant();
            $comment->setMerchant($merchant);
            $comment->setType($type);
        } elseif ($type == 'association') {
            $association = $article->getAssociation();
            $comment->setAssociation($association);
            $comment->setType($type);
        } else {
            $comment->setUser($user);
            $comment->setType('citizen');
        }

        $comment->setArticle($article);

        if($article->getType() == 'user'){
            $comment->setCommunity($article->getCommunity());
        }elseif($article->getType() == 'association'){
            $comment->setCommunity($article->getAssociation()->getCommunity());
        }elseif($article->getType() == 'merchant'){
            $comment->setCommunity($article->getMerchant()->getCommunity());
        }elseif($article->getType() == 'community'){
            $comment->setCommunity($article->getCommunity());
        }

        $comment->setContent($data["content"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $comment->setImage($image);
        }

        if (isset($data['document']) && $data['document'] != null) {
            $document = new File();
            $document->base64($data['document']);
            $comment->setDocument($document);
        }

        $em->persist($comment);
        $em->flush();
        if ($article->getType() == 'user' && $user != $article->getUser()) {
            $message = $user->getFirstname() . ' ' . $user->getLastname() . " a commenté votre article " . $article->getTitle() . '.';
            $this->container->get('notification')->notify($article->getUser(), 'newComment', $message, false, $comment);
            $this->container->get('mobile')->pushNotification($article->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            if ($article->getCreateBy()) {
                $this->container->get('mobile')->pushNotification($article->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre article " . $article->getTitle() . ' a été commenté.', false, $article->getId());
            }
        }
        
        $comments = $this->get('comment.v3')->commentsArticleDetails($request, $em, $article);
        
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/article/{id}/{type}/new",
     * description="Ce webservice permet d'ajouter commentaire.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function addEventCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $comment = new Comment();
        if ($type == 'merchant') {
            $merchant = $event->getMerchant();
            $comment->setMerchant($merchant);
            $comment->setType($type);
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            $comment->setAssociation($association);
            $comment->setType($type);
        } else {
            $comment->setUser($user);
            $comment->setType('citizen');
        }

        $comment->setUser($user);
        $comment->setEvent($event);
        $comment->setCommunity($event->getCommunity());
        $comment->setContent($data["content"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $comment->setImage($image);
        }
        if (isset($data['document']) && $data['document'] != null) {
            $document = new File();
            $document->base64($data['document']);
            $comment->setDocument($document);
        }
        $em->persist($comment);
        $em->flush();
        if ($event->getCreateBy() && $event->getCreateBy() != $user) {
            $this->container->get('mobile')->pushNotification($event->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre évènement " . $event->getTitle() . ' a été commenté.', $event->getId());
        }
       
        $comments = $this->get('comment.v3')->commentsEventDetails($request, $em, $event);
        
        return $comments;
    }


    public function addGoodPlanCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);
        $comment = new Comment();
        if ($type == 'merchant') {
            $merchant = $event->getMerchant();
            $comment->setMerchant($merchant);
            $comment->setType($type);
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            $comment->setAssociation($association);
            $comment->setType($type);
        } else {
            $comment->setUser($user);
            $comment->setType('citizen');
        }

        $comment->setUser($user);
        $comment->setGoodPlan($event);
        $comment->setCommunity($event->getCommunity());
        $comment->setContent($data["content"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $comment->setImage($image);
        }
        if (isset($data['document']) && $data['document'] != null) {
            $document = new File();
            $document->base64($data['document']);
            $comment->setDocument($document);
        }
        $em->persist($comment);
        $em->flush();
        if ($event->getCreateBy() && $event->getCreateBy() != $user) {
            $this->container->get('mobile')->pushNotification($event->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre bon plan " . $event->getTitle() . ' a été commenté.', $event->getId());
        }

        $comments = $this->get('comment.v3')->commentsGoodPlanDetails($request, $em, $event);

        return $comments;
    }


    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/article/reply",
     * description="Ce webservice permet de repondre à un commentaire d'un article.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function articleCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $currentComment = $em->getRepository("AppBundle:Comment")->find($id);
        $article = $currentComment->getArticle();

        $comment = new Comment();
        if ($type == 'merchant') {
            $merchant = $currentComment->getArticle()->getMerchant();
            $comment->setMerchant($merchant);
            $comment->setType($type);
        } elseif ($type == 'association') {
            $association = $currentComment->getArticle()->getAssociation();
            $comment->setAssociation($association);
            $comment->setType($type);
        } else {
            $comment->setUser($user);
            $comment->setType('citizen');
        }
        $comment->setArticle($article);
        $comment->setUser($user);
        $comment->setParent($currentComment);
        if($article->getType() == 'user'){
            $comment->setCommunity($article->getCommunity());
        }elseif($article->getType() == 'association'){
            $comment->setCommunity($article->getAssociation()->getCommunity());
        }elseif($article->getType() == 'merchant'){
            $comment->setCommunity($article->getMerchant()->getCommunity());
        }elseif($article->getType() == 'community'){
            $comment->setCommunity($article->getCommunity());
        }

        $comment->setContent($data["content"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $comment->setImage($image);
        }
        if (isset($data['document']) && $data['document'] != null) {
            $document = new File();
            $document->base64($data['document']);
            $comment->setDocument($document);
        }
        $em->persist($comment);
        if ($article->getType() == 'user') {
            if ($user != $article->getUser() && $article->getUser() == $currentComment->getUser()) {
                $message = $user->getFirstname() . ' ' . $user->getLastname() . " a  répondu à un commentaire sur votre article " . $article->getTitle() . '.';
                $this->container->get('notification')->notify($article->getUser(), 'replyComment', $message, false, $comment);
                $this->container->get('mobile')->pushNotification($article->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
                $this->container->get('mobile')->pushNotification($article->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre article " . $article->getTitle() . ' a été commenté.', false, $article->getId());
            } elseif ($article->getUser() != $currentComment->getUser()) {
                $message = $user->getFirstname() . ' ' . $user->getLastname() . " a  répondu à votre commentaire sur l'article " . $article->getTitle() . '.';
                $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            }
        } else {
            if ($type == 'association') {
                $message = $currentComment->getArticle()->getAssociation()->getName() . " a  répondu à votre commentaire sur l'article " . $article->getTitle() . '.';
                if ($currentComment->getType() == 'citizen') {
                    $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                    $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
                }
            } elseif ($type == 'merchant') {
                $message = $currentComment->getArticle()->getMerchant()->getName() . " a  répondu à votre commentaire sur l'article " . $article->getTitle() . '.';
                if ($currentComment->getType() == 'citizen') {
                    $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                    $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
                }
            } elseif ($user != $currentComment->getUser()) {
                $message = $user->getFirstname() . ' ' . $user->getLastname() . " a  répondu à votre commentaire sur l'article " . $article->getTitle() . '.';
                if ($currentComment->getType() == 'citizen') {
                    $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                    $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
                }
            }
        }
        $em->flush();
        
        $comments = $this->get('comment.v3')->commentRepliesDetails($request, $em, $currentComment->getId());
        

        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/event/reply",
     * description="Ce webservice permet de repondre à un commentaire d'un evenement.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function eventCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $currentComment = $em->getRepository("AppBundle:Comment")->find($id);
        $comment = new Comment();
        if ($type == 'merchant') {
            $merchant = $currentComment->getEvent()->getMerchant();
            $comment->setMerchant($merchant);
            $comment->setType($type);
        } elseif ($type == 'association') {
            $association = $currentComment->getEvent()->getAssociation();
            $comment->setAssociation($association);
            $comment->setType($type);
        } else {
            $comment->setUser($user);
            $comment->setType('citizen');
        }
        $comment->setUser($user);
        $comment->setEvent($currentComment->getEvent());
        $comment->setParent($currentComment);
        $comment->setCommunity($currentComment->getCommunity());
        $comment->setContent($data["content"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $comment->setImage($image);
        }
        if (isset($data['document']) && $data['document'] != null) {
            $document = new File();
            $document->base64($data['document']);
            $comment->setDocument($document);
        }

        $em->persist($comment);
        if ($type == 'association') {
            $message = $currentComment->getEvent()->getAssociation()->getName() . " a  répondu à votre commentaire sur l'évènement " . $currentComment->getEvent()->getTitle() . '.';
            if ($currentComment->getType() == 'citizen') {
                $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            }
        } elseif ($type == 'merchant') {
            $message = $currentComment->getEvent()->getMerchant()->getName() . " a  répondu à votre commentaire sur l'évènement " . $currentComment->getEvent()->getTitle() . '.';
            if ($currentComment->getType() == 'citizen') {
                $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            }
        } elseif ($user != $currentComment->getUser()) {
            $message = $user->getFirstname() . ' ' . $user->getLastname() . " a  répondu à votre commentaire sur l'évènement " . $currentComment->getEvent()->getTitle() . '.';
            $this->container->get('mobile')->pushNotification($currentComment->getEvent()->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre évènement " . $currentComment->getEvent()->getTitle() . ' a été commenté.', $currentComment->getEvent()->getId());
            $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
        }
        $em->flush();
        
        
        $comments = $this->get('comment.v3')->commentsEventDetails($request, $em, $currentComment->getEvent());
        

        return $comments;
    }

    public function goodPlanCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        /** @var Comment $currentComment */
        $currentComment = $em->getRepository("AppBundle:Comment")->find($id);
        $comment = new Comment();
        if ($type == 'merchant') {
            $merchant = $currentComment->getGoodPlan()->getMerchant();
            $comment->setMerchant($merchant);
            $comment->setType($type);
        } elseif ($type == 'association') {
            $association = $currentComment->getEvent()->getAssociation();
            $comment->setAssociation($association);
            $comment->setType($type);
        } else {
            $comment->setUser($user);
            $comment->setType('citizen');
        }
        $comment->setUser($user);
        $comment->setGoodPlan($currentComment->getGoodPlan());
        $comment->setParent($currentComment);
        $comment->setCommunity($currentComment->getCommunity());
        $comment->setContent($data["content"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $comment->setImage($image);
        }
        if (isset($data['document']) && $data['document'] != null) {
            $document = new File();
            $document->base64($data['document']);
            $comment->setDocument($document);
        }

        $em->persist($comment);
        if ($type == 'association') {
            $message = $currentComment->getEvent()->getAssociation()->getName() . " a  répondu à votre commentaire sur le bon plan " . $currentComment->getGoodPlan()->getTitle() . '.';
            if ($currentComment->getType() == 'citizen') {
                $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            }
        } elseif ($type == 'merchant') {
            $message = $currentComment->getGoodPlan()->getMerchant()->getName() . " a  répondu à votre commentaire sur le bon plan " . $currentComment->getGoodPlan()->getTitle() . '.';
            if ($currentComment->getType() == 'citizen') {
                $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
                $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            }
        } elseif ($user != $currentComment->getUser()) {
            $message = $user->getFirstname() . ' ' . $user->getLastname() . " a  répondu à votre commentaire sur le bon plan " . $currentComment->getGoodPlan()->getTitle() . '.';
            $this->container->get('mobile')->pushNotification($currentComment->getGoodPlan()->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre évènement " . $currentComment->getGoodPlan()->getTitle() . ' a été commenté.', $currentComment->getGoodPlan()->getId());
            $this->container->get('mobile')->pushNotification($currentComment->getUser(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            $this->container->get('notification')->notify($currentComment->getUser(), 'replyComment', $message, false, $comment);
        }
        $em->flush();


        $comments = $this->get('comment.v3')->commentsGoodPlanDetails($request, $em, $currentComment->getGoodPlan());


        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/article/delete",
     * description="Ce webservice permet de supprimer un commentaire d'un article.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function deleteArticleCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $article = $comment->getArticle();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $article->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $article->getAssociation();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $article->getMerchant();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }

        $em->remove($comment);
        $em->flush();

        
        $comments = $this->get('comment.v3')->commentsArticleDetails($request, $em, $article);
       
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/event/delete",
     * description="Ce webservice permet de supprimer un commentaire d'un evenement.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function deleteEventCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getEvent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }

        $em->remove($comment);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentsEventDetails($request, $em, $event);
        
        return $comments;
    }

    public function deleteGoodPlanCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getGoodPlan();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }

        $em->remove($comment);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();

        $comments = $this->get('comment.v3')->commentsGoodPlanDetails($request, $em, $event);

        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/article/reply/delete",
     * description="Ce webservice permet de supprimer une reponse sur un commentaire d'un article.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function deleteArticleCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $article = $comment->getParent()->getArticle();
        $parent = $comment->getParent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $article->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $article->getAssociation();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $article->getMerchant();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $em->remove($comment);
        $em->flush();
        foreach ($parent->getComments() as $item) {
            if ($item->getType() == 'merchant') {
                if ($item->getMerchant()->getImage()) {
                    $path = $helper->asset($item->getMerchant()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getMerchant()->setImageURL($baseurl . $path);
                    }
                }
            } elseif ($item->getType() == 'association') {
                if ($item->getAssociation()->getImage()) {
                    $path = $helper->asset($item->getAssociation()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getAssociation()->setImageURL($baseurl . $path);
                    }
                }
            } else {
                if ($item->getUser()->getImage()) {
                    $path = $helper->asset($item->getUser()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getUser()->setImageURL($baseurl . $path);
                    }
                }
            }
        }
        return $parent->getComments();

        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentRepliesDetails($request, $em, $parent->getId());
        
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/event/reply/delete",
     * description="Ce webservice permet de supprimer une reponse sur un commentaire d'un evenement.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function deleteEventCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getParent()->getEvent();
        $parent = $comment->getParent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $em->remove($comment);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();
        
        
        
        $comments = $this->get('comment.v3')->commentsEventDetails($request, $em, $parent->getEvent());
       
        
        return $comments;
    }

    public function deleteGoodPlanCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getParent()->getGoodPlan();
        $parent = $comment->getParent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $em->remove($comment);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();



        $comments = $this->get('comment.v3')->commentsGoodPlanDetails($request, $em, $parent->getGoodPlan());


        return $comments;
    }


    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/article/update",
     * description="Ce webservice permet de mettre à jour un commentaire d'un article.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function updateArticleCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        if ($comment->getParent()) {
            $article = $comment->getParent()->getArticle();
        } else {
            $article = $comment->getArticle();
        }

        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $article->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $article->getAssociation();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $article->getMerchant();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $comment->setContent($data["content"]);
        $em->flush();
        if ($article->getType() == 'user' && $user != $article->getUser()) {
            $message = $user->getFirstname() . ' ' . $user->getLastname() . " a commenté votre article " . $article->getTitle() . '.';
            $this->container->get('notification')->notify($article->getUser(), 'newComment', $message, false, $comment);
            $this->container->get('mobile')->pushNotification($article->getCreateBy(), 'NOUS-ENSEMBLE ', "Votre article " . $article->getTitle() . ' a été commenté.', false, $article->getId());
        }
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentsArticleDetails($request, $em, $article);
        
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/event/update",
     * description="Ce webservice permet de mettre à jour un commentaire d'un evenement.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function updateEventCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        if ($comment->getParent()) {
            $event = $comment->getParent()->getEvent();
        } else {
            $event = $comment->getEvent();
        }
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $event->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $comment->setContent($data["content"]);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentsEventDetails($request, $em, $event);
        
        return $comments;
    }

    public function updateGoodPlanCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        if ($comment->getParent()) {
            $event = $comment->getParent()->getGoodPlan();
        } else {
            $event = $comment->getGoodPlan();
        }
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $event->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $comment->setContent($data["content"]);
        $em->flush();

        $comments = $this->get('comment.v3')->commentsGoodPlanDetails($request, $em, $event);

        return $comments;
    }


    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/article/reply/update",
     * description="Ce webservice permet de mettre à jour un commentaire d'un article.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function updateArticleCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $article = $comment->getParent()->getArticle();
        $parent = $comment->getParent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $article->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $article->getAssociation();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $article->getMerchant();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $comment->setContent($data["content"]);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentRepliesDetails($request, $em, $parent->getId());
        

        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/event/reply/update",
     * description="Ce webservice permet de mettre à jour un commentaire d'un evenement.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function updateEventCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getParent()->getEvent();
        $parent = $comment->getParent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $event->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $comment->setContent($data["content"]);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentRepliesDetails($request, $em, $parent->getId());
        
        return $comments;
    }

    public function updateGoodPlanCommentReplyAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getParent()->getGoodPlan();
        $parent = $comment->getParent();
        if ($type == 'citoyen') {
            if ($user != $comment->getUser() && $user != $event->getUser()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && $user != $event->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $comment->setContent($data["content"]);
        $em->flush();
        $apiVersion = $this->getUser()->getApiVersion();

        $comments = $this->get('comment.v3')->commentRepliesDetails($request, $em, $parent->getId());

        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/association/{id}/article/{page}/{limit}",
     * description="Ce webservice permet d'afficher tous les commentaires sur les articles d'une association.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function associationArticlesCommentsAction($id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
            throw $this->createAccessDeniedException();
        }

        $comments = $em->getRepository("AppBundle:Comment")->findAssociationArticlesComments($association, $page, $limit);
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/association/{id}/event/{page}/{limit}",
     * description="Ce webservice permet d'afficher tous les commentaires sur les evennements d'une association.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function associationEventsCommentsAction($id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
            throw $this->createAccessDeniedException();
        }
        $comments = $em->getRepository("AppBundle:Comment")->findAssociationEventsComments($association, $page, $limit);

        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/merchant/{id}/article/{page}/{limit}",
     * description="Ce webservice permet d'afficher tous les commentaires sur les articles d'une commerce.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function merchantArticlesCommentsAction($id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
            throw $this->createAccessDeniedException();
        }
        $comments = $em->getRepository("AppBundle:Comment")->findMerchantArticlesComments($merchant, $page, $limit);

        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/merchant/{id}/event/{page}/{limit}",
     * description="Ce webservice permet d'afficher tous les commentaires sur les evennements d'une commerce.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function merchantEventsCommentsAction($id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
            throw $this->createAccessDeniedException();
        }
        $comments = $em->getRepository("AppBundle:Comment")->findMerchantEventsComments($merchant, $page, $limit);
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/read/{id}/article",
     * description="Ce webservice permet de mettre les commentaires d'un article en lu",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function markCommentsArticleAsReadAction($id)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository("AppBundle:Article")->find($id);
        foreach ($article->getComments() as $comment) {
            $comment->setReaded(true);
        }
        $em->flush();
        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/comment/read/{id}/event",
     * description="Ce webservice permet de mettre les commentaires d'un evenement en lu",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function markCommentsEventAsReadAction($id)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        foreach ($event->getComments() as $comment) {
            $comment->setReaded(true);
        }
        $em->flush();
        return array("success" => true);
    }

    public function markCommentsGoodPlanAsReadAction($id)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);
        foreach ($event->getComments() as $comment) {
            $comment->setReaded(true);
        }
        $em->flush();
        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/article/list/delete",
     * description="Ce webservice permet de supprimer un commentaire d'un article.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function deleteArticleListCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $article = $comment->getArticle();
        if ($type == 'association') {
            $association = $article->getAssociation();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $article->getMerchant();
            if ($user != $comment->getUser() && $user != $article->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }

        $em->remove($comment);
        $em->flush();
        if ($type == 'association') {
            $comments = $em->getRepository("AppBundle:Comment")->findAssociationArticlesComments($association);
        } else {
            $comments = $em->getRepository("AppBundle:Comment")->findMerchantArticlesComments($merchant);
        }
        foreach ($comments as $item) {
            if ($item->getType() == 'merchant') {
                if ($item->getMerchant()->getImage()) {
                    $path = $helper->asset($item->getMerchant()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getMerchant()->setImageURL($baseurl . $path);
                    }
                }
            } elseif ($item->getType() == 'association') {
                if ($item->getAssociation()->getImage()) {
                    $path = $helper->asset($item->getAssociation()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getAssociation()->setImageURL($baseurl . $path);
                    }
                }
            } else {
                if ($item->getUser()->getImage()) {
                    $path = $helper->asset($item->getUser()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getUser()->setImageURL($baseurl . $path);
                    }
                }
            }
        }
        return $comments;
    }

    /**
     * @ApiDoc(resource="/api/comment/{id}/{type}/event/list/delete",
     * description="Ce webservice permet de supprimer un commentaire d'un evenement.",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param type $id
     * @return Comment
     */
    public function deleteEventListCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getEvent();
        if ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }

        $em->remove($comment);
        $em->flush();
        if ($type == 'association') {
            $comments = $em->getRepository("AppBundle:Comment")->findAssociationEventsComments($association);
        } else {
            $comments = $em->getRepository("AppBundle:Comment")->findMerchantEventsComments($merchant);
        }
        foreach ($comments as $item) {
            if ($item->getType() == 'merchant') {
                if ($item->getMerchant()->getImage()) {
                    $path = $helper->asset($item->getMerchant()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getMerchant()->setImageURL($baseurl . $path);
                    }
                }
            } elseif ($item->getType() == 'association') {
                if ($item->getAssociation()->getImage()) {
                    $path = $helper->asset($item->getAssociation()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getAssociation()->setImageURL($baseurl . $path);
                    }
                }
            } else {
                if ($item->getUser()->getImage()) {
                    $path = $helper->asset($item->getUser()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getUser()->setImageURL($baseurl . $path);
                    }
                }
            }
        }
        return $comments;
    }

    public function deleteGoodPlanListCommentAction(Request $request, $id, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $comment = $em->getRepository("AppBundle:Comment")->find($id);
        $event = $comment->getGoodPlan();
        if ($type == 'association') {
            $association = $event->getAssociation();
            if ($user != $comment->getUser() && !$association->getAdmins()->contains($user) && $association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        } else {
            $merchant = $event->getMerchant();
            if ($user != $comment->getUser() && !$merchant->getAdmins()->contains($user) && $merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }

        $em->remove($comment);
        $em->flush();
        if ($type == 'association') {
            $comments = $em->getRepository("AppBundle:Comment")->findAssociationEventsComments($association);
        } else {
            $comments = $em->getRepository("AppBundle:Comment")->findMerchantEventsComments($merchant);
        }
        foreach ($comments as $item) {
            if ($item->getType() == 'merchant') {
                if ($item->getMerchant()->getImage()) {
                    $path = $helper->asset($item->getMerchant()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getMerchant()->setImageURL($baseurl . $path);
                    }
                }
            } elseif ($item->getType() == 'association') {
                if ($item->getAssociation()->getImage()) {
                    $path = $helper->asset($item->getAssociation()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getAssociation()->setImageURL($baseurl . $path);
                    }
                }
            } else {
                if ($item->getUser()->getImage()) {
                    $path = $helper->asset($item->getUser()->getImage(), 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $item->getUser()->setImageURL($baseurl . $path);
                    }
                }
            }
        }
        return $comments;
    }

    public function articleCommentsAction(Request $request, $article)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentsArticleDetails($request, $em, $article);
       
        return $comments;
    }

    public function eventCommentsAction(Request $request, $event)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $apiVersion = $this->getUser()->getApiVersion();
        
        $comments = $this->get('comment.v3')->commentsEventDetails($request, $em, $event);
        
        return $comments;
    }

    public function goodPlanCommentsAction(Request $request, $event)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $comments = $this->get('comment.v3')->commentsGoodPlanDetails($request, $em, $event);

        return $comments;
    }
    
    public function eventCommentsPaginationAction(Request $request, $event, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $result = array();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $comments = $em->getRepository("AppBundle:Comment")->findEventPaginationComments($event, $page);
        foreach ($comments as $comment) {
            $replies = array();
            $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment['id']);
            switch ($comment['type']) {
                case 'citizen':
                    if (isset($comment["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($comment["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($comment["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            if (isset($comment["commentImg"])) {
                $img = $em->getRepository("AppBundle:File")->find($comment["commentImg"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["commentImg"] = $baseurl . $path;
                    }
                }
            }

            foreach ($secondLevel as $value) {
                if (isset($value["commentImg"])) {
                    $img = $em->getRepository("AppBundle:File")->find($value["commentImg"]);
                    if ($img) {
                        $path = $helper->asset($img, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["commentImg"] = $baseurl . $path;
                        }
                    }
                }
                switch ($value['type']) {
                    case 'citizen':
                        if (isset($value["userImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user.jpg";
                        }

                        break;

                    case 'association':
                        if (isset($value["associationImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    case 'merchant':
                        if (isset($value["merchantImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    default:
                        break;
                }
                
                if (($value['type'] == "citizen" && $value["userId"]) ||
                        ($value['type'] == "association" && $value["associationId"]) ||
                        ($value['type'] == "merchant" && $value["merchantId"])) {
                    $replies[] = $value;
                }
            }
            $comment['replies'] = $replies;
            if (($comment['type'] == "citizen" && $comment["userId"]) ||
                    ($comment['type'] == "association" && $comment["associationId"]) ||
                    ($comment['type'] == "merchant" && $comment["merchantId"])) {
                $result[] = $comment;
            }
        }
        return $result;
    }

    public function goodPlanCommentsPaginationAction(Request $request, $goodPlan, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $result = array();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $comments = $em->getRepository("AppBundle:Comment")->findGoodPlanPaginationComments($goodPlan, $page);
        foreach ($comments as $comment) {
            $replies = array();
            $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment['id']);
            switch ($comment['type']) {
                case 'citizen':
                    if (isset($comment["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($comment["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($comment["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            if (isset($comment["commentImg"])) {
                $img = $em->getRepository("AppBundle:File")->find($comment["commentImg"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["commentImg"] = $baseurl . $path;
                    }
                }
            }

            if (isset($comment["commentDoc"])) {
                $doc = $em->getRepository("AppBundle:File")->find($comment["commentDoc"]);
                if ($doc) {
                    $path = $helper->asset($doc, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["document"] = $baseurl . $path;
                    }
                }
            }

            foreach ($secondLevel as $value) {
                if (isset($value["commentImg"])) {
                    $img = $em->getRepository("AppBundle:File")->find($value["commentImg"]);
                    if ($img) {
                        $path = $helper->asset($img, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["commentImg"] = $baseurl . $path;
                        }
                    }
                }
                if (isset($value["commentDoc"])) {
                    $doc = $em->getRepository("AppBundle:File")->find($value["commentDoc"]);
                    if ($doc) {
                        $path = $helper->asset($doc, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["document"] = $baseurl . $path;
                        }
                    }
                }
                switch ($value['type']) {
                    case 'citizen':
                        if (isset($value["userImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user.jpg";
                        }

                        break;

                    case 'association':
                        if (isset($value["associationImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    case 'merchant':
                        if (isset($value["merchantImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    default:
                        break;
                }

                if (($value['type'] == "citizen" && $value["userId"]) ||
                    ($value['type'] == "association" && $value["associationId"]) ||
                    ($value['type'] == "merchant" && $value["merchantId"])) {
                    $replies[] = $value;
                }
            }
            $comment['replies'] = $replies;
            if (($comment['type'] == "citizen" && $comment["userId"]) ||
                ($comment['type'] == "association" && $comment["associationId"]) ||
                ($comment['type'] == "merchant" && $comment["merchantId"])) {
                $result[] = $comment;
            }
        }
        return $result;
    }
    
    
    public function articleCommentsPaginationAction(Request $request, $article, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $result = array();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $comments = $em->getRepository("AppBundle:Comment")->findArticlePaginationComments($article, $page);
        foreach ($comments as $comment) {
            $replies = array();
            $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment['id']);
            switch ($comment['type']) {
                case 'citizen':
                    if (isset($comment["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($comment["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($comment["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            if (isset($comment["commentImg"])) {
                $img = $em->getRepository("AppBundle:File")->find($comment["commentImg"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["commentImg"] = $baseurl . $path;
                    }
                }
            }
            if (isset($comment["commentDoc"])) {
                $doc = $em->getRepository("AppBundle:File")->find($comment["commentDoc"]);
                if ($doc) {
                    $path = $helper->asset($doc, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["document"] = $baseurl . $path;
                    }
                }
            }
            foreach ($secondLevel as $value) {
                if (isset($value["commentImg"])) {
                    $img = $em->getRepository("AppBundle:File")->find($value["commentImg"]);
                    if ($img) {
                        $path = $helper->asset($img, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["commentImg"] = $baseurl . $path;
                        }
                    }
                }
                if (isset($value["commentDoc"])) {
                    $doc = $em->getRepository("AppBundle:File")->find($value["commentDoc"]);
                    if ($doc) {
                        $path = $helper->asset($doc, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["document"] = $baseurl . $path;
                        }
                    }
                }
                switch ($value['type']) {
                    case 'citizen':
                        if (isset($value["userImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user.jpg";
                        }

                        break;

                    case 'association':
                        if (isset($value["associationImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    case 'merchant':
                        if (isset($value["merchantImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    default:
                        break;
                }
                
                if (($value['type'] == "citizen" && $value["userId"]) ||
                        ($value['type'] == "association" && $value["associationId"]) ||
                        ($value['type'] == "merchant" && $value["merchantId"])) {
                    $replies[] = $value;
                }
            }
            $comment['replies'] = $replies;
            if (($comment['type'] == "citizen" && $comment["userId"]) ||
                    ($comment['type'] == "association" && $comment["associationId"]) ||
                    ($comment['type'] == "merchant" && $comment["merchantId"])) {
                $result[] = $comment;
            }
        }
        return $result;
    }
}
