<?php

namespace ApiBundle\Service;

use AppBundle\Entity\Merchant as Merchant;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MerchantV3
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function home($request, $em, $id, $nbParticipants, $nbComments, $user, $merchant)
    {
        $result = array();

        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                return array('error' => true);
            }
        }
        
        $result["nbEvents"] = count($merchant->getEvents());
        // nombre des participants et des commentaire des evenements
        
        foreach ($merchant->getEvents() as $event) {
            $nbParticipants += $event->getNbparticipants();
            $nbComments += count($event->getComments());
        }
        // nombre des commentaires des articles
        
        foreach ($merchant->getArticles() as $article) {
            $nbComments += count($article->getComments());
        }
        
        $result["nbParticipants"] = $nbParticipants;
        $result["nbComments"] = $nbComments;

        // get last 3 articles
        $articles = $em->getRepository("AppBundle:Article")->getLastCommentedArticlesByMerchant($id);

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
        $events = $em->getRepository("AppBundle:Event")->getLastCommentedEventsByMerchant($id);

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
     
    public function addAdmin($request, $em, $user, $merchant, $exist, $data)
    {
        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                throw new AccessDeniedException();
            }
        }

        if (!$exist) {
            return array("error" => 'email not exists');
        }
        
        if (!$merchant->getAdmins()->contains($exist)) {
            $merchant->addAdmin($exist);
            
            $message = "Vous êtes désormais un administrateur de patenaire " . $merchant->getName() . ". ";
            
            $this->container->get('notification')->notify($exist, 'admin', $message, false);
            $this->container->get('mobile')->pushNotification($exist, 'NOUS-ENSEMBLE ', "", false, false, 'on');
            
            $em->flush();
            
            $this->container->get('mail')->sendInfoAdminMail($data['email'], $user, 'merchant', $merchant);
        } else {
            return array("error" => 'admin exists');
        }

        return array("success" => true);
    }
     
    public function addSuperAdmin($request, $em, $user, $merchant, $exist, $data)
    {
        if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()) {
            throw new AccessDeniedException();
        }


        if (!$exist) {
            return array("error" => 'email not exists');
        }
        
        $merchant->setSuAdmin($exist);
        
        if (!$merchant->getAdmins()->contains($user)) {
            $merchant->addAdmin($user);
        }
        
        $message = "Vous êtes désormais le superadmin du partenaire " . $merchant->getName() . ". ";
        
        $this->container->get('notification')->notify($exist, 'admin', $message, false);
        $this->container->get('mobile')->pushNotification($exist, 'NOUS-ENSEMBLE ', "", false, false, 'on');
        
        $em->flush();
        
        $this->container->get('mail')->sendInfoAdminMail($data['email'], $user, 'merchant', $merchant);

        return array("success" => true);
    }
     
    public function removeAdmins($request, $em, $user, $merchant, $data)
    {
        if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()) {
            throw new AccessDeniedException();
        }
         $admin = $em->getRepository("UserBundle:User")->find($data['admins']);
            
            $merchant->removeAdmin($admin);
            
            $message = "Vous n'êtes plus un administrateur du partenaire " . $merchant->getName() . ". ";
            
            $this->container->get('notification')->notify($admin, 'admin', $message, false);
            $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
            
            $em->flush();

        return array("success" => true);
    }
}
