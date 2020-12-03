<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\AssociationUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Association;
use AppBundle\Entity\Notification;
use UserBundle\Entity\User;
use AppBundle\Entity\File;
use AppBundle\Entity\AssociationDeleted;

class AssociationController extends Controller
{

    /**
     * @ApiDoc(resource="/api/association",
     * description="API add Association",
     * statusCodes={200="Successful"})
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $association = new Association();
        $association->setCreateBy($this->getUser());
        $association->setSuAdmin($this->getUser());
        $name = strtolower($data['name']);
        $association->setName($name);
        $association->setDescription($data['description']);
        $association->setPhone($data['phone']);
        $category = $em->getRepository('AppBundle:Category')->find($data['category']);
        $association->setCategory($category);
        $association->setAddress($data['address']);
        $association->setCodePostal($data['codePostal']);
        $association->setEmail($data['email']);
        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
        $association->setCity($city);
        $community = $em->getRepository('AppBundle:Community')->find($data['community']);
        $association->setCommunity($community);
        $association->setMonday($data['monday']);
        $association->setMondayHour($data['mondayHour']);
        $association->setMondayHourEnd($data['mondayHourEnd']);
        $association->setTuesday($data['tuesday']);
        $association->setTuesdayHour($data['tuesdayHour']);
        $association->setTuesdayHourEnd($data['tuesdayHourEnd']);
        $association->setWednesday($data['wednesday']);
        $association->setWednesdayHour($data['wednesdayHour']);
        $association->setWednesdayHourEnd($data['wednesdayHourEnd']);
        $association->setThursday($data['thursday']);
        $association->setThursdayHour($data['thursdayHour']);
        $association->setThursdayHourEnd($data['thursdayHourEnd']);
        $association->setFriday($data['friday']);
        $association->setFridayHour($data['fridayHour']);
        $association->setFridayHourEnd($data['fridayHourEnd']);
        $association->setSaturday($data['saturday']);
        $association->setSaturdayHour($data['saturdayHour']);
        $association->setSaturdayHourEnd($data['saturdayHourEnd']);
        $association->setSunday($data['sunday']);
        $association->setSundayHour($data['sundayHour']);
        $association->setSundayHourEnd($data['sundayHourEnd']);
        $association->setEnabled(true);
        $association->setModerate('wait');
        $association->setTiming($data["timing"]);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $association->setImage($image);
        }

        $em->persist($association);
        $em->flush();
        $this->container->get('mail')->sendCreationMail($this->getUser(), 'groupe / association');

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/association/home/{id}",
     * description="Ce web service permet de récuperer les information de la page d'accueil d'un association",
     * statusCodes={200="Successful"})
     */
    public function homeAction($id, Request $request)
    {
        $result = array();
        
        $nbParticipants = 0;
        
        $nbComments = 0;
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        
        $association = $em->getRepository("AppBundle:Association")->find($id);
        
        $apiVersion = $this->getUser()->getApiVersion();
        
       
        $result = $this->get('association.v3')->home($request, $em, $id, $nbParticipants, $nbComments, $user, $association);
        
        
        return $result;
    }

    /**
     * Ce web service permet de consulter les détails d'une association
     * @param type $id
     * @return type
     * @throws type
     */
    public function viewAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        /** @var Association $association */
        $association = $em->getRepository("AppBundle:Association")->find($id);
        if ($association->getImage()) {
            $path = $helper->asset($association->getImage(), 'file');
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            if ($path) {
                $association->setImageURL($baseurl . $path);
            }
        }
        if($association->getVideo()){
            $image = $em->getRepository("AppBundle:File")->find($association->getVideo()->getId());
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                $association->setVideoURL($baseurl . $path);
                }
            }
        }else{
            $association->setVideoURL(null);
        }
        
        if ($association->getSuAdmin() == $user) {
            $association->setRole('superadmin');
        } elseif ($association->getAdmins()->contains($user)) {
            $association->setRole('admin');
        }else {
            $association->setRole('simple_user');
        }

        foreach ($association->getUsers() as $u) {

            if($u->getUser() == $user ) {
                $association->setIsMember(true);
            }


        }
        return $association;
    }

    /**
     * @ApiDoc(resource="/api/association/{id}/update",
     * description="API add Association",
     * statusCodes={200="Successful"})
     */
    public function updateAction($id, Request $request)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        
        $datas = $request->getContent();
        
        $data = (array) json_decode($datas);
        
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $association = $em->getRepository("AppBundle:Association")->find($id);
        
        $apiVersion = $this->getUser()->getApiVersion();
        
        if (!$association) {
            return array("success" => false);
        }



        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        
        
        $association->setName($data['name']);
        $association->setDescription($data['description']);
        $association->setPhone($data['phone']);
        $category = $em->getRepository('AppBundle:Category')->find($data['category']);
        $association->setCategory($category);
        $association->setAddress($data['address']);
        $association->setEmail($data['email']);
        $city = $em->getRepository('AppBundle:City')->find($data['city']);
        $association->setCity($city);
        $association->setCodePostal($city->getZipcode());
        $association->setTiming($data['timing']);
        
        if (!empty($data['photo'])) {
            $image = new File();
            
            $image->base64($data['photo']);
            
            $association->setImage($image);
        } elseif ($data["todelete"]) {
            $association->setImage(null);
        }

        $em->flush();
        if ($association->getSuAdmin() == $user) {
            $association->setRole('superadmin');
        } else {
            $association->setRole('admin');
        }

        if ($association->getImage()) {
            $path = $helper->asset($association->getImage(), 'file');
            
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            
            if ($path) {
                $association->setImageURL($baseurl . $path);
            }
        }

        $formatedAsso = $em->getRepository('AppBundle:Association')->formatAssocition($association,$association->getRole(),$em);
        return array("success" => true, 'association' => $formatedAsso);
    }

    /**
     * @ApiDoc(resource="/api/association/{id}/volunteers",
     * description="API get Association volunteers",
     * statusCodes={200="Successful"})
     */
    public function volunteersAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $association = $em->getRepository("AppBundle:Association")->find($id);
        
        $apiVersion = $this->getUser()->getApiVersion();
        
        if (!$association) {
            return array("success" => false);
        }



        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        
        
        $volunteers = $em->getRepository("AppBundle:EventVolunteer")->findVolunteers('association', $association);

        return $volunteers;
    }

    /**
     * @ApiDoc(resource="/api/association/{id}/volunteers/delete",
     * description="API get Association volunteers",
     * statusCodes={200="Successful"})
     */
    public function deleteVolunteersAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $volunteer = $em->getRepository('AppBundle:EventVolunteer')->find($id);

        if (!$volunteer) {
            return array("success" => false);
        }
        $association = $volunteer->getEvent()->getAssociation();



        if ((!$association->getAdmins()->contains($user) || !$association->getSuAdmin() != $user) && !$association->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($volunteer);
        $em->flush();

        $formatedAsso = $em->getRepository('AppBundle:Association')->formatAssocition($association,$association->getRole(),$em,true);

        return array('success' => true,'association' => $formatedAsso);
    }

    /**
     * @ApiDoc(resource="/api/association/{id}/admin",
     * description="API add association admin",
     * statusCodes={200="Successful"})
     */
    public function addAdminAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $association = $em->getRepository("AppBundle:Association")->find($id);
        
        $datas = $request->getContent();
        
        $data = (array) json_decode($datas);

        $exist = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($data['email']);
        
        $apiVersion = $this->getUser()->getApiVersion();
        
      
        // Call V2 function, preferably located inside a service ...
        $result = $this->get('association.v3')->addAdmin($request, $em, $user, $association, $data, $exist);
        
        
        return $result;
    }

    /**
     * @ApiDoc(resource="/api/association/{id}/superadmin",
     * description="API add association admin",
     * statusCodes={200="Successful"})
     */
    public function addSuperAdminAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $association = $em->getRepository("AppBundle:Association")->find($id);
        
        $datas = $request->getContent();
        
        $data = (array) json_decode($datas);
        
        $exist = $this->get('fos_user.user_manager')->findUserByUsername($data['email']);
        
        $apiVersion = $this->getUser()->getApiVersion();
        
      
        $result = $this->get('association.v3')->addSuperAdmin($request, $em, $user, $association, $data, $exist);
        
        
        return $result;
    }
    /**
     * @ApiDoc(resource="/api/association/{id}/admins/{page}/{limit}",
     * description="API get Association admins",
     * statusCodes={200="Successful"})
     */
    public function adminsAction($id, Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $association = $em->getRepository("AppBundle:Association")->find($id);
        
        $apiVersion = $this->getUser()->getApiVersion();
        
        if (!$association) {
            return array("success" => false);
        }


        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        
        
        $admins = $association->getAdmins();
        $adminsFormated = [];
        
        foreach ($admins as $admin) {
            if ($admin->getImage()) {
                $path = $helper->asset($admin->getImage(), 'file');
                
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                
                if ($path) {
                    $admin->setImageURL($baseurl . $path);
                }
            }
            $adminsFormated[]= array('id'=> $admin->getId(),'lastname' => $admin->getLastname(),'firstname' => $admin->getFirstname());
        }
        
        $offset = ($page - 1) * $limit;
        
        $pagination = array_slice($adminsFormated, $offset, $limit);
        
        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/association/{association}/admin/remove",
     * description="API get Association volunteers",
     * statusCodes={200="Successful"})
     */
    public function removeAdminsAction(Association $association, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $apiVersion = $this->getUser()->getApiVersion();
        
      
        $result = $this->get('association.v3')->removeAdmins($request, $em, $user, $association, $data);
        
        
        return $result;
    }
    
    /**
     * @ApiDoc(resource="/api/association/cities",
     * description="API get Associations par cités",
     * statusCodes={200="Successful"})
     */
    public function getAssociationsByCitiesAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        
        $associations = $admin = $em->getRepository("AppBundle:Association")->findAllByCities($user);
        
        foreach ($associations as $association) {
            if ($association->getImage()) {
                $path = $helper->asset($association->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $association->setImageURL($baseurl . $path);
                }
            }
        }
        
        $offset = ($page - 1) * $limit;
        
        $pagination = array_slice($associations, $offset, $limit);
        
        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/association/community",
     * description="API get Associations par cités",
     * statusCodes={200="Successful"})
     */
    public function getAssociationsByCommunityAction(Request $request,$id_community, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $associations = $admin = $em->getRepository("AppBundle:Association")->findAllByCommunity($id_community)->getQuery()->getResult();

        foreach ($associations as $association) {
            if ($association->getImage()) {
                $path = $helper->asset($association->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $association->setImageURL($baseurl . $path);
                }
            }
        }

        $offset = ($page - 1) * $limit;

        $pagination = array_slice($associations, $offset, $limit);

        return $pagination;
    }



    public function changeSuAdminAction(Request $request, $id)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $suAdminEmail = $data['email'];
        $errors = array();
        $sucess = false;
        $suAdmin = $em->getRepository("UserBundle:User")->findOneByEmail($suAdminEmail);
        if (!$suAdmin) {
            $errors[] = "L'email ". $suAdminEmail ." n'est pas un utilisateur de NOUS-Ensemble.";
            $sucess = false;
        } else {
            $association = $em->getRepository("AppBundle:Association")->find($id);
            $association->setSuAdmin($suAdmin);
            $em->flush();
            $message = "Vous êtes désormais le superadmin du groupe " . $association->getName() . ". ";
            $this->container->get('notification')->notify($suAdmin, 'admin', $message, false);
        
            $this->container->get('mail')->sendInfoAdminMail($data['email'], $this->getUser(), 'association', $association);

            $sucess = true;
        }
        
        return array('sucess' => $sucess, 'errors' => $errors);
    }

    public function deleteAssociationAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $reason = $data['reason'];
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $associationDeleted = new AssociationDeleted();
        $associationDeleted->setAddress($association->getAddress());
        $associationDeleted->setCategory($association->getCategory());
        $associationDeleted->setCity($association->getCity());
        //$associationDeleted->setCommunity($association->getCommunity());
        $associationDeleted->setCodePostal($association->getCodePostal());
        $associationDeleted->setDescription($association->getDescription());
        $associationDeleted->setEmail($association->getEmail());
        $associationDeleted->setEnabled($association->getEnabled());
        $associationDeleted->setFriday($association->getFriday());
        $associationDeleted->setFridayHour($association->getFridayHour());
        $associationDeleted->setFridayHourEnd($association->getFridayHourEnd());
        $associationDeleted->setImage($association->getImage());
        $associationDeleted->setMonday($association->getMonday());
        $associationDeleted->setMondayHour($association->getMondayHour());
        $associationDeleted->setMondayHourEnd($association->getMondayHourEnd());
        $associationDeleted->setName($association->getName());
        $associationDeleted->setPhone($association->getPhone());
        $associationDeleted->setSaturday($association->getSaturday());
        $associationDeleted->setSaturdayHour($association->getSaturdayHour());
        $associationDeleted->setSaturdayHourEnd($association->getSaturdayHourEnd());
        $associationDeleted->setSunday($association->getSunday());
        $associationDeleted->setSundayHour($association->getSundayHour());
        $associationDeleted->setSundayHourEnd($association->getSundayHourEnd());
        $associationDeleted->setThursday($association->getThursday());
        $associationDeleted->setThursdayHour($association->getThursdayHour());
        $associationDeleted->setThursdayHourEnd($association->getThursdayHourEnd());
        $associationDeleted->setTiming($association->getTiming());
        $associationDeleted->setTuesday($association->getTuesday());
        $associationDeleted->setThursdayHour($association->getTuesdayHour());
        $associationDeleted->setTuesdayHourEnd($association->getTuesdayHourEnd());
        $associationDeleted->setWednesday($association->getWednesday());
        $associationDeleted->setWednesdayHour($association->getWednesdayHour());
        $associationDeleted->setWednesdayHourEnd($association->getWednesdayHourEnd());
        $associationDeleted->setReason($reason);
                
        $em->persist($associationDeleted);
        $em->flush();
        // disable articles
        foreach ($association->getArticles() as $article) {
            $article->setEnabled(false);
        }
        // disable events
        foreach ($association->getEvents() as $event) {
            $event->setEnabled(false);
        }
        // delete comments
        foreach ($association->getComments() as $comment) {
            $em->remove($comment);
            $em->flush();
        }
       
        $em->remove($association);
        $em->flush();
        $subject = "NOUS Ensemble Un compte association a été supprimé";
        $content = $this->container->get('templating')->render('AppBundle:Mail:removeAccount.html.twig', array(
                    'entity' => $associationDeleted,
                    'type' => 'association'
                ));
        $this->container->get('mail')->deleteAccountMail($subject, $content);
        return array('succes' => true);
    }

    public function demandeMembershipAction($id,Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        /** @var Association $association */
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $inCommunity = false;


        foreach ($association->getCommunity()->getUsers() as $u) {
            if($u->getUser() == $user && $u->getType()== 'approved') {
                $inCommunity = true;
            }
        }

        if(!$inCommunity)
        {
            return array("error" => 'Vous n\'êtes pas lié à la communauté de cette association');
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $phone = $data['phone'];
        foreach ($association->getUsers() as $u) {

            if($u->getUser() == $user ) {
                if($u->getType() == 'approved'){
                    return array("error" => 'Vous êtes déjà membre dans cette association');
                }else{
                    $u->setType('pending');
                    return array('success' => true);
                }

            }
        }


        $access = new AssociationUser();
        $access->setAssociation($association);
        $access->setUser($user);
        $access->setType('pending');
        $em->persist($access);
        $em->flush();
        $this->container->get('mail')->sendJoinMerchantOrAssociation($user, $association, $phone,'association');

        return array('success' => true);


    }

    public function acceptMembershipAction($id,$id_demander)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        /** @var User $u */
        $u = $em->getRepository("UserBundle:User")->find($id_demander);
        /** @var Association $association */
        $association = $em->getRepository("AppBundle:Association")->find($id);
        /** @var AssociationUser $membership */
        $membership = $em->getRepository("AppBundle:AssociationUser")->findOneBy(array('association'=>$association,'user'=>$u));
        if(!$membership) {
            return array('error' => 'Cette demande d\'adhésion n\'existe pas');
        }
        if(!$association->getAdmins()->contains($user)){

            if ($association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $membership->setType('approved');
        $message = "Votre demande d'adhésion au groupe  " . $association->getName() . ' a été acceptée';
        $this->container->get('notification')->notify($u, 'association', $message, false, $association);
        $this->container->get('mobile')->pushNotification($u, 'NOUS-ENSEMBLE ', $message, false, false, 'off',  $association->getId());


        $em->flush();

        return array('success' => true);


    }

    public function refuseMembershipAction($id,$id_demander)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        /** @var User $u */
        $u = $em->getRepository("UserBundle:User")->find($id_demander);
        /** @var Association $association */
        $association = $em->getRepository("AppBundle:Association")->find($id);
        /** @var AssociationUser $membership */
        $membership = $em->getRepository("AppBundle:AssociationUser")->findOneBy(array('association'=>$association,'user'=>$u));
        if(!$membership) {
            return array('error' => 'Cette demande d\'adhésion n\'existe pas');
        }
        if(!$association->getAdmins()->contains($user)){

            if ($association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $membership->setType('refused');


        $em->flush();

        return array('success' => true);


    }

    public function deleteMembershipAction($id,$id_demander)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        /** @var User $u */
        $u = $em->getRepository("UserBundle:User")->find($id_demander);
        /** @var Association $association */
        $association = $em->getRepository("AppBundle:Association")->find($id);
        /** @var AssociationUser $membership */
        $membership = $em->getRepository("AppBundle:AssociationUser")->findOneBy(array('association'=>$association,'user'=>$u));
        if(!$membership) {
            return array('error' => 'Cette demande d\'adhésion n\'existe pas');
        }
        if(!$association->getAdmins()->contains($user)){

            if ($association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $em->remove($membership);


        $em->flush();

        return array('success' => true);


    }

    /**
     * @param $id
     * @return array
     */
    public function getDemandesMembershipAction($id, Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $resDemand = [];
        $resMembers = [];
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        /** @var Association $association */
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $demands = $em->getRepository("AppBundle:Association")->getMembershipDemands($association);
        $members = $em->getRepository("AppBundle:Association")->getMemberships($association);
        if(!$association->getAdmins()->contains($user)){

            if ($association->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        foreach ($demands as $demand)
        {
            $dem = $demand;
            if($demand['userImg']) {
                $img = $em->getRepository("AppBundle:File")->find($demand['userImg']);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $dem["imageURL"] = $baseurl . $path;
                    }
                }
            }else{
                $dem["imageURL"] = "assets/img/default_adhesion.png";
            }
            $resDemand[]= $dem;
        }

        foreach ($members as $demand)
        {
            $dem = $demand;
            if($demand['userImg']) {
                $img = $em->getRepository("AppBundle:File")->find($demand['userImg']);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $dem["imageURL"] = $baseurl . $path;
                    }
                }
            }else{
                $dem["imageURL"] = "assets/img/default_adhesion.png";
            }
            $resMembers[]= $dem;
        }

        return array('demands'=>$resDemand, 'members'=>$resMembers);


    }


}
