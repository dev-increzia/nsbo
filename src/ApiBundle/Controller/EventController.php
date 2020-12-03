<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Carpooling;
use AppBundle\Entity\CarpoolingAnswers;
use AppBundle\Entity\Community;
use AppBundle\Repository\EventRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\File;
use AppBundle\Entity\Notification;
use AppBundle\Entity\EventVolunteer;
use AppBundle\Entity\Push;
use UserBundle\Entity\User;

class EventController extends Controller
{

    /**
     * @ApiDoc(resource="/api/event/eventreservations",
     * description="Ce webservice permet de recupérer le type de réservations d'événement",
     * statusCodes={200="Successful"})
     */
    public function eventReservationAction()
    {
        $em = $this->getDoctrine()->getManager();
        $eventreservations = $em->getRepository("AppBundle:EventReservation")->findAll();

        return $eventreservations;
    }

    /**
     * @ApiDoc(resource="/api/event/{event}/volunteers",
     * description="Liste des bénévoles d'un événement",
     * statusCodes={200="Successful"})
     */
    public function volunteersAction(Event $event, Request $request)
    {
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $volunteers = $event->getVolunteers();

        foreach ($volunteers as $volunteer) {
            $user = $volunteer->getUser();
            if ($user->getImage()) {
                $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
                $path = $helper->asset($user->getImage(), 'file');
                $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                if ($path) {
                    $user->setImageURL($baseurl.$path);
                }
            }
        }

        return $volunteers;
    }

    /**
     * @ApiDoc(resource="/api/event/volunteers/mails",
     * description="Envoi d'un email aux bénévoles d'un événement",
     * statusCodes={200="Successful"})
     */
    public function sendMailsAction(Request $request)
    {
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $event = $em->getRepository('AppBundle:Event')->find($data['event_id']);
        $apiVersion = $user->getApiVersion();

        if ($data['account_type'] == "merchant") {
            $account = $em->getRepository('AppBundle:Merchant')->find($data['account_id']);
        } else {
            $account = $em->getRepository('AppBundle:Association')->find($data['account_id']);
        }

        if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
            if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }

        if ($data['benevoles']) {
            foreach ($data['benevoles'] as $benevole) {
                $user = $em->getRepository('UserBundle:User')->find($benevole);
                $this->container->get('mail')->sendBenevolesMail($this->getUser(), $user, $data['object'],
                    $data['email'], $event, $account, $data['account_type']);
            }
        }

        return array('sucess' => true);
    }

    /**
     * @ApiDoc(resource="/api/event/volunteers/mails/all",
     * description="Envoi d'un email aux bénévoles d'un événement",
     * statusCodes={200="Successful"})
     */
    public function sendAllMailsAction(Request $request)
    {
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $apiVersion = $user->getApiVersion();

        if ($data['account_type'] == "merchant") {
            $account = $em->getRepository('AppBundle:Merchant')->find($data['account_id']);
        } else {
            $account = $em->getRepository('AppBundle:Association')->find($data['account_id']);
        }

        if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
            if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }

        if ($data['benevoles']) {
            foreach ($data['benevoles'] as $benevole) {
                $volunteer = $em->getRepository('AppBundle:EventVolunteer')->find($benevole);

                $this->container->get('mail')->sendBenevolesMail($this->getUser(), $volunteer->getUser(),
                    $data['object'], $data['email'], $volunteer->getEvent(), $account, $data['account_type']);
            }
        }

        return array('sucess' => true);
    }

    /**
     * @ApiDoc(resource="/api/event/{start}/{offset}",
     * description="Liste des événements",
     * statusCodes={200="Successful"})
     */
    public function agendaAction(Request $request, $start, $offset)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $time = $request->query->get('time');
        $city = $request->query->get('city');
        $events = $em->getRepository('AppBundle:Event')->getEventByUser($time, $city, $user, $start, $offset);

        return $events;
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/merchant/{page}/{limit}",
     * description="Liste des événements",
     * statusCodes={200="Successful"})
     */
    public function getEventsMerchantAction($id, $page, $limit, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        $events = $this->get('event.v3')->getEventsMerchant($request, $id, $user, $page, $limit);

        return $events;
    }

    /**
     * @ApiDoc(resource="/api/event/merchant/{id}/filter/{personalized}/{page}/{limit}",
     * description="Liste des événements avec filter personalisé",
     * statusCodes={200="Successful"})
     */
    public function getEventsFilterMerchantAction($id, $personalized, $page, $limit, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $merchant = $em->getRepository("AppBundle:Merchant")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
            if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        $events = $this->get('event.v2')->getEventsFilterMerchant($request, $em, $id, $personalized, $page, $limit);

        return $events;
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/association/{page}/{limit}",
     * description="Liste des événements",
     * statusCodes={200="Successful"})
     */
    public function getEventsAssociationAction($id, $page, $limit, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }

        $events = $this->get('event.v3')->getEventsAssociation($request, $id, $user, $page, $limit);

        return $events;
    }

    /**
     * @ApiDoc(resource="/api/event/association/{id}/filter/{personalized}/{page}/{limit}",
     * description="Liste des événements avec filter personalisé",
     * statusCodes={200="Successful"})
     */
    public function getEventsFilterAssociationAction($id, $personalized, $page, $limit, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $association = $em->getRepository("AppBundle:Association")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();


        if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
            if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }
        $events = $this->get('event.v3')->getEventsFilterAssociation($request, $id, $personalized, $page, $limit);

        return $events;
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/details",
     * description="Détails evénement",
     * statusCodes={200="Successful"})
     */
    public function detailsEventAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $event = $this->get('event.v3')->detailsEvent($request, $id, $user);

        return $event;
    }

    /**
     * @ApiDoc(resource="/api/event/{type}/{id}/new",
     * description="Ce webservice permet d'ajouter un événement.",
     * statusCodes={200="Successful"})
     */
    public function newAction(Request $request, $type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $apiVersion = $this->getUser()->getApiVersion();
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($type == "association") {
            $account = $em->getRepository("AppBundle:Association")->find($id);

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        }
        else if ($type == "community") {
            /** @var Community $account */
            $account = $em->getRepository("AppBundle:Community")->find($id);

            if ((!$user->isCommunityAdmin($account) &&  !$user->isCommunitySuAdmin($account)) || !$account->getEnabled()) {
                throw $this->createAccessDeniedException();
            }
        } elseif ($type == "merchant") {
              $account = $em->getRepository("AppBundle:Merchant")->find($id);

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        }
        $result = $this->get('event.v3')->newEvent($account, $type, $user, $data);

        return $result;
    }
    /**
     * @ApiDoc(resource="/api/event/delete/{id}",
     * description="Ce web service permet de supprimer un evenement",
     * statusCodes={200="Successful"})
     */
    public function deleteEventAction(Request $request, Event $event)
    {
        $user = $this->getUser();
        $apiVersion = $user->getApiVersion();

        if (!$user->getEnabled()){
            throw $this->createAccessDeniedException();
        }

        $this->checkPermissions($event, $user);
        
        if ($event->getDuplicatedEvents()->count() > 0) {
            return array("success" => false);
        }

        $mode = $request->get('mode', 'current');
        try {
            $result = $this->get('event.v3')->delete($mode, $event, $apiVersion);
        }
        catch(\Exception $ex) {
            $result =   false;
        }

        return array("success" => $result);
    }

    private function checkPermissions(Event $event, User $user)
    {
        $type = $event->getType();
        if ($type == "association") {
            $account = $event->getAssociation();
            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()) {
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "merchant") {
            $account = $event->getMerchant();
            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin()  !=  $user   || !$account->getEnabled()) {
                    throw $this->createAccessDeniedException();
                }
            }
        } else {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/activate",
     * description="Ce webservice permet d'activer un evenement.",
     * statusCodes={200="Successful"})
     */
    public function activateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $type = $event->getType();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($type == "association") {
            $account = $event->getAssociation();

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "merchant") {
            $account = $event->getMerchant();

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } else {
            throw $this->createAccessDeniedException();
        }

        $event->setEnabled(true);
        $em->flush();

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/deactivate",
     * description="Ce webservice permet de desactiver un evenement.",
     * statusCodes={200="Successful"})
     */
    public function deactivateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $type = $event->getType();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($type == "association") {
            $account = $event->getAssociation();

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "merchant") {
            $account = $event->getMerchant();

            if (!$account->getAdmins()->contains($user) || !$account->getEnabled()) {
                if ($account->getSuAdmin() != $user || !$account->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } else {
            throw $this->createAccessDeniedException();
        }

        $event->setEnabled(false);
        foreach ($event->getParticipants() as $participant) {
            $message = "L'événement ".$event->getTitle()." a été désactivé. ";
            $this->container->get('notification')->notify($participant, 'eventDisabled', $message, false, $event);
            $this->container->get('mobile')->pushNotification($participant, 'NOUS-ENSEMBLE ', "", false, false, 'on');
        }

        $em->flush();

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/takepart",
     * description="Ce webservice permet d'ajouter un participant a un événement.",
     * statusCodes={200="Successful"})
     */
    public function takepartAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $event->addParticipant($user);

        $message = 'Vous participez à l\'événement "'.$event->getTitle().'" le '.$event->getStartAt()->format('d/m/Y').' à '.$event->getStartAt()->format('H:i:s').'.';
        $this->container->get('notification')->notify($user, 'eventParticipateAdd', $message, true, $event);
        $em->flush();

        $event = $this->get('event.v3')->detailsEvent($request, $id, $user);

        return array("success" => true, "event" => $event);
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/canceltakepart",
     * description="Ce webservice permet de supprimer un participant d'un événement.",
     * statusCodes={200="Successful"})
     */
    public function canceltakepartAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $event->removeParticipant($user);
        $volunteers = $em->getRepository("AppBundle:EventVolunteer")->findBy(array('user' => $user, 'event' => $event));

        foreach ($volunteers as $volunteer) {
            $em->remove($volunteer);
        }
        $message = 'Vous ne participez plus à l\'événement "'.$event->getTitle().'" le '.$event->getStartAt()->format('d/m/Y').' à '.$event->getStartAt()->format('H:i:s').'.';
        $this->container->get('notification')->notify($user, 'eventParticipateRemove', $message, true, $event);
        $em->flush();

        $event = $this->get('event.v3')->detailsEvent($request, $id, $user);

        return array("success" => true, "event" => $event);
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/addcontact",
     * description="Ce webservice permet d'ajouter le contact d'un volentère a un événement.",
     * statusCodes={200="Successful"})
     */
    public function addContactAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $eventvolunteer = new EventVolunteer();
        $eventvolunteer->setType($data["type"]);
        if ($data["type"] == "phone") {
            $eventvolunteer->setValue($data["value"]);
            $user->setPhone($data["value"]);
        } else {
            $eventvolunteer->setValue($user->getEmail());
        }

        $event = $em->getRepository("AppBundle:Event")->find($id);
        $eventvolunteer->setEvent($event);
        $eventvolunteer->setUser($user);
        if ($event->getType() == 'association') {
            $message = 'Vous vous êtes porté bénévole sur l\'événement "'.$event->getTitle().'" du groupe "'.($event->getAssociation() ? $event->getAssociation()->getName() : '').'".';
        } else {
            $message = 'Vous vous êtes porté bénévole sur l\'événement "'.$event->getTitle().'" de la communautée "'.($event->getCommunity() ? $event->getCommunity()->getName() : '').'".';
        }
        $em->persist($eventvolunteer);
        $this->container->get('notification')->notify($user, 'volunteer', $message, true, $event);

        $em->flush();

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/event/citzen/agenda/{page}/{limit}",
     * statusCodes={200="Successful"})
     */
    public function citzenAgendaAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $enabled = true;
        $userinterests = $user->getInterests();
        $arrayuserinterestIds = array();

        foreach ($userinterests as $userinterest) {
            $arrayuserinterestIds[] = $userinterest->getId();
        }

        $associationsEvents = $em->getRepository("AppBundle:Event")->getEventsAssociationsByInterest($user, $enabled,
            $arrayuserinterestIds);
        $merchantsEvents = $em->getRepository("AppBundle:Event")->getEventsMerchantsByInterest($user, $enabled,
            $arrayuserinterestIds);


        $agendas = array_merge($associationsEvents, $merchantsEvents);
        usort($agendas, function ($a, $b) {
            if ($a->getStartAt() == $b->getStartAt()) {
                return 0;
            } else {
                return ($a->getStartAt() > $b->getStartAt()) ? 1 : -1;
            }
        });

        foreach ($agendas as $keyagenda => $agenda) {
            if ($agenda->getImage()) {
                $path = $helper->asset($agenda->getImage(), 'file');
                $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                if ($path) {
                    $agenda->setImageURL($baseurl.$path);
                }
            }

            if ($agenda->getPersonalized()) {
                if (!$em->getRepository("UserBundle:User")->getMatchedEventsPersonalized($user, $agenda)) {
                    unset($agendas[$keyagenda]);
                }
            }
            foreach ($agenda->getParticipants() as $value) {
                if ($value->getImage()) {
                    $path = $helper->asset($value->getImage(), 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $value->setImageURL($baseurl.$path);
                    }
                }
            }
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($agendas, $offset, $limit);

        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/event/citzenevent/filter/{city}/{datetime}/{page}/{limit}",
     * statusCodes={200="Successful"})
     */
    public function citzeneventFilterAction(Request $request, $city, $datetime, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $enabled = true;
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $userinterests = $user->getInterests();
        $arrayuserinterestIds = array();

        foreach ($userinterests as $userinterest) {
            $arrayuserinterestIds[] = $userinterest->getId();
        }

        $associationsEvents = $em->getRepository("AppBundle:Event")->getAllEventAssociationsByIntercom($user, $enabled,
            $city, $datetime, $arrayuserinterestIds);
        $merchantsEvents = $em->getRepository("AppBundle:Event")->getAllEventByMerchantsIntercom($user, $enabled, $city,
            $datetime, $arrayuserinterestIds);

        $events = array_merge($associationsEvents, $merchantsEvents);
        usort($events, function ($a, $b) {
            if ($a->getStartAt() == $b->getStartAt()) {
                return 0;
            } else {
                return ($a->getStartAt() > $b->getStartAt()) ? 1 : -1;
            }
        });

        foreach ($events as $event) {
            if ($event->getImage()) {
                $path = $helper->asset($event->getImage(), 'file');
                $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                if ($path) {
                    $event->setImageURL($baseurl.$path);
                }
            }
            foreach ($event->getParticipants() as $value) {
                if ($value->getImage()) {
                    $path = $helper->asset($value->getImage(), 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $value->setImageURL($baseurl.$path);
                    }
                }
            }
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($events, $offset, $limit);

        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/event/citzeneventall/interests/{page}/{limit}",
     * statusCodes={200="Successful"})
     */
    public function citzeneventallInterestsAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $enabled = true;
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $allcategorys = $em->getRepository("AppBundle:Category")->findAll();
        $userinterests = $user->getInterests();
        $arrayuserinterestIds = array();
        foreach ($userinterests as $userinterest) {
            $arrayuserinterestIds[] = $userinterest->getId();
        }
        $userinterestIds = array();
        foreach ($allcategorys as $allcategory) {
            if (!in_array($allcategory->getId(), $arrayuserinterestIds)) {
                $userinterestIds[] = $allcategory->getId();
            }
        }

        $events = $this->get('event.v3')->citzeneventallInterests($request, $user, $enabled, $userinterestIds,
            $page, $limit);

        return $events;
    }

    /**
     * @ApiDoc(resource="/api/event/citzenevent/personalized/{type}/{id}/{dayactivitymonday}/{dayactivitytuesday}/{dayactivitywednesday}/{dayactivitythursday}/{dayactivityfriday}/{dayactivitysaturday}/{dayactivitysunday}/{agefrom}/{ageto}/{lessThanSix}/{betweenSixTwelve}/{betweenTwelveEighteen}/{allChildrens}",
     * statusCodes={200="Successful"})
     */
    public function citzeneventPersonalizedAction(
        $type,
        $id,
        $dayactivitymonday,
        $dayactivitytuesday,
        $dayactivitywednesday,
        $dayactivitythursday,
        $dayactivityfriday,
        $dayactivitysaturday,
        $dayactivitysunday,
        $agefrom,
        $ageto,
        $lessThanSix,
        $betweenSixTwelve,
        $betweenTwelveEighteen,
        $allChildrens
    ) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($type == "association") {
            $account = $em->getRepository("AppBundle:Association")->find($id);
        } else {
            $account = $em->getRepository("AppBundle:Merchant")->find($id);
        }

        $city = $account->getCity()->getId();
        $category = $account->getCategory()->getId();
        $citizenForEventPersonalized = $em->getRepository("UserBundle:User")->findCitizenForEventPersonalizedByCity($city,
            $category, $agefrom, $ageto, $dayactivitymonday, $dayactivitytuesday, $dayactivitywednesday,
            $dayactivitythursday, $dayactivityfriday, $dayactivitysaturday, $dayactivitysunday, $lessThanSix,
            $betweenSixTwelve, $betweenTwelveEighteen, $allChildrens);

        return $citizenForEventPersonalized;
    }

    /**
     * @ApiDoc(resource="/api/event/{type}/{event}/edit",
     * description="Ce webservice permet d'ajouter un événement.",
     * statusCodes={200="Successful"})
     */
    public function editAction(Request $request, $type, Event $event)
    {
        $data = (array)json_decode($request->getContent());
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $this->checkPermissions($event, $user);

        try
        {
            $result = $this->get('event.v3')->update($data, $event);
        }
        catch(\Exception $ex)
        {
            $result =   false;
        }

        return array("success" => $result);
    }

    /**
     * @ApiDoc(resource="/api/event/{type}/{event}/editPrivate",
     * description="Ce webservice permet de modifier le champ private d'un événement.",
     * statusCodes={200="Successful"})
     */
    public function editPrivateAction(Request $request, $type, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$event) {
            throw $this->createNotFoundException(
                'No product found for id '.$event->getId()
            );
        }

        $event->setPrivate($type === 'true'? true: false);
        $em->flush();
        $arrayEvent =   array(
            "id"        =>  $event->getId(),
            "createAt"  =>  $event->getCreateAt()   ?   $event->getCreateAt()->format('d/m/Y')  :   "",
            "private"   =>  $event->getPrivate(),
            "state"     =>  $event->getState(),
            "type"      =>  $event->getType()
        );

        return array("success" => $arrayEvent);
    }
    /**
     * @ApiDoc(resource="/api/event/{id}/participants",
     * description="Ce webservice permet de récupérer les participants par événement.",
     * statusCodes={200="Successful"})
     */
    public function eventParticipantsAction(Request $request, $id)
    {
        $result = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CITIZEN')) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $participants = $em->getRepository("AppBundle:Event")->findParticipants($id);
        foreach ($participants as $participant) {
            if (isset($participant["image"])) {
                $img = $em->getRepository("AppBundle:File")->find($participant["image"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $participant["imageURL"] = $baseurl.$path;
                    }
                }
            } else {
                $participant["imageURL"] = "assets/img/user.jpg";
            }
            $result[] = $participant;
        }

        return $result;
    }

    public function eventVolonteersAction(Request $request, $id)
    {
        $result = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CITIZEN')) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $participants = $em->getRepository("AppBundle:Event")->findVolonteers($id);
        foreach ($participants as $participant) {
            if (isset($participant["image"])) {
                $img = $em->getRepository("AppBundle:File")->find($participant["image"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $participant["imageURL"] = $baseurl.$path;
                    }
                }
            } else {
                $participant["imageURL"] = "assets/img/user.jpg";
            }
            $result[] = $participant;
        }

        return $result;
    }

    public function eventTransportedAction(Request $request, $id)
    {
        $result = array();
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CITIZEN')) {
            throw $this->createAccessDeniedException();
        }
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $participants = $em->getRepository("AppBundle:Event")->findTransported($id);
        foreach ($participants as $participant) {
            if (isset($participant["image"])) {
                $img = $em->getRepository("AppBundle:File")->find($participant["image"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();
                    if ($path) {
                        $participant["imageURL"] = $baseurl.$path;
                    }
                }
            } else {
                $participant["imageURL"] = "assets/img/user.jpg";
            }
            $result[] = $participant;
        }

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/event/{id}",
     * description="consulter détails evénements",
     * statusCodes={200="Successful"})
     */
    public function viewAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if ($event->getImage()) {
            $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            $path = $helper->asset($event->getImage(), 'file');
            $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();

            if ($path) {
                $event->setImageURL($baseurl.$path);
            }
        } else {
            $event->setImageURL("assets/img/user_default.png");
        }

        $eventImages = $event->getImages();
        $images = array();
        foreach ($eventImages as $image) {
            $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
            $path = $helper->asset($image, 'file');
            $baseurl = $request->getScheme().'://'.$request->getHttpHost().$request->getBasePath();

            if ($path) {
                $data = array();
                $data['id'] = $image->getId();
                $data['imageURL'] = $baseurl.$path;
                $images[] = $data;
            }
        }

        $event->setEventImages($images);
        $categories = $event->getCategories();
        $categoryNames = array();

        foreach ($categories as $category) {
            $categoryNames[] = $category->getName();
        }

        $event->setCategoryNames($categoryNames);

        return $event;
    }

    /**
     * @param Request $request
     * @param $page
     * @param $limit
     * @return array
     */
    public function eventsAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_CITIZEN')) {
            throw $this->createAccessDeniedException();
        }

        $cities = $request->get('city');
        $categories = $request->get('category');
        $period = $request->get('date');
        $associationId = $request->get('associationId');
        $communityId = $request->get('communityId');

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $result = array();
        $resultWeekend = array();
        /** @var EventRepository $eventReposiory */
        $eventReposiory = $em->getRepository('AppBundle:Event');

        if ($associationId) {
            $associations[] = $em->getRepository('AppBundle:Association')->find($associationId);
            $privateAssociationsEvents = $eventReposiory->getEventsPrivateAssociations($associations, $cities,
                $categories, '2018-01-01',false);
            $publicAssocEventsByCommunity = [];
            $associationMebmbersEvents = $eventReposiory->getEventsAssociationsMembers($associations, $cities,
                $categories, '2018-01-01', false);
            $secondaryCommunitiesEvents = [];
            $communitiesEvents = [];

        }
        elseif ($communityId){
            $community[] = $em->getRepository('AppBundle:Community')->find($communityId);
            $publicAssocEventsByCommunity = $eventReposiory->getPublicAssocEventsByCommunity($community,
                $cities, $categories, '2018-01-01');
            $communitiesEvents = $eventReposiory->getCommunitiesEventsByUser($community, $cities, $categories,
                '2018-01-01');
            $privateAssociationsEvents = [];
            $associationMebmbersEvents = [];
            $secondaryCommunitiesEvents = [];
        }
        else{
            $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
            $joinedAssociations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
            $adminAssociations = $em->getRepository('AppBundle:Association')->findUserAssociations($user);
            $associations = array_merge($joinedAssociations, $adminAssociations);

            $publicAssocEventsByCommunity = $eventReposiory->getPublicAssocEventsByCommunity($followedCommunities,
                $cities, $categories, $period);

            $associationMebmbersEvents = $eventReposiory->getEventsAssociationsMembers($associations, $cities,
                $categories, $period, true);
            $privateAssociationsEvents = $eventReposiory->getEventsPrivateAssociations($associations, $cities,
                $categories, $period,true);
            $communitiesEvents = $eventReposiory->getCommunitiesEventsByUser($followedCommunities, $cities, $categories,
                $period);
            $secondaryCommunitiesEvents = $eventReposiory->getEventsSecondaryCommunities($followedCommunities, $cities,
                $categories, $period);
        }

        $events = array_map("unserialize", array_unique(array_map("serialize", array_merge($secondaryCommunitiesEvents, $publicAssocEventsByCommunity, $privateAssociationsEvents, $associationMebmbersEvents, $communitiesEvents))));
        $repoImg = $em->getRepository("AppBundle:File");
        foreach ($events as $event) {
            /** @var Event $currentEvent */
            $currentEvent = $em->getRepository("AppBundle:Event")->find($event['id']);
            $event["categories"] = [];
            foreach ($currentEvent->getCategories() as $cat) {
                $event["categories"][] = array("name"=>$cat->getName(),"id"=>$cat->getId());
            }
            if ($currentEvent->getPush()){
                $event["push"]['enabled'] = true;
                $event["push"]['date'] = $currentEvent->getPush()->getSendAt();

                $event["push"]['content'] = $currentEvent->getPush()->getContent();
            }else{
                $event["push"]['enabled'] = false;
                $event["push"]['date'] = "";

                $event["push"]['content'] = "";
            }
            if ($currentEvent->getParticipants()->contains($user)) {
                $event["isParticipated"] = true;
            } else {
                $event["isParticipated"] = false;
            }
            /** @var EventVolunteer $volunteer */
            $volunteer = $em->getRepository("AppBundle:EventVolunteer")->findOneBy(array('user'=>$user,'event'=>$currentEvent));
            $event["nbVolunteers"] = $currentEvent->getVolunteers()->count();
            if ($volunteer){
                $event["isVolunteer"] = true;
            }else{
                $event["isVolunteer"] = false;
            }
            $event['isParent'] = false;

            if ($currentEvent && $currentEvent->getDuplicatedEvents()->count() !== 0) {
                $event['isParent'] = true;
            }

            $event['hasParent'] = false;

            if ($currentEvent && $currentEvent->getParent()) {
                $event['hasParent'] = true;
            }

            $event["nbParticipants"] = $this->getNbrParticipants($em, $event['id']);
            $event["nbComments"] = $this->getNbrComments($em, $event['id']);
            $event["alreadyAddPool"] = 0;
            $event["alreadyPool"] = 0;
            $event["needCarpool"] = $em->getRepository('AppBundle:Carpooling')->getEventCarpool($currentEvent);
            $existAdd = $em->getRepository('AppBundle:Carpooling')->findBy(array('event'=>$event['id'],'createBy'=>$user));
            if ($existAdd){
                $event["alreadyAddPool"] = 1;
            }
            $event["nbCarpoolUsers"] = 0;

            foreach ($event["needCarpool"] as $key => $carpool){
                $carpoolingAnswers = $em->getRepository('AppBundle:CarpoolingAnswers')->findBy(array('carpooling'=> $carpool['id']));
                $event["nbCarpoolUsers"]+= count($carpoolingAnswers);
                $event["needCarpool"][$key]['placeLeft'] = $carpool['nbPlaces'] - count($carpoolingAnswers);
                $existAnswer = $em->getRepository('AppBundle:CarpoolingAnswers')->findBy(array('carpooling'=>$carpool['id'],'createBy'=>$user));
                if ($existAnswer){
                    $event["alreadyPool"] = 1;
                }
            }
            $images = $em->getRepository("AppBundle:Event")->find($event['id'])->getImages();
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

            if($currentEvent) {
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

            }else{
                $event['videoFile'] = null;
            }

            if (!isset($event["imageURL"]) && !isset($event['images'])) {
                $event["imageURL"] = "assets/img/user_default.png";
            }
            $event['day'] = $currentEvent->getStartAt();
            if ($this->isWeekend($currentEvent->getStartAt())) {
                $resultWeekend[] = $event;
            }
            $result[] = $event;
        }
        if ($period == "weekend") {
            usort($resultWeekend, function ($a, $b) {
                if ($a["day"] == $b["day"]) {
                    return 0;
                } else {
                    return ($a["day"] > $b["day"]) ? 1 : -1;
                }
            });

            $offset = ($page - 1) * $limit;
            $pagination = array_slice($resultWeekend, $offset, $limit);
        } else {
            usort($result, function ($a, $b) {
                if ($a["day"] == $b["day"]) {
                    return 0;
                } else {
                    return ($a["day"] > $b["day"]) ? 1 : -1;
                }
            });

            $offset = ($page - 1) * $limit;
            $pagination = array_slice($result, $offset, $limit);
        }

        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/addCarpollAnswer",
     * description="Ce webservice permet d'ajouter une réponse a un carpoll.",
     * statusCodes={200="Successful"})
     */
    public function addCarpollAnswerAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $user = $this->getUser();
        $carpooling = $em->getRepository("AppBundle:Carpooling")->find($id);
        $exist = $em->getRepository('AppBundle:CarpoolingAnswers')->findBy(array('carpooling'=>$carpooling,'createBy'=>$user));
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if (!$exist) {
            $carpoolingAnswers = new CarpoolingAnswers();
            $carpoolingAnswers->setCreateBy($user)
                ->setCreateAt(new DateTime());
            if ($data["phone"]) {
                $carpoolingAnswers->setPhoneNumber($data["phone"]);
            }
            $carpoolingAnswers->setCarpooling($carpooling);
            $this->container->get('mail')->sendCarpoolAnswerCreator($carpooling, $this->getUser(),$data["phone"]);
            $this->container->get('mail')->sendCarpoolAnswerUser($carpooling, $this->getUser());

            $em->persist($carpoolingAnswers);

            $em->flush();
        }

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/event/{id}/addCarpoll",
     * description="Ce webservice permet d'ajouter une réponse a un carpoll.",
     * statusCodes={200="Successful"})
     */
    public function addCarpollAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $user = $this->getUser();
        $event = $em->getRepository("AppBundle:Event")->find($id);
        $exist = $em->getRepository('AppBundle:Carpooling')->findBy(array('createBy'=>$user,'event'=>$event));

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        if (!$exist) {
            $carpooling = new Carpooling();
            $carpooling
                ->setPhoneNumber($data["phone"])
                ->setRelayPoint($data["rallyPoint"])
                ->setMeetingTime(\DateTime::createFromFormat('H:i', $data["rdvHours"]))
                ->setNbPlaces($data["seatedFree"])
                ->setCreateAt(new DateTime())
                ->setCreateBy($user)
                ->setEvent($event);

            $em->persist($carpooling);
            $em->flush();
            $this->container->get('mail')->sendCarpoolRecap($carpooling, $this->getUser());
        }

        return array("success" => true);
    }

    public function isWeekend($date)
    {
        $weekDay = date('w', strtotime($date->format('Y-m-d H:i:s')));

        return ($weekDay == 0 || $weekDay == 6);
    }

    public function getNbrComments($em, $id)
    {
        $event = $em->getRepository("AppBundle:Event")->find($id);

        return count($event->getComments());
    }

    public function getNbrParticipants($em, $id)
    {
        $event = $em->getRepository("AppBundle:Event")->find($id);

        return count($event->getParticipants());
    }

    public function getNbrVolunteers($em, $id)
    {
        $event = $em->getRepository("AppBundle:Event")->find($id);

        return count($event->getVolunteers());
    }

    public function enventProgressAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:Event")->findEventParticiped($id);

        return $event;
    }

    public function participantsNbreAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $output = "";
        foreach ($data['events'] as $event) {
            $entity = $em->getRepository("AppBundle:Event")->find($event->id);
            $entity->setParticipantsNbre($event->participantsNbre);
            $notification = $em->getRepository("AppBundle:Notification")->findOneBy(array(
                'event' => $entity,
                "participantsInformed" => true,
            ));
            if ($notification && $event->participantsNbre != "") {
                $em->remove($notification);
            }
            $em->flush();
        }

        return array('success' => true);
    }


    public function deleteCarpoolDemandAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $event = $entity = $em->getRepository("AppBundle:Event")->find($id);
        $carpoolings = $em->getRepository("AppBundle:Carpooling")->findBy(array("event" => $event));
        foreach ($carpoolings as $carpooling) {
            $demands = $em->getRepository("AppBundle:CarpoolingAnswers")->findBy(array("carpooling" => $carpooling, "createBy" => $user));
            foreach ($demands as $demand) {
                $em->remove($demand);
                $em->flush();
                $this->container->get('mail')->sendCancelCarpoolAnswerCreator($carpooling, $this->getUser());
                $this->container->get('mail')->sendCancelCarpoolAnswerUser($carpooling, $this->getUser());
            }
        }

        return array('success' => true);
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/duplicate",
     * description="Ce webservice permet de dupliquer un article.",
     * statusCodes={200="Successful"})
     */
    public function duplicateAction(Request $request, Event $parent)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $parent = $parent->getParent() ? $parent->getParent() :$parent;
        $type = $parent->getType();

        if ($type == "association") {
            $association = $parent->getAssociation();
            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "community") {
            $community = $parent->getCommunity();
            if (!$user->isCommunityAdmin($community) || !$community->getEnabled()) {
                if (!$user->isCommunitySuAdmin($community) || !$community->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        }

        $data = (array) json_decode($request->getContent());
        $this->get('event.v3')->duplicate($data, $parent, $user);

        return array("success" => true);
    }
}
