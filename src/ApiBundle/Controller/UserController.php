<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\AccessAdminCommunity;
use AppBundle\Entity\AdminAccess;
use AppBundle\Entity\ArticleHeading;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunitySetting;
use AppBundle\Entity\Number;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use AppBundle\Entity\File;
use AppBundle\Entity\DeviceToken;
use UserBundle\Entity\UserDeleted;
use AppBundle\Entity\CommunityUsers;
use AppBundle\Entity\NavigationLog;

class UserController extends Controller
{

    /**
     * @ApiDoc(resource="/api/user/login",
     * description="API user login check",
     * statusCodes={200="Successful"})
     */
    public function loginAction()
    {
        //override login jwt action, for doc
        throw new \RuntimeException();
    }

    /**
     * @ApiDoc(resource="/api/user/token/refresh",
     * description="API user refresh token",
     * statusCodes={200="Successful"})
     */
    public function refreshTokenAction()
    {
        //override api_user_refresh_token
        throw new \RuntimeException();
    }

    /**
     * @ApiDoc(resource="/api/user/logout",
     * description="API user logout",
     * statusCodes={200="Successful"})
     */
    public function logoutAction(Request $request)
    {
        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return array('success' => true);
    }

    /**
     * @ApiDoc(resource="/api/user/reset_password",
     * description="API user reset password",
     * statusCodes={200="Successful"})
     */
    public function lostPasswordAction(Request $request)
    {
        $email = $request->query->get('email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);
        if (null === $user) {
            return array("success" => false);
        }
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $password = implode($pass);
        $this->container->get('mail')->sendResettingMail($user, $password);

        $user->setPassword($password);
        $user->setPlainPassword($password);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->get('fos_user.user_manager')->updateUser($user);
        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/users",
     * description="API user register",
     * statusCodes={200="Successful"})
     */
    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $usernameExist = $this->get('fos_user.user_manager')->findUserByUsername($data['email']);
        if ($usernameExist) {
            return array("success" => false);
        }
        $emailExist = $this->get('fos_user.user_manager')->findUserByEmail($data['email']);
        if ($emailExist) {
            return array("success" => false);
        }
        $user = new User();

        $user->setEmail($data['email']);
        $user->addRole('ROLE_CITIZEN');
        $user->setUsername($data['email']);
        $user->setPassword($data['password']);
        $user->setPlainPassword($data['password']);
        $user->setEnabled(true);
        $firstname = strtolower($data['firstname']);
        $user->setFirstname($firstname);
        $lastname = strtolower($data['lastname']);
        $user->setLastname($lastname);
        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['residence_city']));
        $user->setCity($city);
        

        if (isset($data['image'])) {
            $image = new File();
            $image->base64($data['image']);
            $user->setImage($image);
        }
        $categories = $em->getRepository('AppBundle:Category')->findAll();
        foreach ($categories as $category) {
            $user->addInterest($category);
        }
        $em->persist($user);
        /** @var Community $community */
        $community = $em->getRepository('AppBundle:Community')->findOneById(19);
        if(!$community) {
            return array("success" => false);
        }
        $communityUser = new CommunityUsers();
        $communityUser  ->setCommunity($community);
        $communityUser  ->setUser($user);
        $communityUser  ->setFollow(true);
        $em->persist($communityUser);
        $em->flush();
        $this->container->get('mail')->sendConfirmationMail($user, $data['email'], $data['password']);

        if (!isset($data['api'])) {
            $data['api'] = '1';
        }

        $user->setApiVersion($data['api']);

        $token = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);

        return array("token" => $token, 'user' => $user);
    }
    /**
     * @ApiDoc(resource="/api/users/register/follow",
     * description="API user register and follow",
     * statusCodes={
     * {200="Successful"},
     * {412="email exists"},
     * {404="community don't exist"},
     * {406="wrong community password"},
     * {410="expired community"},
     * {409="database error"}
     * })
     */
    public function registerAndFollowAction(Request $request) {
        $em         =   $this->getDoctrine()->getManager();
        $datas      =   $request->getContent();
        $data       =   (array) json_decode($datas);
        $emailExist =   $this->get('fos_user.user_manager')->findUserByEmail($data['email']);
        if ($emailExist) {
            return array(
                "code"      =>  412,
                "message"   =>  "Cette adresse e-mail existe déjà"
            );
        }
        $user = new User();
        $city = $em->getRepository('AppBundle:City')->findOneByName($data['residence_city']);
        $user   ->setEmail($data['email'])
                ->addRole('ROLE_CITIZEN')
                ->setUsername($data['email'])
                ->setPassword($data['password'])
                ->setPlainPassword($data['password'])
                ->setEnabled(true)
                ->setFirstname(strtolower($data['firstname']))
                ->setLastname(strtolower($data['lastname']))
                ->setCity($city);
        
        if (isset($data['image'])) {
            $image = new File();
            $image->base64($data['image']);
            $user->setImage($image);
        }
        $categories = $em->getRepository('AppBundle:Category')->findAll();
        foreach ($categories as $category) {
            $user->addInterest($category);
        }
        $em->persist($user);
        if (!isset($data['api'])) {
            $data['api'] = '4';
        }
        $user->setApiVersion($data['api']);
        foreach($data['communities'] as $communityId) {
            /** @var Community $community */
            $community = $em->getRepository('AppBundle:Community')->find($communityId->id);
            if(!$community) {
                return array(
                    "code"      =>  404,
                    "message"   =>  "Cette communauté n'existe pas"
                );
            }
            $communityUser = new CommunityUsers();
            if($communityId->type == "private") {
                $password = $communityId->password;
                $now = new \DateTime();
                if(!($community->getPassword() == $password &&  $now < $community->getExpirationDate())) {       
                    if(!($community->getPassword() == $password)) {
                        return array(
                            "code"      =>  406,
                            "message"   =>  "Mot de passe communauté erroné"
                        );
                    } else if (!($now < $community->getExpirationDate())) {
                        return array(
                            "code"      =>  410,
                            "message"   =>  "Cette communauté est expiré"
                        );
                    }
                }
                $communityUser->setType('pending');
            }
            $communityUser  ->setCommunity($community);
            $communityUser  ->setUser($user);
            $communityUser  ->setFollow(true);
            $em->persist($communityUser);   
        }
        try {
            $em->flush();
        } catch(\Exception $exception) {
            return array(
                "code"      =>  409,
                "message"   =>  "Erreur lors de mises à jour dans la base de données"
            );
        }
        $this->container->get('mail')->sendConfirmationMail($user, $data['email'], $data['password']);
        $token = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);
        return array(
            "code"      =>  200,
            "message"   =>  "Utilisateur créé et lié aux communautés avec succès",
            "token"     =>  $token, 
            'user'      =>  $user
            );
    }    
    
    /**
     * @ApiDoc(resource="/api/community/add/follow",
     * description="API add and follow community",
      * statusCodes={
     * {200="Successful"},
     * {404="community don't exist"},
     * {412="relation exists"},
     * {406="wrong community password"},
     * {410="expired community"},
     * {409="database error"}
     * })
     */

    public function addAndFollowCommunitiesAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        foreach($data['communities'] as $communityId) {
            $community = $em->getRepository('AppBundle:Community')->find($communityId->id);
            if(!$community) {   
                return array(
                    "code"      =>  404,
                    "message"   =>  "Cette communauté n'existe pas"
                );
            }
            /** @var CommunityUsers $exist */
            $exist = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('community'=> $community, 'user'=>$user));
                    
            if($exist) { 
                $exist->setFollow(true);
                try {
                    $em->flush();
                } catch(\Exception $exception) {
                    return array(
                        "code"      =>  409,
                        "message"   =>  "Erreur lors de mises à jour dans la base de données"
                    );
                }
                return array(
                    "code"      =>  412,
                    "message"   =>  "Cette communauté est déjà liée à cet utilisateur"
                );
            }
            $communityUser = new CommunityUsers();
            if($communityId->type == "private") {
                /*** add private community */
                $password = $communityId->password;
                $now = new \DateTime();
                if(!($community->getPassword() == $password &&  $now < $community->getExpirationDate())) {       
                    if(!($community->getPassword() == $password)) {
                        return array(
                            "code"      =>  406,
                            "message"   =>  "Mot de passe communauté erroné"
                        );
                    } else if (!($now < $community->getExpirationDate())) {
                        return array(
                            "code"      =>  410,
                            "message"   =>  "Cette communauté est expiré"
                        );
                    }
                }
                $communityUser->setType('pending');
            }
            $communityUser->setCommunity($community);
            $communityUser->setUser($user);
            $communityUser->setFollow(true);
            $em->persist($communityUser);
        }
        try {
            $em->flush();
        } catch(\Exception $exception) {
            return array(
                "code"      =>  409,
                "message"   =>  "Erreur lors de mises à jour dans la base de données"
            );
        }
        return array(
            "code"      =>  200,
            "message"   =>  "Utilisateur lié aux communautés avec succès"
        );
    }
    
    /**
     * @ApiDoc(resource="/api/user/deviceToken",
     * description="Ce webservice permet de définir le token device de l'utilisateur.",
     * statusCodes={200="Successful"})
     */
    public function deviceTokenAction(Request $request)
    {
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $this->getUser();
        $exist = $em->getRepository('AppBundle:DeviceToken')->findOneBy(array('deviceToken' => $data['deviceToken']));
        if ($exist) {
            return array("success" => false);
        } else if($data['deviceToken']) {
            $deviceToken = new DeviceToken();
            $deviceToken->setDeviceToken($data['deviceToken']);
            $deviceToken->setType($data['deviceType']);
            $em->persist($deviceToken);
            $user->addDeviceToken($deviceToken);
            $em->persist($user);
            $em->flush();
        }else{
            return array("success" => false);
        }
        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/user/deviceToken",
     * description="Ce webservice permet de supprimer le token device de l'utilisateur.",
     * statusCodes={200="Successful"})
     */
    public function deleteDeviceTokenAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $token = $data['deviceToken'];

        /*$devicetoken = $em->getRepository('AppBundle:DeviceToken')->findOneBy(array('deviceToken' => $token));
        if($devicetoken) {
            $em->remove($devicetoken);
            $em->flush();
        }*/

        $devicetokens = $em->getRepository('AppBundle:DeviceToken')->findBy(array('user' => $user));
        foreach ($devicetokens as $devicetoken) {
            if ($devicetoken) {
                $user->removeDeviceToken($devicetoken);
                $em->flush();
            }
        }

        return array('statut' => 'OK');
    }

    /**
     * @ApiDoc(resource="/api/user",
     * description="API show user ",
     * statusCodes={200="Successful"})
     */
    public function showAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if ($user->getImage()) {
            $path = $helper->asset($user->getImage(), 'file');
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            if ($path) {
                $user->setImageURL($baseurl . $path);
            }
        }

        $countUnread = $em->getRepository('AppBundle:Notification')->countUnreadNotifications($user);
        $user->setCountUnread($countUnread);
        if ($user->hasRole('ROLE_COMMUNITY_SU_ADMIN')) {
            $user->setRole('community_su_admin');
        } else {
            $user->setRole('citizen');
        }

        return $user;
    }
    
    /**
     * @ApiDoc(resource="/api/user/communities/followed",
     * description="API get followed communities ",
     * statusCodes={200="Successful"})
     */
    public function communitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $communities = $em->getRepository('AppBundle:Community')->findUserFollowedCommunities($user);
        $communitiesList = '';

        foreach ($communities as $key => $community) {
            /** @var Community $one */

            $communitiesList = $communitiesList . $community['name'] . ',';
            $one = $em->getRepository('AppBundle:Community')->find($community["id"]);
            $communities[$key]['categories'] = $one->getCategories();

        }

        $communitiesList = rtrim($communitiesList, ",");
        $this->logVisit($user, $communitiesList);

        return $communities;
    }

    /**
     * @ApiDoc(resource="/api/user/communities",
     * description="API get communities ",
     * statusCodes={200="Successful"})
     */
    public function userCommunitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $this->getUser();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $communities = $em->getRepository('AppBundle:Community')->findUserCommunities($user);

        foreach ($communities as $key => $community) {
            /** @var Community $one */
            $one = $em->getRepository('AppBundle:Community')->find($community["id"]);
            $communities[$key]['type']='community';

            if ($one->getEnabled()) {
                $communities[$key]['moderate'] = 'accepted';
            }
            $communities[$key]['categories'] = $one->getCategories();
            if ($one->getImage() != null) {
                $communities[$key]['img_url'] = $request->getScheme() . '://' .$request->getHttpHost() . $request->getBasePath() . $helper->asset($one->getImage(), 'file');
            } else {
                $communities[$key]['img_url'] = $request->getScheme() . '://' .$request->getHttpHost() .$this->container->get('assets.packages')->getUrl('bundles/app/images/user_default.png');
            }
            if ( $user->isCommunitySuAdmin($one) || $user->isCommunityAdmin($one)) {

                $communities[$key]['isAdminPublish'] = true;
            } else{

                $communities[$key]['isAdminPublish'] = false;
            }
            $hasSurveyRight = false;
            if($user->isCommunityAdmin($one)) {
                /** @var AccessAdminCommunity[] $accesses */
                $accesses = $user->getAccess();
                foreach ($accesses as $access) {
                    if($access->getAccess()->getSlug() == 'survey_create' && $access->getCommunity() === $one){
                        $hasSurveyRight = true;
                    }
                }
            }


            if ($one->hasSetting('activate_survey') && ($hasSurveyRight || $user->isCommunitySuAdmin($one))){
                $communities[$key]['activate_survey'] = true;
            }
            else{
                $communities[$key]['activate_survey'] = false;
            }

            $communities[$key]['volunteers'] =  $em->getRepository("AppBundle:EventVolunteer")->findVolunteers('community', $one);


            /** @var CommunitySetting[] $settings */
            $settings= $one->getSettings();
            $comment_allowed = false;
            if(count($settings) != 0){
                foreach($settings as $setting)
                {
                    if($setting->getSlug()=='activate_comments')
                    {
                        $comment_allowed = true;
                    }
                }
            }
            $communities[$key]['comment_allowed'] = $comment_allowed;
            $communities[$key]['comment_article_heading_allowed'] = $one->getIsCommentActive();
            /** @var ArticleHeading[] $articleHeadings */
            $articleHeadings = $one->getArticleHeadings();
            if (count($articleHeadings) != 0 ){
                foreach ($articleHeadings as $articleHeading ){
                    if ($articleHeading->getEnabled() && ($articleHeading->getEmailAdmin() == $user->getEmail() || $user->isCommunitySuAdmin($one) || $articleHeading->getAdmins()->contains($user)) ) {
                        $communities[$key]['articleHeading'][] = array('id'=>$articleHeading->getId(),'title'=>$articleHeading->getTitle());
                    }
                }
            }
            $communities[$key]['activate_articles'] = ($one->hasSetting('activate_articles') && ($user->isCommunitySuAdmin($one) || isset($communities[$key]['articleHeading'])));
        }


        return $communities;
    }
    
    /**
     * @ApiDoc(resource="/api/user/associations",
     * description="API get associations ",
     * statusCodes={200="Successful"})
     */
    public function associationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        $associations = $em->getRepository('AppBundle:Association')->findUserAssociations($user);
        $assos = [];
        foreach ($associations as $association) {
            if ($association->getImage()) {
                $path = $helper->asset($association->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $association->setImageURL($baseurl . $path);
                }
            }
            if ($association->getSuAdmin() == $user) {
                $association->setRole('superadmin');
            } else {
                $association->setRole('admin');
            }
            // To get Community withtout expose everything and still get an object.for image and role comparaison
            $assos[] = $em->getRepository('AppBundle:Association')->formatAssocition($association,$association->getRole(),$em,true);
        }
        return $assos;
    }

    /**
     * @ApiDoc(resource="/api/user/adherentAsso",
     * description="API get associations adherent ",
     * statusCodes={200="Successful"})
     */
    public function adherentAssoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        $associations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
        $assos =[];
        foreach ($associations as $association) {
            if ($association->getImage()) {
                $path = $helper->asset($association->getImage(), 'file');
                $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                if ($path) {
                    $association->setImageURL($baseurl.$path);
                }
            }
            // To get Community withtout expose everything and still get an object.for image and role comparaison
            $assos[] = $em->getRepository('AppBundle:Association')->formatAssocition($association,'member',$em);
        }
        return $assos;
    }

    /**
     * @ApiDoc(resource="/api/user/adherentMerchant",
     * description="API get merchant adherent ",
     * statusCodes={200="Successful"})
     */
    public function adherentMerchantAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        $merchants = $em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
        $merchs =[];
        foreach ($merchants as $merchant) {
            if ($merchant->getImage()) {
                $path = $helper->asset($merchant->getImage(), 'file');
                $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                if ($path) {
                    $merchant->setImageURL($baseurl.$path);
                }
            }
            // To get Community withtout expose everything and still get an object.for image and role comparaison
            $merchs[] = $em->getRepository('AppBundle:Merchant')->formatMerchant($merchant,'member');
        }
        return $merchs;
    }

    /**
     * @ApiDoc(resource="/api/user/merchants",
     * description="API get merchants ",
     * statusCodes={200="Successful"})
     */
    public function merchantsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        $merchants = $em->getRepository('AppBundle:Merchant')->findUserMerchants($user);
        $merchs = [];
        foreach ($merchants as $merchant) {
            if ($merchant->getImage()) {
                $path = $helper->asset($merchant->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $merchant->setImageURL($baseurl . $path);
                }
            }
            if ($merchant->getSuAdmin() == $user) {
                $merchant->setRole('superadmin');
            } else {
                $merchant->setRole('admin');
            }
            // To get Community withtout expose everything and still get an object.for image and role comparaison
            $merchs[] = $em->getRepository('AppBundle:Merchant')->formatMerchant($merchant,$merchant->getRole());

        }
        return $merchs;
    }

    /**
     * @ApiDoc(resource="/api/user/profile/secondarycity",
     * description="API add secondary city ",
     * statusCodes={200="Successful"})
     */
    public function addCityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
        if (!$city) {
            $city = $em->getRepository('AppBundle:City')->find($data['city']);
        }
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $exist = $user->getSecondaryCities()->contains($city);
        if ($exist) {
            return array('error' => $city->getName() . ' est déjà une ville secondaire.');
        }
        if ($city == $user->getCity()) {
            return array('error' => $city->getName() . ' est votre ville principale.');
        }
        $user->addSecondaryCity($city);
        $em->flush();
        return array('success' => true, 'user' => $user);
    }

    /**
     * @ApiDoc(resource="/api/user/secondarycity/remove",
     * description="API remove secondary city ",
     * statusCodes={200="Successful"})
     */
    public function removeCityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
        if (!$city) {
            $city = $em->getRepository('AppBundle:City')->find($data['city']);
        }
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $user->removeSecondaryCity($city);
        $em->flush();
        return array('cities' => $user->getSecondaryCities(), 'user' => $user);
    }

    /**
     * @ApiDoc(resource="/api/user/profile/days",
     * description="API edit user days ",
     * statusCodes={200="Successful"})
     */
    public function daysAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        switch ($data['day']) {
            case 'monday':
                if ($data['action'] == 'add') {
                    $user->setMonday(true);
                } else {
                    $user->setMonday(false);
                }
                break;
            case 'tuesday':
                if ($data['action'] == 'add') {
                    $user->setTuesday(true);
                } else {
                    $user->setTuesday(false);
                }

                break;
            case 'wednesday':
                if ($data['action'] == 'add') {
                    $user->setWednesday(true);
                } else {
                    $user->setWednesday(false);
                }

                break;
            case 'thursday':
                if ($data['action'] == 'add') {
                    $user->setThursday(true);
                } else {
                    $user->setThursday(false);
                }

                break;
            case 'friday':
                if ($data['action'] == 'add') {
                    $user->setFriday(true);
                } else {
                    $user->setFriday(false);
                }

                break;
            case 'saturday':
                if ($data['action'] == 'add') {
                    $user->setSaturday(true);
                } else {
                    $user->setSaturday(false);
                }

                break;
            case 'sunday':
                if ($data['action'] == 'add') {
                    $user->setSunday(true);
                } else {
                    $user->setSunday(false);
                }

                break;
        }
        $em->flush();
        return array('success' => true , 'user' => $user);
    }

    /**
     * @ApiDoc(resource="/api/user/profile/childrens",
     * description="API add user childrens ",
     * statusCodes={200="Successful"})
     */
    public function childrensAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        switch ($data['children']) {
            case 'lessThanSix':
                if ($data['action'] == 'add') {
                    $user->setLessThanSix(true);
                } else {
                    $user->setLessThanSix(false);
                }
                break;
            case 'betweenSixTwelve':
                if ($data['action'] == 'add') {
                    $user->setBetweenSixTwelve(true);
                } else {
                    $user->setBetweenSixTwelve(false);
                }

                break;
            case 'betweenTwelveEighteen':
                if ($data['action'] == 'add') {
                    $user->setBetweenTwelveEighteen(true);
                } else {
                    $user->setBetweenTwelveEighteen(false);
                }

                break;
            case 'noChildrens':
                $user->setLessThanSix(false);
                $user->setBetweenTwelveEighteen(false);
                $user->setBetweenSixTwelve(false);

                break;
        }
        $em->flush();
        return array('success' => true, 'user' => $user);
    }

    /**
     * @ApiDoc(resource="/api/user/profile/interests",
     * description="API edit user interests ",
     * statusCodes={200="Successful"})
     */
    public function interestsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $interest = $em->getRepository('AppBundle:Category')->findOneBy(array("name" => $data['interest']));
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if ($data['action'] == 'add') {
            $user->addInterest($interest);
        } else {
            $user->removeInterest($interest);
        }
        $em->flush();
        return array('success' => true, 'user' => $user);
    }

    /**
     * @ApiDoc(resource="/api/user/password",
     * description="API edit password ",
     * statusCodes={200="Successful"})
     */
    public function passwordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $salt = $user->getSalt();

        if (!$encoder->isPasswordValid($user->getPassword(), $data['actual_password'], $salt)) {
            return array('success' => false);
        }
        $user->setPassword($data['password']);
        $user->setPlainPassword($data['password']);

        $em->flush();
        return array('success' => true);
    }

    /**
     * @ApiDoc(resource="/api/user/notifications",
     * description="API get user's notifications ",
     * statusCodes={200="Successful"})
     */
    public function notificationsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        //$notifications = $user->getNotifications();
        $notifEvent = $em->getRepository('AppBundle:Notification')->findUnreadEventNotification($user);
        $notifArticle = $em->getRepository('AppBundle:Notification')->findUnreadArticleNotification($user);
        $notifComment = $em->getRepository('AppBundle:Notification')->findUnreadCommentNotification($user);
        $notifAssociation = $em->getRepository('AppBundle:Notification')->findAssociationCommentNotification($user);
        $notifMerchant = $em->getRepository('AppBundle:Notification')->findMerchantCommentNotification($user);
        $notifAdmin = $em->getRepository('AppBundle:Notification')->findAdminsNotification($user);

        $notifications = array_merge($notifEvent, $notifArticle, $notifComment, $notifAssociation, $notifMerchant, $notifAdmin);
        usort($notifications, function ($a, $b) {
            if ($a['createAt'] == $b['createAt']) {
                return 0;
            } else {
                return ($a['createAt'] < $b['createAt']) ? 1 : -1;
            }
        });

        $tagged = array();
        foreach ($notifications as $notification) {
            if ($notification['seen'] == false) {
                $notification['tag'] = 'new';
            }
            $tagged[] = $notification;
        }

        $unread = $em->getRepository('AppBundle:Notification')->findUnreadNotifications($user);

        foreach ($unread as $notification) {
            $notification->setSeen(true);
            $notification->setTag('new');
        }
        $em->flush();


        $page = $request->query->get('start');
        $limit = $request->query->get('limit');

        $offset = ($page - 1) * $limit;
        $pagination = array_slice($tagged, $offset, $limit);
        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/user/notifications/participation",
     * description="API get user's notifications ",
     * statusCodes={200="Successful"})
     */
    public function participationNotifAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        //$notifications = $user->getNotifications();
        $notifications = $em->getRepository('AppBundle:Notification')->findParticipationNotification($user);

        usort($notifications, function ($a, $b) {
            if ($a['createAt'] == $b['createAt']) {
                return 0;
            } else {
                return ($a['createAt'] < $b['createAt']) ? 1 : -1;
            }
        });

        return $notifications;
    }
    
    /**
     * Ce web service de mettre à jour la photo de profile de l'utilisateur
     *
     * @param Request $request
     * @return type
     */
    public function saveUserImageAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        if (isset($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $user->setImage($image);
        }
        $em->flush();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        if ($user->getImage()) {
            $path = $helper->asset($user->getImage(), 'file');
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            if ($path) {
                $user->setImageURL($baseurl . $path);
            }
        }
        return array('success' => true, "image" => $user->getImageURL());
    }

    /**
     * @ApiDoc(resource="/api/user/edit",
     * description="API edit user's informations ",
     * statusCodes={200="Successful"})
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $exist = $this->get('fos_user.user_manager')->findUserByUsername($data['email']);
        if ($exist && $exist != $user) {
            return array("success" => false, 'user' => $user);
        }

        $user->setBirthDate($data['birth']);
        $user->setPhone($data['phone']);
        $user->setEmail($data['email']);
        $user->setUsername($data['email']);
        if($data['city']){
        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
        if (!$city) {
            return array('error' => 'Impossible de trouver la ville correspondante', 'message' => 'Impossible de trouver la ville correspondante');
        }

            $user->setCity($city);
        }

        $em->flush();
        if ($user->getImage()) {
            $path = $helper->asset($user->getImage(), 'file');
            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
            if ($path) {
                $user->setImageURL($baseurl . $path);
            }
        }

        return array('success' => true, 'user' => $user);
    }

    /**
     * @ApiDoc(resource="/api/user/number/{category}",
     * description="API permet de récupérer les numéros utiles de la communauté de l'utilisateur ",
     * statusCodes={200="Successful"})
     */
    public function numbersAction(Request $request,$category)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }


        //$numberCategory = $em->getRepository('AppBundle:PhoneBookHeading')->find($category);
        /** @var Number[] $numbers */
        $numbers = $em->getRepository("AppBundle:Number")->findBy(array('categoryPhoneBookHeading' => $category), array('title' => 'ASC'));


        $output = array();
        foreach ($numbers as $key=>$number) {

            $phone = $number->getPhone();
            $output[$key]['id'] = $number->getId();
            $output[$key]['title'] = $number->getTitle();
            $output[$key]['phone'] = $number->getPhone();
            $output[$key]['description'] = $number->getDescription();
            $output[$key]['category_phonebookHeading'] = $number->getCategoryPhoneBookHeading();


            $phones = preg_split('/\r\n|[\r\n]/', $phone);
            $output[$key]['phone_list'] = $phones;

            $number->setPhoneList($phones);
            if($number->getDocument()) {
                $doc = $em->getRepository("AppBundle:File")->find($number->getDocument()->getId());
                if ($doc) {
                    $path = $helper->asset($doc, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {

                        $output[$key]['document_url'] =$baseurl . $path;
                    }
                }
            }
            //$output[] = $number;
        }
        /*dump($output);
        exit;*/
        return $output;
    }

    /**
     * @ApiDoc(resource="/api/user/invitation",
     * description="API add association admin",
     * statusCodes={200="Successful"})
     */
    public function invitationAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $this->container->get('mail')->sendInvitationMail($data['email'], $this->getUser());

        return array("success" => true);
    }

    public function mapInterestAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $categories = $request->query->get('categories');
        $interests = $em->getRepository("AppBundle:Interest")->findUserInterests($this->getUser(), $categories);
        foreach ($interests as $value) {
            if ($value->getImage()) {
                $path = $helper->asset($value->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                $value->setImageURL($baseurl . $path);
            }
        }
        return $interests;
    }

    /**
     * * @ApiDoc(resource="/api/map/work",
     * description="Ce webservice permet d'afficher les travaux sur la carte pratique",
     * statusCodes={200="Successful"})
     * @param integer $id
     * @return array
     */
    public function workAction($id)
    {

        $em = $this->getDoctrine()->getManager();

        $works = $em->getRepository('AppBundle:Work')->findBy(array('enabled' => true,'mapHeading'=>$id));

        return $works;
    }

    /**
     * @ApiDoc(resource="/api/categories",
     * description="API get categories",
     * statusCodes={200="Successful"})
     */
    public function categoriesAction()
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("AppBundle:Category")->findCitizenCategories();
        return $categories;
    }

    public function userCategoriesAction()
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("AppBundle:Category")->findCatByUser($user);
        return $categories;
    }



    public function advancedSearchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $allArticles = array();
        $allEvents = array();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $cities = $data["cities"];
        $key = $data["key"];
        $theme = $data["theme"];
        if (isset($data["accounts"]) && $data["accounts"]) {
            $result['associations'] = $em->getRepository("AppBundle:Association")->appSearch($user, $key, $theme, $cities);
            $associationsItems = array();
            foreach ($result['associations'] as $association) {
                if (isset($association['image'])) {
                    $image = $em->getRepository("AppBundle:File")->find($association['image']);
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $association['imageURL'] = $baseurl . $path;
                        }
                    }
                } else {
                    $association["imageURL"] = "assets/img/user_default.png";
                }
                $associationsItems[] = $association;
            }

            $result["associations"] = $associationsItems;

            $result['merchants'] = $em->getRepository("AppBundle:Merchant")->appSearch($user, $key, $theme, $cities);
            $merchantsItems = array();
            foreach ($result['merchants'] as $merchant) {
                if (isset($merchant['image'])) {
                    $image = $em->getRepository("AppBundle:File")->find($merchant['image']);
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $merchant['imageURL'] = $baseurl . $path;
                        }
                    }
                } else {
                    $merchant["imageURL"] = "assets/img/user_default.png";
                }
                $merchantsItems[] = $merchant;
            }
            $result["merchants"] = $merchantsItems;
        }

        if (isset($data["articles"]) && $data["articles"]) {
            $allArticles = $em->getRepository("AppBundle:Article")->appSearchByTitleCreator($user, $key, $theme, $cities);
            if (empty($allArticles)) {
                $result['articles'] = $em->getRepository("AppBundle:Article")->appSearchByDescription($user, $key, $theme, $cities);
            } else {
                $result['articles'] = $allArticles;
            }
            $articlesItems = array();
            foreach ($result['articles'] as $article) {
                if (isset($article['image'])) {
                    $image = $em->getRepository("AppBundle:File")->find($article['image']);
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $article['imageURL'] = $baseurl . $path;
                        }
                    }
                } else {
                    $article["imageURL"] = "assets/img/user_default.png";
                }

                if($article['type'] != 'community') {
                    $article['addComments'] = true;
                }
                $articlesItems[] = $article;
            }
            $result["articles"] = $articlesItems;
        }

        if (isset($data["events"]) && $data["events"]) {
            $allEvents = $em->getRepository("AppBundle:Event")->appSearchByTitleCreator($user, $key, $theme, $cities);
            if (empty($allEvents)) {
                $result['events'] = $em->getRepository("AppBundle:Event")->appSearchByDescription($user, $key, $theme, $cities);
            } else {
                $result['events'] = $allEvents;
            }
            $eventsItems = array();
            foreach ($result['events'] as $event) {
                if (isset($event['image'])) {
                    $image = $em->getRepository("AppBundle:File")->find($event['image']);
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['imageURL'] = $baseurl . $path;
                        }
                    }
                } else {
                    $event["imageURL"] = "assets/img/user_default.png";
                }
                $eventsItems[] = $event;
            }
            $result["events"] = $eventsItems;
        }

        if (isset($data["goodplans"]) && $data["goodplans"]) {
            $allEvents = $em->getRepository("AppBundle:GoodPlan")->appSearchByTitleCreator($user, $key, $theme, $cities);
            if (empty($allEvents)) {
                $result['goodplans'] = $em->getRepository("AppBundle:GoodPlan")->appSearchByDescription($user, $key, $theme, $cities);
            } else {
                $result['goodplans'] = $allEvents;
            }
            $eventsItems = array();
            foreach ($result['goodplans'] as $event) {
                if (isset($event['image'])) {
                    $image = $em->getRepository("AppBundle:File")->find($event['image']);
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['imageURL'] = $baseurl . $path;
                        }
                    }
                } else {
                    $event["imageURL"] = "assets/img/user_default.png";
                }
                $eventsItems[] = $event;
            }
            $result["goodplans"] = $eventsItems;
        }

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/associations/merchants",
     * description="API get les associations et les commerçants dont l'utilisateur est super administrateur",
     * statusCodes={200="Successful"})
     */
    public function associationsMerchantsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $result = array();
        $associations = $em->getRepository("AppBundle:Association")->findBySuAdmin($user);
        foreach ($associations as $association) {
            if (isset($association['image'])) {
                $image = $em->getRepository("AppBundle:File")->find($association['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $association['imageURL'] = $baseurl . $path;
                    }
                }
            }
            $result['associations'][] = $association;
        }
        $merchants = $em->getRepository("AppBundle:Merchant")->findBySuAdmin($user);
        foreach ($merchants as $merchant) {
            if (isset($merchant['image'])) {
                $image = $em->getRepository("AppBundle:File")->find($merchant['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $merchant['imageURL'] = $baseurl . $path;
                    }
                }
            }
            $result['merchants'][] = $merchant;
        }

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/associations/merchants/save/superadmin",
     * description="API permet d'enregistrer les super admin des associations et des commercants de l'utilisateur",
     * statusCodes={200="Successful"})
     */
    public function saveAssociationsMerchantsSuperAdminAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $errors = array();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $items = $data["items"];
        $saved = array();
        foreach ($items as $item) {
            $suAdmin = $em->getRepository('UserBundle:User')->findOneByEmail($item->suAdmin);
            if (!$suAdmin) {
                $errors[] = "- " . $item->suAdmin . " n'est pas un utilisateur NOUS-Ensemble";
            } else {
                if ($item->type == 'association') {
                    $association = $em->getRepository('AppBundle:Association')->find($item->id);
                    $association->setSuAdmin($suAdmin);
                    $saved[] = $item->id;
                } elseif ($item->type == 'merchant') {
                    $merchant = $em->getRepository('AppBundle:Merchant')->find($item->id);
                    $merchant->setSuAdmin($suAdmin);
                    $saved[] = $item->id;
                }
            }
        }
        $em->flush();
        if (sizeof($errors) > 0) {
            $result = array("sucess" => false, "errors" => array_unique($errors), "saved" => $saved);
        } else {
            $result = array("sucess" => true);
        }
        return $result;
    }

    public function deleteMyAccountAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $reason = $data['reason'];
        $userDeleted = new UserDeleted();

        $userDeleted->setBirthDate($user->getBirthDate());

        $userDeleted->setCivility($user->getCivility());
        $userDeleted->setFirstname($user->getFirstname());

        $userDeleted->setImage($user->getImage());
        $userDeleted->setLastname($user->getLastname());

        $userDeleted->setPhone($user->getPhone());

        $userDeleted->setReason($reason);
        $em->persist($userDeleted);
        $em->flush();
        // desactiver articles
        foreach ($user->getArticles() as $article) {
            $article->setEnabled(false);
        }

        // supprimer commentaire
        foreach ($user->getComments() as $comment) {
            $em->remove($comment);
            $em->flush();
        }
        // supprimer les mention j'aime sur les articles
        $articlesLikes = $em->getRepository('AppBundle:ArticleLikes')->findByUser($user);
        foreach ($articlesLikes as $articleLike) {
            $em->remove($articleLike);
            $em->flush();
        }

        // supprimer la mention benvoles
        foreach ($user->getEventVolunteer() as $eventVolunteer) {
            $em->remove($eventVolunteer);
            $em->flush();
        }

        $em->remove($user);
        $em->flush();
        $subject = "NOUS Ensemble Un compte citoyen a été supprimé";
        $content = $this->container->get('templating')->render('AppBundle:Mail:removeCitzenAccount.html.twig', array(
            'entity' => $userDeleted,
            'type' => 'citoyen'
        ));
        $this->container->get('mail')->deleteAccountMail($subject, $content);
        return array('succes' => true);
    }
    
    
    /**
     * @ApiDoc(resource="/api/community/public/add",
     * description="API lier un utilisateur à des communautés publiques",
     * statusCodes={200="Successful"})
     */

    public function addPublicCommunitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        foreach($data['communities'] as $communityId)
        {
            $community = $em->getRepository('AppBundle:Community')->find($communityId);
            $exist = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('community'=> $community, 'user'=>$user));
            if(!$exist)
            {
                $communityUser = new CommunityUsers();
                $communityUser->setCommunity($community);
                $communityUser->setUser($user);
                $communityUser->setFollow(false);
                $em->persist($communityUser);      
            }
        }
        $em->flush();
            
        return array('succes' => true);
    }
    
    /**
     * @ApiDoc(resource="/api/community/private/add",
     * description="API lier un utilisateur à des communautés privés",
     * statusCodes={200="Successful"})
     */

    public function addPrivateCommunitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $communityId = $data['community'];

        /** @var Community $community */
        $community = $em->getRepository('AppBundle:Community')->find($communityId);
        $password = $data['password'];
        $now = new \DateTime();
        if($community->getPassword() == $password &&  $now < $community->getExpirationDate())
        {
            /** @var CommunityUsers $exist */
            $exist = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('community'=> $community, 'user'=>$user));
            if(!$exist)
            {
                $communityUser = new CommunityUsers();
                $communityUser->setCommunity($community);
                $communityUser->setUser($user);
                $communityUser->setFollow(false);
                $em->persist($communityUser);      
            }else{
                $exist->setType('approved');
            }
            $em->flush();      
            return array('success' => true);
        }
    return array('success' => false);

    }
    
    /**
     * @ApiDoc(resource="/api/community/password",
     * description="API demande le mot de passe d'une communauté privée",
     * statusCodes={200="Successful"})
     */

    public function passwordRequestAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $communityId = $data['community'];
        /** @var Community $community */
        $community = $em->getRepository('AppBundle:Community')->find($communityId);
        $phone = $data['phone'];
        $user->setPhone($phone);
        /** @var CommunityUsers $exist */
        $exist = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('community' => $community, 'user' => $user));
        if (!$exist) {
            $communityUser = new CommunityUsers();
            $communityUser->setCommunity($community);
            $communityUser->setUser($user);
            $communityUser->setFollow(false);
            $communityUser->setType('pending');
            $em->persist($communityUser);
            $adminsCommu = $community->getCommunityAdmins();
            $sent = 0;
            foreach ($adminsCommu as $adminCommu){
                if ($adminCommu->hasRight('user_aprove',$community)) {
                    $this->container->get('mail')->sendJoinPrivateCommunity($adminCommu->getEmail(), $this->getUser(),
                        $community);
                    $sent++;
                }
            }
            if ($sent == 0){
                $suadminsCommu = $community->getCommunitySuadmins();
                foreach ($suadminsCommu as $v){
                    $this->container->get('mail')->sendJoinPrivateCommunity($v->getEmail(), $this->getUser(),$community);

                }
            }
        }
        $em->flush();

        return array('succes' => true);
    }
    
    /**
     * @ApiDoc(resource="/api/communities/private",
     * description="API get private communities",
     * statusCodes={200="Successful"})
     */
    public function privateCommunitiesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $allCommunities = $em->getRepository("AppBundle:Community")->findAllPrivateCommunities();
        $communities = $em->getRepository("AppBundle:Community")->findAllUserPrivateCommunities($user);
        if(!empty($communities)){
            $result = array();
            foreach($allCommunities as $community){

                if( !in_array($community, $communities)){
                    $result[] = $community;
                }
            }
            return $result;
        }else{
            return $allCommunities;
        }
    }
    
    /**
     * @ApiDoc(resource="/api/communities/public",
     * description="API get private communities",
     * statusCodes={200="Successful"})
     */
    public function publicCommunitiesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $allCommunities = $em->getRepository("AppBundle:Community")->findAllPublicCommunities();
        $communities = $em->getRepository("AppBundle:Community")->findAllUserPublicCommunities($user);
        if(!empty($communities)){
        $result = array();
            foreach($allCommunities as $community){

                if( !in_array($community, $communities)){
                    $result[] = $community;
                }
            }
            return $result;
        }else{
            return $allCommunities;
        }
    }

    /**
     * @ApiDoc(resource="/api/community/follow",
     * description="API suivre des communautés",
     * statusCodes={200="Successful"})
     */

    public function followCommunitiesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        /** @var CommunityUsers[] $userCommunities */
        $userCommunities = $em->getRepository('AppBundle:CommunityUsers')->findBy(array('user'=>$user));
        foreach ($userCommunities as $userCommunity){
            $userCommunity->setFollow(false);
        }
        $em->flush();

        foreach($data['communities'] as $communityId)
        {
            /** @var Community $community */
            $community = $em->getRepository('AppBundle:Community')->find($communityId->id);
            /** @var CommunityUsers $communityUser */
            $communityUser= $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('community'=>$community, 'user'=>$user));
            $communityUser->setFollow(true);
        }
        $em->flush();

        return array('succes' => true);
    }

    /**
     * @ApiDoc(resource="/api/community/unfollow",
     * description="API ne plus suivre des communautés",
     * statusCodes={200="Successful"})
     */

    public function unfollowAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $communityId = $data['community'];
        /** @var Community $community */
        $community = $em->getRepository('AppBundle:Community')->find($communityId);
        /** @var CommunityUsers $userCommunitiy */
        $userCommunitiy = $em->getRepository('AppBundle:CommunityUsers')->findOneBy(array('community'=>$community, 'user'=>$user));
        if($userCommunitiy){
            $em->remove($userCommunitiy);
            $em->flush();
        }


        return array('succes' => true);
    }

    /**
     * @ApiDoc(resource="/api/user/map/location/{id}",
     * description="API get Community Location",
     * statusCodes={200="Successful"})
     */
    public function communityLocationByMapHeadingAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $location = $em->getRepository('AppBundle:MapHeading')->findCommunityLocationByMapHeading($id);
        return $location;

    }

    private function logVisit($user, $communities)
    {
        $em = $this->getDoctrine()->getManager();
        $existLog = $em->getRepository('AppBundle:NavigationLog')->findOneBy(
            ['userId' => $user->getId()],
            ['id' => 'DESC']
        );

        if ($existLog ) {
            $logDate = $existLog->getDate();
            $now = new \DateTime('now');
            $dteDiff  = $logDate->diff($now);
            $minutes = (int)$dteDiff->format("%I");
            $days = (int)$dteDiff->format("%D");

            if ($days === 0 && $minutes <= 10) {
                return;
            }
        }

        $log = new NavigationLog();
        $log->setUserId($user->getId())
            ->setUserFirstname($user->getFirstname())
            ->setUserLastName($user->getLastname())
            ->setCommunities($communities)
            ->setDate(new \DateTime('now'));

        $em->persist($log);
        $em->flush();
    }
}
