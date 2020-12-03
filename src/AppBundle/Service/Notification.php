<?php

namespace AppBundle\Service;

use AppBundle\Entity\Notification as NotificationEntity;

class Notification
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param $user
     * @param $type
     * @param $message
     * @param $seen
     * @param null $parent
     * @param bool $informed
     */
    public function notify($user, $type, $message, $seen, $parent = null, $informed = false)
    {
        $em = $this->container->get('doctrine')->getManager();
        $notification = new NotificationEntity();
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setUser($user);
        $notification->setSeen($seen);
        $notification->setParticipantsInformed($informed);

        if ($type == 'event' || $type == 'eventDisabled' || $type == 'eventParticipateAdd' || $type == 'eventParticipateAdd' || $type == 'volunteer') {
            $notification->setEvent($parent);
        } elseif ($type == 'article') {
            $notification->setArticle($parent);
        } elseif ($type == 'association' || $type == 'associationRefused') {
            $notification->setAssociation($parent);
        } elseif ($type == 'merchant' || $type == 'merchantRefused') {
            $notification->setMerchant($parent);
        } elseif ($type == 'newComment' || $type == 'replyComment') {
            $notification->setComment($parent);
        } elseif ($type == 'goodPlan') {
            $notification->setGoodPlan($parent);
        }

        $em->persist($notification);
        $em->flush();
    }
}
