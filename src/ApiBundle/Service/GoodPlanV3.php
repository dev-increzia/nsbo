<?php

namespace ApiBundle\Service;

use AppBundle\Entity\Event as Event;
use AppBundle\Entity\GoodPlan;
use AppBundle\Entity\Push;
use AppBundle\Entity\File;
use AppBundle\Entity\PushLog;
use DateTime;

class GoodPlanV3
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function newEvent($em, $account, $type, $user, $data)
    {
        $goodPlan = new GoodPlan();
        if ($type == "merchant") {
            $goodPlan->setMerchant($account);
            $city = $account->getCity();
        }

        if (isset($data['photo'])) {
            $image = new File();

            $image->base64($data['photo']);

            $goodPlan->setImage($image);
        }
        if (isset($data['photo2'])) {
            $image = new File();

            $image->base64($data['photo2']);

            $goodPlan->addImage($image);
        }

        if (isset($data['photo3'])) {
            $image = new File();

            $image->base64($data['photo3']);

            $goodPlan->addImage($image);
        }

        if (!empty($data['video']))
        {
            $video = new File();
            $video->base64($data['video']);

            $goodPlan->setVideo($video);
            $em->flush();


        }

        if (!empty($data['document']))
        {
            $document = new File();
            $document->base64($data['document']);

            $goodPlan->setDocument($document);
            $em->flush();


        }

        if (isset($data['photos'])) {
            foreach ($data['photos'] as $value) {
                $image = new File();

                $image->base64($value);

                $goodPlan->addImage($image);
            }
        }

        $city = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));

        if (isset($data["secondCommu"])){
            $secondCummu = $em->getRepository("AppBundle:Community")->find($data["secondCommu"]);
            $goodPlan->setSecondaryCommunity($secondCummu);
            if (isset($data["secondCategories"])) {
                foreach ($data["secondCategories"] as $category) {
                    $category = $em->getRepository('AppBundle:Category')->find($category);
                    $goodPlan->addSecondaryCategory($category);
                }
            }
        }
        foreach ($data["categories"] as $category) {
            $category = $em->getRepository('AppBundle:Category')->find($category);
            $goodPlan->addCategory($category);
        }
        $goodPlan
            ->setStartAt(new \DateTime($data["start_at"]))
            ->setEndAt(new \DateTime($data["end_at"]))
            ->setDescription($data["description"])
            ->setEnabled(true)
            ->setPlace($data["place"])
            ->setCity($city)
            ->setCreateBy($user)
            ->setModerate('wait')
            ->setTitle($data["title"])
            ->setPrivate($data["private"])
            ->setUpdateAt(new DateTime())
            ->setCreateAt(new DateTime())
            ->setCommunity($account->getCommunity());


        if ($account->getCommunity()->getAutoModGoodPlan()) {
            $goodPlan->setModerate("accepted");
        } else {
            $goodPlan->setModerate("wait");
        }

        $em->persist($goodPlan);

        $goodPlan->setPushEnabled($data["push_enabled"]);

        if ($data["push_enabled"]) {
            $push = new Push();
            $push_hour = new DateTime($data["push_hour"]);
            $push_date = new DateTime($data["push_date"]);
            $date_of_push = $push_date->format('Y-m-d') . " " . $push_hour->format('H:i:s');

            $push->setGoodPlan($goodPlan);
            $push->setType('goodPlan');
            $push->setContent($data["push_content"]);
            $push->setCreateBy($user);
            $push->setUpdateBy($user)
                ->setCommunity($account->getCommunity());

            $dateAt = new \DateTime($date_of_push);
            $push->setSendAt($dateAt);
            $goodPlan->setPush($push);
            $em->persist($push);
        } else {
            $goodPlan->setPush(null);
        }
        $this->notifyUsers($goodPlan);
        $em->flush();

        return array("success" => true, "id" => $goodPlan->getId());
    }

    private function notifyUsers($goodPlan)
    {
        if ($goodPlan && $goodPlan->getEnabled()) {
            foreach ($goodPlan->getParticipants() as $user) {
                $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'goodPlanCounter', false);
                $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter',false , $goodPlan);
            }
        }
    }

    public function detailsGoodPlan($request, $em, $id, $user)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $event = $em->getRepository("AppBundle:GoodPlan")->findGoodPlan($id);
        $currentEvent = $em->getRepository("AppBundle:GoodPlan")->find($id);

        $event["nbParticipants"] = $this->getNbrParticipants($em, $event['id']);
        $event["nbComments"] = $this->getNbrComments($em, $event['id']);


        if ($em->getRepository("AppBundle:GoodPlan")->find($id)->getParticipants()->contains($user)) {
            $event["isParticipated"] = true;
        } else {
            $event["isParticipated"] = false;
        }

        $images = $em->getRepository("AppBundle:GoodPlan")->find($event['id'])->getImages();
        foreach ($images as $value) {
            $image = $em->getRepository("AppBundle:File")->find($value);
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                }
            }
        }
        if (isset($event['image'])) {
            $eventImg = $em->getRepository("AppBundle:File")->find($event['image']);
            if ($eventImg) {
                $path = $helper->asset($eventImg, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $event["imageURL"] = $baseurl . $path;
                }
            }
        } elseif ((isset($event["associationImg"]) || isset($event["merchantImg"])) && !isset($event["imageURL"]) && !isset($event['images'])) {
            if (isset($event["associationImg"])) {
                $accountImg = $em->getRepository("AppBundle:File")->find($event["associationImg"]);
            } else {
                $accountImg = $em->getRepository("AppBundle:File")->find($event["merchantImg"]);
            }

            if ($accountImg) {
                $path = $helper->asset($accountImg, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $event["imageURL"] = $baseurl . $path;
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

        $event["participants"] = $em->getRepository("AppBundle:GoodPlan")->findParticipants($id);


        return $event;
    }

    public function getNbrParticipants($em, $id)
    {
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);
        return count($event->getParticipants());
    }

    public function getNbrComments($em, $id)
    {
        $event = $em->getRepository("AppBundle:GoodPlan")->find($id);
        return count($event->getComments());
    }
}
