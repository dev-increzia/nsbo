<?php

namespace ApiBundle\Service;

use AppBundle\Entity\Association as Association;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AssociationV3
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function home($request, $em, $id, $nbParticipants, $nbComments, $user, $association)
    {
        $result = array();

        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw new AccessDeniedException();
            }
        }
        
        $result["nbEvents"] = count($association->getEvents());
        // nombre des participants et des commentaire des evenements
        
        foreach ($association->getEvents() as $event) {
            $nbParticipants += count($event->getParticipants());
            $nbComments += count($event->getComments());
        }
        // nombre des commentaires des articles
        
        foreach ($association->getArticles() as $article) {
            $nbComments += count($article->getComments());
        }
        
        $result["nbParticipants"] = $nbParticipants;
        $result["nbComments"] = $nbComments;

        // get last 3 articles
        $articles = $em->getRepository("AppBundle:Article")->getLastCommentedArticlesByAssociation($id);

        $articlefinal = array();

        foreach ($articles as $article) {
            if ($article['image']) {
                $path = $article['imageName'];
                
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                
                if ($path) {
                    $article["imageURL"] = ($baseurl . '/upload/' . $path);
                }
            }

            $unreadComments = $em->getRepository("AppBundle:Comment")->countUnreadArticleComments($article['id']);
            
            $article['unreadComments'] = $unreadComments;
            
            $articlefinal[] = $article;
        }

        $result["articles"] = $articlefinal;
        // get last 3 events
        
        $events = $em->getRepository("AppBundle:Event")->getLastCommentedEventsByAssociation($id);

        $evenfinal = array();

        foreach ($events as $event) {
            if ($event['image']) {
                $path = $event['imageName'];
                
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                
                if ($path) {
                    $event["imageURL"] = ($baseurl . '/upload/' . $path);
                }
            }

            $unreadComments = $em->getRepository("AppBundle:Comment")->countUnreadEventComments($event['id']);
            $event['unreadComments'] = $unreadComments;

            $evenfinal[] = $event;
        }

        $result["events"] = $evenfinal;

        return $result;
    }
    
    public function addAdmin($request, $em, $user, $association, $data, $exist)
    {
        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw new AccessDeniedException();
            }
        }

        if (!$exist) {
            return array("error" => 'email not exists');
        }
        
        if (!$association->getAdmins()->contains($exist)) {
            $association->addAdmin($exist);
            
            $message = "Vous êtes désormais un administrateur du groupe " . $association->getName() . ". ";
            
            $this->container->get('notification')->notify($exist, 'admin', $message, false);
            $this->container->get('mobile')->pushNotification($exist, 'NOUS-ENSEMBLE ', "", false, false, 'on');
            
            $em->flush();
            
            $this->container->get('mail')->sendInfoAdminMail($data['email'], $user, 'association', $association);
        } else {
            return array("error" => 'admin exists');
        }

        return array("success" => true);
    }
    
    public function addSuperAdmin($request, $em, $user, $association, $data, $exist)
    {
        if ($association->getSuAdmin() != $user || !$association->getEnabled()) {
            throw new AccessDeniedException();
        }

        if (!$exist) {
            return array("error" => 'email not exists');
        }
        
        $association->setSuAdmin($exist);
        
        if (!$association->getAdmins()->contains($user)) {
            $association->addAdmin($user);
        }

        $message = "Vous êtes désormais le superadmin du groupe " . $association->getName() . ". ";
        
        $this->container->get('notification')->notify($exist, 'admin', $message, false);
        $this->container->get('mobile')->pushNotification($exist, 'NOUS-ENSEMBLE ', "", false, false, 'on');
        
        $em->flush();
        
        $this->container->get('mail')->sendInfoAdminMail($data['email'], $user, 'association', $association);

        return array("success" => true);
    }
    
    public function removeAdmins($request, $em, $user, $association, $data)
    {
        if ($association->getSuAdmin() != $user || !$association->getEnabled()) {
            throw new AccessDeniedException();
        }
         $admin = $em->getRepository("UserBundle:User")->find($data['admins']);
        $association->removeAdmin($admin);
        $message = "Vous n'êtes plus un administrateur du groupe " . $association->getName() . ". ";
        $this->container->get('notification')->notify($admin, 'admin', $message, false);
        $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
        $em->flush();

        return array("success" => true);
    }
}
