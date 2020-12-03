<?php

namespace AppBundle\Command;

use AppBundle\Entity\CommunityUsers;
use AppBundle\Entity\Push;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;
use UserBundle\Entity\User;

class PushCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('nousensemble:push')
            ->setDescription('Le cron tourne toutes les 5 minutes pour envoyer des notifications pushs.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Demarrage");
        $em = $this->getContainer()->get('doctrine')->getManager();
        $pushs = $em->getRepository('AppBundle:Push')->findAll();
        $date = new \DateTime('now');
        /** @var User[] $users */
        $users = $em->getRepository('UserBundle:User')->search(false, array(), null, array('ROLE_CITIZEN'), null, null, null, null, null, null);
        foreach ($pushs as $push) {
            /** @var Push $push */
            if ($push->getCommunity()) {
                $diff = $date->diff($push->getSendAt());
                $seconds = ($diff->format('%a') * 86400) + // total days converted to seconds
                    ($diff->format('%h') * 3600) +   // hours converted to seconds
                    ($diff->format('%i') * 60) +   // minutes converted to seconds
                    $diff->format('%s');          // seconds
                if (($date >= $push->getSendAt()) && ($seconds < 300)) {
                    if ($push->getType() == 'event') {
                        if ($push->getEvent() && $push->getEvent()->getEnabled() && $push->getEvent()->getModerate() == 'accepted') {
                            if ($push->getEvent()->getType() == 'association') {
                                if ($push->getEvent()->getAssociation() && $push->getEvent()->getAssociation()->getEnabled() && $push->getEvent()->getAssociation()->getModerate() == 'accepted') {
                                    $category = $push->getEvent()->getAssociation()->getCategory() ? $push->getEvent()->getAssociation()->getCategory() : false;

                                    foreach ($users as $user) {
                                        if ($push->getEvent()->getPrivate()) {
                                            $joinedAssociations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
                                            if (in_array($push->getEvent()->getAssociation(), $joinedAssociations)) {
                                                $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent(), $push->getEvent()->getId());
                                            }
                                        } else {
                                            $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
                                            if (in_array($push->getEvent()->getCommunity(), $followedCommunities)) {
                                                $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent(), $push->getEvent()->getId());
                                            }

                                        }

                                    }
                                }
                            } elseif ($push->getType() == 'community') {
                                //tous les users de la meme communauté
                                foreach ($users as $user) {
                                    /** @var CommunityUsers $commUsers */
                                    $commUsers = $user->getCommunities();
                                    foreach ($commUsers as $c){
                                        if ($c->getCommunity() == $push->getCommunity()) {
                                            $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent());
                                        }
                                    }

                                }
                            }
                        }
                    } else if ($push->getType() == 'article') {
                        if ($push->getArticle() && $push->getArticle()->getEnabled()) {
                            if ($push->getArticle()->getType() == 'association') {
                                if ($push->getArticle()->getAssociation() && $push->getArticle()->getAssociation()->getEnabled() && $push->getArticle()->getAssociation()->getModerate() == 'accepted') {
                                    $category = $push->getArticle()->getAssociation()->getCategory() ? $push->getArticle()->getAssociation()->getCategory() : false;
                                    foreach ($users as $user) {
                                        if ($push->getArticle()->getPrivate()) {
                                            $joinedAssociations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
                                            if (in_array($push->getArticle()->getAssociation(), $joinedAssociations)) {
                                                $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent(), $push->getArticle()->getId());
                                            }
                                        } else {
                                            $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
                                            if (in_array($push->getArticle()->getCommunity(), $followedCommunities)) {
                                                $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent(), $push->getArticle()->getId());
                                            }
                                        }
                                    }
                                }
                            } elseif ($push->getArticle()->getType() == 'merchant') {
                                if ($push->getArticle()->getAssociation() && $push->getArticle()->getAssociation()->getEnabled() && $push->getArticle()->getAssociation()->getModerate() == 'accepted') {
                                    $category = $push->getArticle()->getAssociation()->getCategory() ? $push->getArticle()->getAssociation()->getCategory() : false;
                                    $merchantUsers = $push->getArticle()->getMerchant()->getUsers();
                                    foreach ($merchantUsers as $merchantUser) {
                                        if($merchantUser->getType() == 'approved') {
                                            $user = $merchantUser->getUser();
                                            $joinedMerchants = $em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
                                            if (in_array($push->getArticle()->getMerchant(), $joinedMerchants)) {
                                                $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent(), $push->getArticle()->getId());
                                            }
                                        }
                                    }
                                }
                            } elseif ($push->getType() == 'community' || $push->getArticle()->getType() == 'user') {
                                //tous les users de la meme communauté
                                foreach ($users as $user) {
                                    /** @var CommunityUsers $commUsers */
                                    $commUsers = $user->getCommunities();
                                    foreach ($commUsers as $c){
                                        if ($c->getCommunity() == $push->getCommunity()) {
                                            $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent());
                                        }
                                    }

                                }
                            }
                        }
                    } else if ($push->getType() == 'goodPlan') {
                        if ($push->getGoodPlan() && $push->getGoodPlan()->getEnabled()) {
                            foreach ($push->getGoodPlan()->getParticipants() as $user) {
                                $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent(), $push->getGoodPlan()->getId());
                            }
                        }
                    }else{
                        foreach ($users as $user) {
                            /** @var CommunityUsers $commUsers */
                            $commUsers = $user->getCommunities();
                            foreach ($commUsers as $c){
                                if ($c->getCommunity() == $push->getCommunity()) {
                                    $this->sendNotification($em, $push, $user, 'NOUS Ensemble', $push->getContent());
                                }
                            }
                        }
                    }
                }
            }
        }


        //maintenant les pushs mobile une heure avant la date de début d'un event
        $events = $em->getRepository('AppBundle:Event')->search(false, array(), null, null, null, true, 'accepted', false, null, null, null, null, null, false, false, null);
        foreach ($events as $event) {
            if ($event->getStartAt()) {
                if (($event->getStartAt()->getTimestamp() - 3600) <= $date->getTimestamp() && (($event->getStartAt()->getTimestamp() - 3600) + 300) >= $date->getTimestamp()) {
                    $em->flush();
                    foreach ($event->getParticipants() as $participant) {
                        $this->getContainer()->get('mobile')->pushNotification($participant, 'NOUS-ENSEMBLE ', 'Rappel ' . $event->getTitle() . ' débute dans une heure. Nous vous attendons.', $event);
                    }
                }
            }
        }
        $output->writeln("Fin");
    }

    private function sendNotification($em, $push, $user, $title, $message, $eventId = false)
    {
        //save log
        $log = new PushLog();
        $log->setPush($push);
        $log->setUser($user);
        $em->persist($log);
        $em->flush();
        //push mobile notification
        $this->getContainer()->get('mobile')->pushNotification($user, $title, $message, $eventId);
    }
}
