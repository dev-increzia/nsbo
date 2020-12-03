<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Association;
use AppBundle\Entity\AssociationUser;
use AppBundle\Entity\MerchantUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Merchant;
use AppBundle\Entity\Notification;
use AppBundle\Entity\File;
use AppBundle\Entity\MerchantDeleted;
use UserBundle\Entity\User;

class MerchantController extends Controller
{

    /**
     * @ApiDoc(resource="/api/merchant",
     * description="API add merchant",
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
        $merchant = new Merchant();
        $merchant->setCreateBy($this->getUser());
        $merchant->setSuAdmin($this->getUser());
        $name = strtolower($data['name']);
        $merchant->setName($name);
        $merchant->setDescription($data['description']);
        $merchant->setPhone($data['phone']);
        $category = $em->getRepository('AppBundle:Category')->find($data['category']);
        $merchant->setCategory($category);
        $merchant->setAddress($data['address']);
        $merchant->setEmail($data['email']);
        $merchant->setSiret($data['siret']);
        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
        $merchant->setCity($city);
        $merchant->setCodePostal($city->getZipcode());
        $community = $em->getRepository('AppBundle:Community')->find($data['community']);
        $merchant->setCommunity($community);
        $merchant->setTiming($data['timing']);
        $merchant->setEnabled(true);
        $merchant->setModerate('wait');
        $merchant->setTiming($data["timing"]);
        if ($data['photo']) {
            $image = new File();
            $image->base64($data['photo']);
            $merchant->setImage($image);
        }

        $em->persist($merchant);
        $em->flush();
        $this->container->get('mail')->sendCreationMail($this->getUser(), 'commerce / partenaire');

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/merchant/home/{id}",
     * description="Ce web service récupére les informations de l'accueil commercants",
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
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);

        $apiVersion = $this->getUser()->getApiVersion();

        
        $result = $this->get('merchant.v3')->home($request, $em, $id, $nbParticipants, $nbComments, $user, $merchant);
        

        return $result;
    }

    /**
     * Ce web service permet de récupérer les informations d'un commercant
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
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        if ($merchant->getImage()) {
            $path = $helper->asset($merchant->getImage(), 'file');
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            if ($path) {
                $merchant->setImageURL($baseurl . $path);
            }
        }
        if($merchant->getVideo()){
            $image = $em->getRepository("AppBundle:File")->find($merchant->getVideo()->getId());
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                $merchant->setVideoURL($baseurl . $path);
                }
            }
        }else{
            $merchant->setVideoURL(null);
        }
        
        if ($merchant->getSuAdmin() == $user) {
            $merchant->setRole('superadmin');
        } else {
            $merchant->setRole('admin');
        }

        foreach ($merchant->getUsers() as $u) {

            if($u->getUser() == $user ) {
                $merchant->setIsMember(true);
            }


        }

        return $merchant;
    }

    /**
     * @ApiDoc(resource="/api/merchant/{id}/update",
     * description="API edit Merchant",
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

        $apiVersion = $this->getUser()->getApiVersion();

        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);

        if (!$merchant) {
            return array("success" => false);
        }



        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        

        $merchant->setName($data['name']);
        $merchant->setDescription($data['description']);
        $merchant->setPhone($data['phone']);
        $merchant->setSiret($data['siret']);
        $category = $em->getRepository('AppBundle:Category')->find($data['category']);
        $merchant->setCategory($category);
        $merchant->setAddress($data['address']);
        $merchant->setEmail($data['email']);
        $city = $em->getRepository('AppBundle:City')->find($data['city']);
        $merchant->setCity($city);
        $merchant->setCodePostal($city->getZipcode());


        if (!empty($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $merchant->setImage($image);
        } elseif ($data["todelete"]) {
            $merchant->setImage(null);
        }
        $em->flush();

        if ($merchant->getSuAdmin() == $user) {
            $merchant->setRole('superadmin');
        } else {
            $merchant->setRole('admin');
        }

        if ($merchant->getImage()) {
            $path = $helper->asset($merchant->getImage(), 'file');
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            if ($path) {
                $merchant->setImageURL($baseurl . $path);
            }
        }

        $formatedMerchant = $em->getRepository('AppBundle:Merchant')->formatMerchant($merchant,$merchant->getRole());

        return array("success" => true, 'merchant' => $formatedMerchant);
    }

    /**
     * @ApiDoc(resource="/api/merchant/{id}/volunteers",
     * description="API get Merchant volunteers",
     * statusCodes={200="Successful"})
     */
    public function volunteersAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$merchant) {
            return array("success" => false);
        }

        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        

        $volunteers = $em->getRepository("AppBundle:EventVolunteer")->findVolunteers('merchant', $merchant);

        foreach ($volunteers as $volunteer) {
            $userVolunteer = $volunteer->getUser();

            if ($userVolunteer->getImage()) {
                $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
                $path = $helper->asset($userVolunteer->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $userVolunteer->setImageURL($baseurl . $path);
                }
            }
        }

        return $volunteers;
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
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);

        $datas = $request->getContent();

        $data = (array) json_decode($datas);

        $exist = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($data['email']);

        $apiVersion = $this->getUser()->getApiVersion();

        
        $result = $this->get('merchant.v3')->addAdmin($request, $em, $user, $merchant, $exist, $data);
        

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/merchant/{id}/superadmin",
     * description="API add merchant admin",
     * statusCodes={200="Successful"})
     */
    public function addSuperAdminAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);


        $datas = $request->getContent();

        $data = (array) json_decode($datas);

        $exist = $this->get('fos_user.user_manager')->findUserByUsername($data['email']);

        $apiVersion = $this->getUser()->getApiVersion();

       
        $result = $this->get('merchant.v3')->addSuperAdmin($request, $em, $user, $merchant, $exist, $data);
        

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/merchant/{id}/admins/{page}/{limit}",
     * description="API get Merchant admins",
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
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        if (!$merchant) {
            return array("success" => false);
        }

        $apiVersion = $this->getUser()->getApiVersion();



        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }

        $admins = $merchant->getAdmins();
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
     * @ApiDoc(resource="/api/merchant/{merchant}/admin/remove",
     * description="API get Merchant volunteers",
     * statusCodes={200="Successful"})
     */
    public function removeAdminsAction(Merchant $merchant, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $apiVersion = $this->getUser()->getApiVersion();

        
        $result = $this->get('merchant.v3')->removeAdmins($request, $em, $user, $merchant, $data);
        

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/merchant/{id}/siret/{siret}",
     * description="API find num siret",
     * statusCodes={200="Successful"})
     */
    public function findSiretAction($id, $siret)
    {
        $em = $this->getDoctrine()->getManager();
        $result = false;
        if ($id == 0) {
            $exist = $em->getRepository("AppBundle:Merchant")->findOneBy(array('siret' => $siret));
            if ($exist) {
                $result = true;
            }
        } else {
            $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
            if ($merchant) {
                if ($merchant->getSiret() != $siret) {
                    $exist = $em->getRepository("AppBundle:Merchant")->findOneBy(array('siret' => $siret));
                    if ($exist) {
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/merchant/cities",
     * description="API get Merchants par cités",
     * statusCodes={200="Successful"})
     */
    public function getMerchantsByCitiesAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $merchants = $admin = $em->getRepository("AppBundle:Merchant")->findAllByCities($user);

        foreach ($merchants as $merchant) {
            if ($merchant->getImage()) {
                $path = $helper->asset($merchant->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $merchant->setImageURL($baseurl . $path);
                }
            }
        }

        $offset = ($page - 1) * $limit;
        $pagination = array_slice($merchants, $offset, $limit);
        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/merchant/cities",
     * description="API get Merchants par cités",
     * statusCodes={200="Successful"})
     */
    public function getMerchantsByCommunityAction(Request $request,$id_community, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $merchants = $admin = $em->getRepository("AppBundle:Merchant")->findAllByCommunity($id_community)->getQuery()->getResult();

        foreach ($merchants as $merchant) {
            if ($merchant->getImage()) {
                $path = $helper->asset($merchant->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $merchant->setImageURL($baseurl . $path);
                }
            }
        }

        $offset = ($page - 1) * $limit;
        $pagination = array_slice($merchants, $offset, $limit);
        return $pagination;
    }

    public function changeSuAdminAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $suAdminEmail = $data['email'];
        $errors = array();
        $sucess = false;
        $suAdmin = $em->getRepository("UserBundle:User")->findOneByEmail($suAdminEmail);
        if (!$suAdmin) {
            $errors[] = "L'email " . $suAdminEmail . " n'est pas un utilisateur de NOUS-Ensemble.";
            $sucess = false;
        } else {
            $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
            $merchant->setSuAdmin($suAdmin);
            $em->flush();
            $message = "Vous êtes désormais le superadmin du partenaire " . $merchant->getName() . ". ";
            $this->container->get('notification')->notify($suAdmin, 'admin', $message, false);
            $this->container->get('mail')->sendInfoAdminMail($data['email'], $this->getUser(), 'merchant', $merchant);

            $sucess = true;
        }

        return array('sucess' => $sucess, 'errors' => $errors);
    }

    public function deleteMerchantAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $reason = $data['reason'];
        /** @var Merchant $merchant */
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        $deletedMerchant = new MerchantDeleted();
        $deletedMerchant->setAddress($merchant->getAddress());
        $deletedMerchant->setCategory($merchant->getCategory());
        //$deletedMerchant->setCity($merchant->getCity());
        //$deletedMerchant->setCommunity($merchant->getCommunity());
        $deletedMerchant->setCodePostal($merchant->getCodePostal());
        $deletedMerchant->setDescription($merchant->getDescription());
        $deletedMerchant->setEmail($merchant->getEmail());
        $deletedMerchant->setEnabled($merchant->getEnabled());
        $deletedMerchant->setFriday($merchant->getFriday());
        $deletedMerchant->setFridayHour($merchant->getFridayHour());
        $deletedMerchant->setFridayHourEnd($merchant->getFridayHourEnd());
        $deletedMerchant->setImage($merchant->getImage());
        $deletedMerchant->setMonday($merchant->getMonday());
        $deletedMerchant->setMondayHour($merchant->getMondayHour());
        $deletedMerchant->setMondayHourEnd($merchant->getMondayHourEnd());
        $deletedMerchant->setName($merchant->getName());
        $deletedMerchant->setPhone($merchant->getPhone());
        $deletedMerchant->setSaturday($merchant->getSaturday());
        $deletedMerchant->setSaturdayHour($merchant->getSaturdayHour());
        $deletedMerchant->setSaturdayHourEnd($merchant->getSaturdayHourEnd());
        $deletedMerchant->setSiret($merchant->getSiret());
        $deletedMerchant->setSunday($merchant->getSunday());
        $deletedMerchant->setSundayHour($merchant->getSundayHour());
        $deletedMerchant->setSundayHourEnd($merchant->getSundayHourEnd());
        $deletedMerchant->setThursday($merchant->getThursday());
        $deletedMerchant->setThursdayHour($merchant->getThursdayHour());
        $deletedMerchant->setThursdayHourEnd($merchant->getThursdayHourEnd());
        $deletedMerchant->setTiming($merchant->getTiming());
        $deletedMerchant->setTuesday($merchant->getTuesday());
        $deletedMerchant->setThursdayHour($merchant->getTuesdayHour());
        $deletedMerchant->setTuesdayHourEnd($merchant->getTuesdayHourEnd());
        $deletedMerchant->setWednesday($merchant->getWednesday());
        $deletedMerchant->setWednesdayHour($merchant->getWednesdayHour());
        $deletedMerchant->setWednesdayHourEnd($merchant->getWednesdayHourEnd());
        $deletedMerchant->setReason($reason);

        $em->persist($deletedMerchant);
        $em->flush();
        // disable articles

        // disable events
        foreach ($merchant->getGoodPlans() as $event) {
            $event->setEnabled(false);
        }
        // delete comments
        foreach ($merchant->getComments() as $comment) {
            $em->remove($comment);
            $em->flush();
        }

        $em->remove($merchant);
        $em->flush();
        $subject = "NOUS Ensemble Un compte commerce / partenaire a été supprimé";
        $content = $this->container->get('templating')->render('AppBundle:Mail:removeAccount.html.twig', array(
            'entity' => $deletedMerchant,
            'type' => 'commerçant'
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
        $association = $em->getRepository("AppBundle:Merchant")->find($id);
        $inCommunity = false;


        foreach ($association->getCommunity()->getUsers() as $u) {
            if($u->getUser() == $user && $u->getType()== 'approved') {
                $inCommunity = true;
            }
        }

        if(!$inCommunity)
        {
            return array("error" => 'Vous n\'êtes pas lié à la communauté de ce commerce / partenaire');
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $phone = $data['phone'];

        foreach ($association->getUsers() as $u) {

            if($u->getUser() == $user ) {
                if($u->getType() == 'approved'){
                    return array("error" => 'Vous êtes déjà membre dans ce commerce  / partenaire');
                }else{
                    $u->setType('pending');
                    return array('success' => true);
                }

            }
        }
        $access = new MerchantUser();
        $access->setMerchant($association);
        $access->setUser($user);
        $access->setType('pending');
        $em->persist($access);
        $em->flush();
        $this->container->get('mail')->sendJoinMerchantOrAssociation($user, $association, $phone,'merchant');


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
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        /** @var AssociationUser $membership */
        $membership = $em->getRepository("AppBundle:MerchantUser")->findOneBy(array('merchant'=>$association,'user'=>$u));
        if(!$membership) {
            return array('error' => 'Cette demande d\'adhésion n\'existe pas');
        }
        if(!$merchant->getAdmins()->contains($user)){

            if ($merchant->getSuAdmin() != $user) {
                throw $this->createAccessDeniedException();
            }
        }
        $membership->setType('approved');
        $message = "Votre demande d'adhésion au commerce  " . $merchant->getName() . ' a été acceptée';
        $this->container->get('notification')->notify($u, 'merchant', $message, false, $merchant);
        $this->container->get('mobile')->pushNotification($u, 'NOUS-ENSEMBLE ', $message, false, false, 'off', false,  $merchant->getId());


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
        /** @var MerchantUser $association */
        $association = $em->getRepository("AppBundle:Merchant")->find($id);
        /** @var MerchantUser $membership */
        $membership = $em->getRepository("AppBundle:MerchantUser")->findOneBy(array('merchant'=>$association,'user'=>$u));
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
        /** @var Merchant $association */
        $association = $em->getRepository("AppBundle:Merchant")->find($id);
        /** @var MerchantUser $membership */
        $membership = $em->getRepository("AppBundle:MerchantUser")->findOneBy(array('merchant'=>$association,'user'=>$u));

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

        /** @var Merchant $association */
        $association = $em->getRepository("AppBundle:Merchant")->find($id);
        $demands = $em->getRepository("AppBundle:Merchant")->getMembershipDemands($association);
        $members = $em->getRepository("AppBundle:Merchant")->getMemberships($association);
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
