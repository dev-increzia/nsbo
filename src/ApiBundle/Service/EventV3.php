<?php

namespace ApiBundle\Service;

use AppBundle\Entity\Event as Event;
use AppBundle\Entity\Push;
use AppBundle\Entity\File;
use AppBundle\Repository\EventRepository;
use Doctrine\ORM\EntityManager;
use \DateTime;

class EventV3
{
    protected $container;
    protected $em;

    public function __construct($container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function getEventsFilterMerchant($request, $id, $personalized, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $events = $this->em->getRepository("AppBundle:Event")->getEventsByMerchantFilter($id, $personalized, $page, $limit);
        $result = array();

        foreach ($events as $event) {
            $event["nbParticipants"] = $this->getNbrParticipants($event['id']);
            $event["nbComments"] = $this->getNbrComments($event['id']);
            $event["nbVolunteers"] = $this->getNbrVolunteers($event['id']);

            if (isset($event["image"])) {
                $eventImg = $this->em->getRepository("AppBundle:File")->find($event["image"]);
                if ($eventImg) {
                    $path = $helper->asset($eventImg, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl . $path;
                    }
                }
            }

            $images = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }

            $result[] = $event;
        }

        return $result;
    }

    public function getEventsMerchant($request, $id, $user, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $events = $this->em->getRepository("AppBundle:Event")->getEventsByMerchant($id, $page, $limit);
        $result = array();
        foreach ($events as $event) {
            $event["nbParticipants"] = $this->getNbrParticipants($event['id']);
            $event["nbComments"] = $this->getNbrComments($event['id']);
            $event["nbVolunteers"] = $this->getNbrVolunteers($event['id']);
            $categories = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getCategories();
            $event["categories"] = $categories;
            $entity = $this->em->getRepository("AppBundle:Event")->find($event['id']);
            if ($entity->getParticipants()->contains($user)) {
                $event["isLiked"] = true;
            } else {
                $event["isLiked"] = false;
            }

            $event['isParent'] = false;

            if ($entity->getDuplicatedEvents()->count() !== 0) {
                $event['isParent'] = true;
            }

            if (isset($event["image"])) {
                $eventImg = $this->em->getRepository("AppBundle:File")->find($event["image"]);
                if ($eventImg) {
                    $path = $helper->asset($eventImg, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }
            $result[] = $event;
        }

        return $result;
    }

    public function getEventsAssociation($request, $id, $user, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $events = $this->em->getRepository("AppBundle:Event")->getEventsByAssociation($id, $page, $limit);
        $result = array();
        foreach ($events as $event) {
            $event["nbParticipants"] = $this->getNbrParticipants($event['id']);
            $event["nbComments"] = $this->getNbrComments($event['id']);
            $event["nbVolunteers"] = $this->getNbrVolunteers($event['id']);
            $categories = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getCategories();
            $event["categories"] = $categories;
            if ($this->em->getRepository("AppBundle:Event")->find($event['id'])->getParticipants()->contains($user)) {
                $event["isLiked"] = true;
            } else {
                $event["isLiked"] = false;
            }
            if (isset($event["image"])) {
                $eventImg = $this->em->getRepository("AppBundle:File")->find($event["image"]);
                if ($eventImg) {
                    $path = $helper->asset($eventImg, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl . $path;
                    }
                }
            }

            $images = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }

            $result[] = $event;
        }
        return $result;
    }

    public function getEventsFilterAssociation($request, $id, $personalized, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $events = $this->em->getRepository("AppBundle:Event")->getEventsByAssociationFilter($id, $personalized, $page, $limit);
        $result = array();
        foreach ($events as $event) {
            $event["nbParticipants"] = $this->getNbrParticipants($event['id']);
            $event["nbComments"] = $this->getNbrComments($event['id']);
            $event["nbVolunteers"] = $this->getNbrVolunteers($event['id']);

            if (isset($event["image"])) {
                $eventImg = $this->em->getRepository("AppBundle:File")->find($event["image"]);
                if ($eventImg) {
                    $path = $helper->asset($eventImg, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getImages();

            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }

            $result[] = $event;
        }
        return $result;
    }

    public function detailsEvent($request, $id, $user)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $event = $this->em->getRepository("AppBundle:Event")->findEvent($id);
        $currentEvent = $this->em->getRepository("AppBundle:Event")->find($id);
        $event["nbParticipants"] = $this->getNbrParticipants($event['id']);
        $event["nbComments"] = $this->getNbrComments($event['id']);
        $event["nbVolunteers"] = $this->getNbrVolunteers($event['id']);
        $event["needCarpool"] = $this->em->getRepository('AppBundle:Carpooling')->getEventCarpool($event['id']);
        $event["alreadyPool"] = 0;
        $event["alreadyAddPool"] = 0;
        /** @var EventVolunteer $volunteer */
        $volunteer = $this->em->getRepository("AppBundle:EventVolunteer")->findOneBy(array('user'=>$user,'event'=>$currentEvent));
        $event["nbVolunteers"] = $currentEvent->getVolunteers()->count();
        if($volunteer){
            $event["isVolunteer"] = true;
        }else{
            $event["isVolunteer"] = false;
        }

        $existAdd = $this->em->getRepository('AppBundle:Carpooling')->findBy(array('event'=>$event['id'],'createBy'=>$user));
        if ($existAdd){
            $event["alreadyAddPool"] = 1;
        }
        $event["nbCarpoolUsers"] = 0;
        foreach ($event["needCarpool"] as $key => $carpool){
            $carpoolingAnswers = $this->em->getRepository('AppBundle:CarpoolingAnswers')->findBy(array('carpooling'=> $carpool['id']));
            $event["nbCarpoolUsers"]+= count($carpoolingAnswers);
            $event["needCarpool"][$key]['placeLeft'] = $carpool['nbPlaces'] - count($carpoolingAnswers);
            $exist = $this->em->getRepository('AppBundle:CarpoolingAnswers')->findBy(array('carpooling'=>$carpool['id'],'createBy'=>$user));
            if ($exist){
                $event["alreadyPool"] = 1;
            }
        }
          /*$event["needCarpool"] = $this->em->getRepository('AppBundle:Carpooling')->getEventCarpool($event);
          foreach ($event["needCarpool"] as $key => $carpool){
              $event["needCarpool"][$key]['placeLeft'] = $carpool['nbPlaces'] - count($this->em->getRepository('AppBundle:CarpoolingAnswers')->findBy(array('carpooling'=> $carpool['id'])));
          }*/
        if ($this->em->getRepository("AppBundle:Event")->find($id)->getParticipants()->contains($user)) {
            $event["isParticipated"] = true;
        } else {
              $event["isParticipated"] = false;
        }

        $images = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getImages();
        foreach ($images as $value) {
            $image = $this->em->getRepository("AppBundle:File")->find($value);
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                }
            }
        }
        if (isset($event['image'])) {
            $eventImg = $this->em->getRepository("AppBundle:File")->find($event['image']);
            if ($eventImg) {
                $path = $helper->asset($eventImg, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $event["imageURL"] = $baseurl . $path;
                }
            }
        } elseif ((isset($event["associationImg"]) || isset($event["merchantImg"])) && !isset($event["imageURL"]) && !isset($event['images'])) {
            if (isset($event["associationImg"])) {
                $accountImg = $this->em->getRepository("AppBundle:File")->find($event["associationImg"]);
            } else {
                $accountImg = $this->em->getRepository("AppBundle:File")->find($event["merchantImg"]);
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
            $image = $this->em->getRepository("AppBundle:File")->find($currentEvent->getVideo()->getId());
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                  $event['videoFile'] = $baseurl . $path;
                }
            }
        } else{
            $event['videoFile'] = null;
        }
        if (!isset($event["imageURL"]) && !isset($event['images'])) {
            $event["imageURL"] = "assets/img/user_default.png";
        }

        $event["participants"] = $this->em->getRepository("AppBundle:Event")->findParticipants($id);

        if (isset($event['articleId'])) {
            $event['article'] = $this->em->getRepository("AppBundle:Article")->findArticle($event['articleId']);
              // image url articl
            if (isset($event['article']['image'])) {
                $eventArticleImg = $this->em->getRepository("AppBundle:File")->find($event['article']['image']);
                if ($eventArticleImg) {
                    $path = $helper->asset($eventArticleImg, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['article']["imageURL"] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:Article")->find($event['articleId'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['article']['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }


            switch ($event['article']['type']) {
                case 'association':
                if (isset($event['article']['associationImage'])) {
                    $imageFile = $this->em->getRepository("AppBundle:File")->find($event['article']['associationImage']);
                    if ($imageFile) {
                        $path = $helper->asset($imageFile, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['article']["associationImageURL"] = $baseurl . $path;
                        }
                    }
                }

                break;
                case 'merchant':
                if (isset($event['article']['merchantImage'])) {
                    $imageFile = $this->em->getRepository("AppBundle:File")->find($event['article']['merchantImage']);
                    if ($imageFile) {
                        $path = $helper->asset($imageFile, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['article']["merchantImageURL"] = $baseurl . $path;
                        }
                    }
                }

                break;
                case 'cityhall':
                if (isset($event['article']['cityhallImage'])) {
                    $imageFile = $this->em->getRepository("AppBundle:File")->find($event['article']['associationImage']);
                    if ($imageFile) {
                        $path = $helper->asset($imageFile, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['article']["cityhallImageURL"] = $baseurl . $path;
                        }
                    }
                }

                break;
                case 'user':
                if (isset($event['article']['userImage'])) {
                    $imageFile = $this->em->getRepository("AppBundle:File")->find($event['article']['associationImage']);
                    if ($imageFile) {
                        $path = $helper->asset($imageFile, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $event['article']["userImageURL"] = $baseurl . $path;
                        }
                    }
                }

                break;
                default:
                break;
            }
        }

        return $event;
    }

    public function newEvent($account, $type, $user, $data)
    {
        $event = new Event();
        if ($type == "association") {
            $event->setAssociation($account);
            $event->setType("association");
            $event->setCommunity($account->getCommunity());
            $city = $account->getCity();
            if($account->getCommunity()->getAutoModEvent())
            {
                $event->setModerate('accepted');
                $event->setModerateAt(new DateTime('now'));

            }else {
                $event->setModerate('wait');
            }
        } elseif ($type == "merchant") {
            $event->setMerchant($account);
            $event->setType("merchant");
            $city = $account->getCity();
        }
        elseif ($type == "community") {
            $event->setCommunity($account);
            $event->setType("community");

            if($account->getAutoModEvent())
            {
                $event->setModerate('accepted');
                $event->setModerateAt(new DateTime('now'));

            }else {
                $event->setModerate('wait');
            }
        }

        if (isset($data['photo'])) {
            $image = new File();

            $image->base64($data['photo']);

            $event->setImage($image);
        }
        
        if (!empty($data['video'])) 
        {
            $video = new File();
            $video->base64($data['video']);
            
            $event->setVideo($video);
            
            if (!empty($data["event"])) 
            {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setVideo($video);
                $this->em->flush();
            }
        }
        
        if (isset($data['photo2'])) {
            $image = new File();

            $image->base64($data['photo2']);

            $event->addImage($image);
        }

        if (isset($data['photo3'])) {
            $image = new File();

            $image->base64($data['photo3']);

            $event->addImage($image);
        }

        foreach ($event->getReservations() as $value) {
            $event->removeReservation($value);
        }

        if (isset($data["eventreservation_id"])) {
            $eventreservation = $this->em->getRepository("AppBundle:EventReservation")->find($data["eventreservation_id"]);
            if ($eventreservation) {
                $event->addReservation($eventreservation);
            }
        }

        $city = $this->em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
        if (isset($data["secondCommu"])){
            $secondCummu = $this->em->getRepository("AppBundle:Community")->find($data["secondCommu"]);
            $event->setSecondaryCommunity($secondCummu);
            if (($data["secondCategories"])){
                foreach ($data["secondCategories"] as $category) {
                    $category = $this->em->getRepository('AppBundle:Category')->find($category);
                    $event->addSecondaryCategory($category);
                }
            }
        }

        foreach ($data["categories"] as $category) {
            $category = $this->em->getRepository('AppBundle:Category')->find($category);
            $event->addCategory($category);
        }
        $event
            ->setStartAt(new DateTime($data["start_at"]))
            ->setEndAt(new DateTime($data["end_at"]))
            ->setDescription($data["description"])
            ->setPrice($data["price"])
            ->setPersonalized($data["personalized"])
            ->setEnabled(true)
            ->setPlace($data["place"])
            ->setCity($city)
            ->setCreateBy($user)
            ->setTitle($data["title"])
            ->setPrivate($data["private"]);

        if ($type != "community"){
            $event->setCommunity($account->getCommunity());
        }

        if ($data["personalized"]) {
            $event
            ->setAgeFrom($data["agefrom"])
            ->setAgeTo($data["ageto"])
            ->setMonday($data["monday"])
            ->setTuesday($data["tuesday"])
            ->setWednesday($data["wednesday"])
            ->setThursday($data["thursday"])
            ->setFriday($data["friday"])
            ->setSaturday($data["saturday"])
            ->setSunday($data["sunday"])
            ->setLessThanSix($data["lessthansix"])
            ->setBetweenSixTwelve($data["betweensixtwelve"])
            ->setBetweenTwelveEighteen($data["twelveeighteen"])
            ->setAllChildrens($data["allchildrens"]);
        }
        if (isset($data["article_id"])) {
            $article = $this->em->getRepository("AppBundle:Article")->find($data["article_id"]);
            $event->setArticle($article);
        }

            $this->em->persist($event);
            if ($event->getType() == 'association') {
                $message = 'le groupe / association ' . ($event->getAssociation() ? $event->getAssociation()->getName() : '') . ' vous propose ' . $event->getTitle() . ' le ' . ($event->getStartAt() ? $event->getStartAt()->format('d/m/Y H:i:s') : '') . '.';
            } elseif ($event->getType() == 'merchant') {
                $message = 'Le Commerce / partenaire ' . ($event->getMerchant() ? $event->getMerchant()->getName() : '') . ' vous propose ' . $event->getTitle() . ' le ' . ($event->getStartAt() ? $event->getStartAt()->format('d/m/Y H:i:s') : '') . '.';
            }
            elseif ($event->getType() == 'community') {
                $message = 'La communauté ' . ($event->getCommunity() ? $event->getCommunity()->getName() : '') . ' vous propose ' . $event->getTitle() . ' le ' . ($event->getStartAt() ? $event->getStartAt()->format('d/m/Y H:i:s') : '') . '.';
            }

            if ($data["room"]){
                $receivers = $event->getCommunity()->getCommunityAdmins();
                $mailToUser = 0;
                foreach ($receivers as $receiver){
                    if ($receiver->hasRight('room_aprove',$event->getCommunity())){
                        $this->container->get('mail')->sendRoomMail($user, $receiver->getEmail(), $event, $data["messageMail"]);
                        $mailToUser++;
                    }
                }
                if ($mailToUser == 0) {
                    $suAdmins = $event->getCommunity()->getCommunitySuadmins();
                    foreach ($suAdmins as $su) {
                        $this->container->get('mail')->sendRoomMail($user, $su->getEmail(), $event, $data["messageMail"]);

                    }
                }
            }

            if ($event->getPersonalized()) {
                $users = $this->em->getRepository('UserBundle:User')->findCitizenForEventPersonalized($event->getCommunity(), $event->getAgeFrom(), $event->getageTo(), $event->getMonday(), $event->getTuesday(), $event->getWednesday(), $event->getThursday(), $event->getFriday(), $event->getSaturday(), $event->getSunday(), $event->getLessThanSix(), $event->getBetweenSixTwelve(), $event->getBetweenTwelveEighteen(), $event->getAllChildrens());
                foreach ($users as $user) {
                    $this->container->get('notification')->notify($user, 'event', $message, false, $event);
                    $this->container->get('mobile')->pushNotification($user, 'NOUS-ENSEMBLE ', "", false, false, 'on');
                }
            }
            if ($data["private"] && $type == "association") {
                $message = 'le groupe / association ' . ($event->getAssociation() ? $event->getAssociation()->getName() : '') . ' vous propose ' . $event->getTitle() . ' le ' . ($event->getStartAt() ? $event->getStartAt()->format('d/m/Y H:i:s') : '') . '.';

                $users = $this->em->getRepository("AppBundle:Association")->getMemberships($account);

                foreach ($users as $u) {
                    $user = $this->em->getRepository("UserBundle:User")->find($u['user_id']);
                    $this->container->get('notification')->notify($user, 'event', $message, false, $event);
                    $this->container->get('mobile')->pushNotification($user, 'NOUS-ENSEMBLE ', $message, false, false, 'off');
                }
            }

            $this->em->flush();

        return array("success" => true, "id" => $event->getId(),'moderate' => $event->getModerate());
    }

    public function citzeneventInterest($request, $user, $enabled, $userinterestIds)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $associationsEvents = $this->em->getRepository("AppBundle:Event")->getEventsAssociationsByInterest($user, $enabled, $userinterestIds);
        $merchantsEvents = $this->em->getRepository("AppBundle:Event")->getEventsMerchantsByInterest($user, $enabled, $userinterestIds);
        $events = array_merge($associationsEvents, $merchantsEvents);
        $result = array();
        foreach ($events as $event) {
            if (isset($event["image"])) {
                $img = $this->em->getRepository("AppBundle:File")->find($event["image"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl . $path;
                    }
                }
            } else {
                $event["imageURL"] = "assets/img/user_default.png";
            }
            $result[] = $event;
        }

        usort($result, function ($a, $b) {
            if ($a["startAt"] == $b["startAt"]) {
                return 0;
            } else {
                return ($a["startAt"] > $b["startAt"]) ? 1 : -1;
            }
        });

        return $result;
    }

    public function citzeneventallInterests($request, $user, $enabled, $userinterestIds, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $associationsEvents = $this->em->getRepository("AppBundle:Event")->getEventsAssociationsByInterest($user, $enabled, $userinterestIds);
        $merchantsEvents = $this->em->getRepository("AppBundle:Event")->getEventsMerchantsByInterest($user, $enabled, $userinterestIds);
        $events = array_merge($associationsEvents, $merchantsEvents);
        $repoImg = $this->em->getRepository("AppBundle:File");
        $result = array();
        foreach ($events as $event) {
            $event["nbParticipants"] = $this->getNbrParticipants($event['id']);
            $event["nbComments"] = $this->getNbrComments($event['id']);
            
            if ($this->em->getRepository("AppBundle:Event")->find($event['id'])->getParticipants()->contains($user)) {
                $event["isLiked"] = true;
            } else {
                $event["isLiked"] = false;
            }
            if (isset($event["image"])) {
                $eventImg = $this->em->getRepository("AppBundle:File")->find($event["image"]);
                if ($eventImg) {
                    $path = $helper->asset($eventImg, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event["imageURL"] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:Event")->find($event['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $event['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }

            $result[] = $event;
        }

        usort($result, function ($a, $b) {
            if ($a["startAt"] == $b["startAt"]) {
                return 0;
            } else {
                return ($a["startAt"] > $b["startAt"]) ? 1 : -1;
            }
        });

        $offset = ($page - 1) * $limit;
        $pagination = array_slice($result, $offset, $limit);

        return $pagination;
    }

    public function inPeriod($period, $date)
    {
        $inDate = false;
        $now = new DateTime();
        $todayStart = new DateTime($now->format("Y-m-d") . " 00:00:00");
        $todayEnd = new DateTime($now->format("Y-m-d") . " 23:59:59");
        switch ($period) {
            case 'today':
            $to = new DateTime('now');

            break;

            case 'weekend':
            $from = new DateTime('next Saturday');
            $to = new DateTime('next Sunday');

            break;

            case '1month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +1 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +1 month');

            break;

            case '2month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +2 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +2 month');

            break;

            case '3month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +3 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +3 month');

            break;

            case '4month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +4 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +4 month');

            break;

            case '5month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +5 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +5 month');

            break;

            case '6month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +6 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +6 month');

            break;

            case '7month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +7 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +7 month');

            break;

            case '8month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +8 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +8 month');

            break;

            case '9month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +9 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +9 month');

            break;

            case '10month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +10 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +10 month');

            break;

            case '11month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +11 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +11 month');

            break;

            case '12month':

            $from = new DateTime($now->format("Y-m-d") . " 00:00:00");
            $from = $todayStart->modify('first day of +12 month');

            $to = new DateTime($now->format("Y-m-d") . " 23:59:59");
            $to = $todayEnd->modify('last day of +12 month');

            break;
        }

        if ($to->format('Y-m-d') >= $date) {
            $inDate = true;
        }

        return $inDate;
    }

    public function getNbrComments($id)
    {
        $event = $this->em->getRepository("AppBundle:Event")->find($id);
        return count($event->getComments());
    }

    public function getNbrParticipants($id)
    {
        $event = $this->em->getRepository("AppBundle:Event")->find($id);
        return count($event->getParticipants());
    }

    public function getNbrVolunteers($id)
    {
        $event = $this->em->getRepository("AppBundle:Event")->find($id);
        return count($event->getVolunteers());
    }

    public function update( $data, $event)
    {
        $mode = "current";

        if (isset($data['mode'])) {
            $mode = $data['mode'];
        }

        if ($mode === "current") {
            $this->updateEventData($event, $data);
        }
        if ($mode === 'currentAndNext') {
            $events = $this->em->getRepository("AppBundle:Event")->getNextEvents($event);

            foreach ($events as $currentEvent) {
                $this->updateEventData($currentEvent, $data);
            }
        }

        $this->em->flush();

        return true;
    }
    
    public function updateEventData($event, $data)
    {
        foreach ($event->getCategories() as $category) {
            $event->removeCategory($category);
        }

        foreach ($data["categories"] as $category) {
            if(is_object($category)){
                $category = (array) $category;
                $category   =   $category['id'];
            }
            $category = $this->em->getRepository('AppBundle:Category')->find($category);
            $event->addCategory($category);
        }

        $event->setTitle($data["title"]);

        if (!empty($data['video'])) {
            $video = new File();
            $video->base64($data['video']);

            $event->setVideo($video);
        } elseif (isset($data["todeleteVideo"])) {
            $event->setVideo(null);
        } 
        
        // image 1
        if (!empty($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $event->setImage($image);
        } elseif ($data["todelete"]) {
            $event->setImage(null);
        }
        
        // image 2
        if (!empty($data['photo2'])) {
            $image = new File();
            $image->base64($data['photo2']);
            $event->addImage($image);
        }
        if ($data["todelete2"] && $data["photoId2"]) {
            $img = $this->em->getRepository("AppBundle:File")->find($data["photoId2"]);
            $event->removeImage($img);
        }

        // image 3
        if (!empty($data['photo3'])) {
            $image = new File();
            $image->base64($data['photo3']);
            $event->addImage($image);
        }
        if ($data["todelete3"] && $data["photoId3"]) {
            $img = $this->em->getRepository("AppBundle:File")->find($data["photoId3"]);
            $event->removeImage($img);
        }
        
        if (isset($data['imges'])) {
            foreach ($data['imges'] as $value) {
                if (empty($value->url)) {
                    $img = $this->em->getRepository("AppBundle:File")->find($value->id);
                    $event->removeImage($img);
                }
            }
        }

        if (isset($data['photos'])) {
            foreach ($data['photos'] as $value) {
                $image = new File();

                $image->base64($value);
                $event->addImage($image);
            }
        }

        $city = $this->em->getRepository("AppBundle:City")->findOneBy(array("name"=>$data["cityName"]));
        $event->setCity($city);

        if(isset($data["pushEnabled"])) {
            if($data["pushEnabled"]) {
                if($event->getPush()) {
                    $event->getPush()->setContent($data["pushContent"])->setSendAt(new DateTime($data["pushDate"]));
                } else {
                    $push = new Push();
                    $push->setContent($data["pushContent"])->setSendAt(new DateTime($data["pushDate"]));
                    $this->em->persist($push);
                    $event->setPush($push);
                }
            } else {
                $event->setPush(null);
            }
        }

        $event->setDescription($data["description"]);
    }

    public function duplicate($data, $parent, $user)
    {
        $type = $parent->getType();
        $dateArray = $this->getRecursiveDates($data);
        $dateStartParent = $parent->getStartAt();
        $dateEndParent = $parent->getEndAt();
        $interval = $dateStartParent->diff($dateEndParent);

        if ($parent->getPush()) {
            $pushDate = $parent->getPush()->getSendAt();
            $pushInterval = $pushDate->diff($dateStartParent);
        }

        foreach ($dateArray as $date) {
            $event = new Event();
            $startDate = new DateTime($date);
            $endDate = $startDate->add($interval);
                $city = $this->em->getRepository('AppBundle:City')->findOneBy(array("name" => $data['city']));
                if(isset($data['images'])) {
                    $imagesCounter = 0;
                    foreach ($data['images'] as $image) {
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
                            if(count($data['images']) == $imagesCounter && !isset($data['photo'])) {
                                $event->setImage($currentImage);
                            }
                            else
                            {
                                $event->addImage($currentImage);
                            }
                        }
                    }
                }
            $event->setStartAt(new DateTime($date))
                ->setEndAt($endDate)
                ->setParent($parent)
                ->setAssociation($parent->getAssociation())
                ->setType($parent->getType())
                ->setCommunity($parent->getCommunity())
                ->setModerate($parent->getModerate())
                ->setModerateAt($parent->getModerateAt())
                ->setDescription($data["description"])
                ->setPrice($data["price"])
                ->setPersonalized($parent->getPersonalized())
                ->setEnabled(true)
                ->setCity($city)
                ->setCreateBy($user)
                ->setTitle($data["title"])
                ->setPrivate($parent->getPrivate())
                ->setPushEnabled($parent->getPushEnabled())
                ->setSecondaryCommunity($parent->getSecondaryCommunity());

            if ($parent->getSecondaryCategories()->count() !== 0){
                foreach ($parent->getSecondaryCategories() as $category) {
                    $event->addSecondaryCategory($category);
                }
            }

            if (isset($data['photo'])) {
                $image = new File();

                $image->base64($data['photo']);
                $event->setImage($image);
            }

            if (!empty($data['video']))
            {
                $video = new File();

                $video->base64($data['video']);
                $event->setVideo($video);
            }

            if (isset($data['photo2'])) {
                $image = new File();

                $image->base64($data['photo2']);
                $event->addImage($image);
            }

            if (isset($data['photo3'])) {
                $image = new File();

                $image->base64($data['photo3']);
                $event->addImage($image);
            }

            if (isset($data['photos'])) {
                foreach ($data['photos'] as $value) {
                    $image = new File();

                    $image->base64($value);
                    $event->addImage($image);
                }
            }

            foreach ($parent->getCategories() as $category) {
                $event->addCategory($category);
            }

            if ($parent->getPush()) {
                $push = new Push();

                $push->setEvent($event);
                $push->setContent($parent->getPush()->getContent());
                $push->setCreateBy($user);
                $push->setUpdateBy($user);
                $push->setCommunity($parent->getPush()->getCommunity());
                $push->setSendAt($startDate->sub($pushInterval));

                $event->setPush($push);
                $this->em->persist($push);
            }

            $this->em->persist($event);
        }

        $this->em->flush();
    }

    public function getRecursiveDates($data)
    {
        $recursivityEnd = $data['recursivity_end'];
        $recursivityPeriod = $data['recurivity_period'];
        $recursivityDay = $data['recursivity_day'];
        $dateArray = [];

        if ($recursivityPeriod === 'weekly') {
            $now = strtotime('now');
            $endDate = strtotime($recursivityEnd);
            for($i = strtotime($recursivityDay, $now); $i <= $endDate; $i = strtotime('+1 week', $i)) {
                $dateArray[] = date('Y-m-d', $i) . ' ' . $data['recursivity_hour'];
            }
        }

        if ($recursivityPeriod === 'daily') {
            $now = new DateTime('now');
            $endDate = new DateTime($recursivityEnd);
            $period = new \DatePeriod($now, new \DateInterval('P1D'), $endDate->modify('+1 day'));

            foreach($period as $date) {
                $dateArray[] = $date->format('Y-m-d') . ' ' . $data['recursivity_hour'];
            }
        }

        if ($recursivityPeriod === 'monthly') {
            $start = new DateTime('now');
            $start->modify('first day of this month');

            $end = new DateTime($recursivityEnd);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $date = $dt->format('Y-m-' . $recursivityDay);
                if ($this->validateDate($date)) {
                    $dateArray[] = $date . ' ' . $data['recursivity_hour'];
                }
            }
        }

        return $dateArray;
    }

    public function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }
    
    public function delete($mode, Event $event, $apiVersion)
    {
        if ($apiVersion === '3') {
            $this->em->remove($event);
            $this->em->flush();
            return true;
        }

        if ($event->getDuplicatedEvents()->count() !== 0) {
            return false;
        }

        if ($mode === "current") {
            $this->em->remove($event);
            $this->em->flush();
        } else if ($mode === 'currentAndNext') {
            $this->em->getRepository("AppBundle:Event")->removeNextEvents($event);
        }

        return true;
    }
}
