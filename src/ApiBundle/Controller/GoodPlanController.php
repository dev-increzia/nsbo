<?php

namespace ApiBundle\Controller;

use AppBundle\Repository\GoodPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\File;
use AppBundle\Entity\Notification;
use AppBundle\Entity\EventVolunteer;
use AppBundle\Entity\Push;

class GoodPlanController extends Controller
{


    /**
     * @ApiDoc(resource="/api/goodplan/{type}/{id}/new",
     * description="Ce webservice permet d'ajouter un bon plan.",
     * statusCodes={200="Successful"})
     */
    public function newAction(Request $request, $type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($type == "merchant") {
            $account = $em->getRepository("AppBundle:Merchant")->find($id);

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        }
        $result = $this->get('goodplan.v3')->newEvent($em, $account, $type, $user, $data);


        return $result;
    }

    /**
     * @ApiDoc(resource="/api/goodplan/{type}/{id}/new",
     * description="Ce webservice permet d'ajouter un bon plan.",
     * statusCodes={200="Successful"})
     */
    public function goodPlansAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CITIZEN')) {
            throw $this->createAccessDeniedException();
        }

        $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
        $joinedMerchants = $em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
        $adminMerchants = $em->getRepository('AppBundle:Merchant')->findUserMerchants($user);
        $merchants = array_merge($joinedMerchants, $adminMerchants);


        $cities = $request->get('city');
        $categories = $request->get('category');
        $catIds = [];
        if($categories){
            foreach ($categories as $category) {
                $cat = $em->getRepository('AppBundle:Category')->find($category);
                $catName = $cat->getName();
                $cats = $em->getRepository('AppBundle:Category')->findByCatMerchNameAnCommunities($catName,$followedCommunities);
                foreach ($cats as $c) {
                    $catIds[] = $c->getId();

                }

            }
        }

        $period = $request->get('date');

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $result = array();


        /** @var GoodPlanRepository $goodPlanRepository */
        $goodPlanRepository = $em->getRepository("AppBundle:GoodPlan");

        $merchantId = $request->get('merchantId');

        if ($merchantId){
            $merchants =[];
            $merchants[] = $em->getRepository('AppBundle:Merchant')->find($merchantId);
            $publicMerchGoodPlansByCommunity = [];
            $merchantsMebmbersGoodPlans = $goodPlanRepository->getGoodPlansMerchantsMembers($merchants, $cities,
                $catIds, '2018-01-01');
            $privateMerchantsGoodPlans = $goodPlanRepository->getGoodPlansPrivateMerchants($merchants, $cities,
                $catIds, '2018-01-01');
        }
        else {
            $publicMerchGoodPlansByCommunity = $goodPlanRepository->getPublicMerchantGoodPlanByCommunity($followedCommunities,
                $cities, $catIds, $period);
            $merchantsMebmbersGoodPlans = $goodPlanRepository->getGoodPlansMerchantsMembers($merchants, $cities,
                $catIds, $period);
            $privateMerchantsGoodPlans = $goodPlanRepository->getGoodPlansPrivateMerchants($merchants, $cities,
                $catIds, $period);
        }

        $events = array_map("unserialize",array_unique(array_map("serialize",array_merge($publicMerchGoodPlansByCommunity, $privateMerchantsGoodPlans, $merchantsMebmbersGoodPlans))));
        $repoImg = $em->getRepository("AppBundle:File");
        foreach ($events as $event) {
            /** @var Event $currentEvent */
            $currentEvent = $em->getRepository("AppBundle:GoodPlan")->find($event['id']);
            if ($currentEvent->getParticipants()->contains($user)) {
                $event["isParticipated"] = true;
            }
            if(!$currentEvent->getParticipants()->contains($user)) {
                $event["isParticipated"] = false;
            }



            $event["nbComments"] = $this->getNbrComments($em, $event['id']);
            $images = $em->getRepository("AppBundle:GoodPlan")->find($event['id'])->getImages();
            foreach ($images as $value) {
                $image = $em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl.$path);
                    }
                }
            }
            if (isset($event["image"])) {
                $eventImg = $repoImg->find($event["image"]);
                if ($eventImg) {
                    $path = $helper->asset($eventImg, 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl.$path;
                    }
                }
            } elseif (isset($event["creatorImg"]) && !isset($event["imageURL"]) && !isset($event['images'])) {
                $accountImg = $repoImg->find($event["creatorImg"]);
                if ($accountImg) {
                    $path = $helper->asset($accountImg, 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl.$path;
                    }
                }
            }
                if($currentEvent->getVideo()){
                    $image = $em->getRepository("AppBundle:File")->find($currentEvent->getVideo()->getId());
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['videoFile'] = $baseurl . $path;
                        }
                    }
                }else{
                    $event['videoFile'] = null;
                }

            if($currentEvent->getDocument()){
                $doc = $em->getRepository("AppBundle:File")->find($currentEvent->getDocument()->getId());
                if ($doc) {
                    $path = $helper->asset($doc, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['document'] = $baseurl . $path;
                    }
                }
            }

            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }
            $event['day'] = $currentEvent->getStartAt();
            if ($this->isWeekend($currentEvent->getStartAt())) {
                $resultWeekend[] = $event;
            }
            $event['nbParticipants'] = count($currentEvent->getParticipants());
            $result[] = $event;
        }

        usort($result, function ($a, $b) {
            if ($a["day"] == $b["day"]) {
                return 0;
            } else {
                return ($a["day"] > $b["day"]) ? 1 : -1;
            }
        });

        $offset = ($page - 1) * $limit;
        $pagination = array_slice($result, $offset, $limit);

        return $pagination;


    }

    public function getNbrComments($em, $id)
    {
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);

        return count($event->getComments());
    }

    public function isWeekend($date)
    {
        $weekDay = date('w', strtotime($date->format('Y-m-d H:i:s')));

        return ($weekDay == 0 || $weekDay == 6);
    }


    public function detailsGoodPlanAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }


        $event = $this->get('goodPlan.v3')->detailsGoodPlan($request, $em, $id, $user);


        return $event;
    }



    public function takepartAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Event $event */
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if($event->getParticipants()->contains($user))
        {
            return array("error" => 'Vous êtes déjà participant à ce bon plan');
        }
        $event->addParticipant($user);

        $message = 'Vous participez au bon plan "'.$event->getTitle().'" le '.$event->getStartAt()->format('d/m/Y').' à '.$event->getStartAt()->format('H:i:s').'.';
        $this->container->get('notification')->notify($user, 'goodPlan', $message, true, $event);
        $em->flush();

        $event = $this->get('goodPlan.v3')->detailsGoodPlan($request, $em, $id, $user);

        return array("success" => true, "event" => $event);
    }


    public function canceltakepartAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if(!$event->getParticipants()->contains($user))
        {
            return array("error" => 'Vous n\'êtes pas déjà participant à ce bon plan');
        }
        $event->removeParticipant($user);

        $message = 'Vous ne participez plus au bon plan "'.$event->getTitle().'" le '.$event->getStartAt()->format('d/m/Y').' à '.$event->getStartAt()->format('H:i:s').'.';
        $this->container->get('notification')->notify($user, 'goodplan', $message, true, $event);
        $em->flush();

        $event = $this->get('goodPlan.v3')->detailsGoodPlan($request, $em, $id, $user);

        return array("success" => true, "event" => $event);
    }

    public function contactMerchantAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $goodplan = $em->getRepository("AppBundle:GoodPlan")->find($id);



        $result = $this->get('mail')->contactMerchant($goodplan , $user, $data);


        return array('result =>success');
    }

}
