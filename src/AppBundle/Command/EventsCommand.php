<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;

class EventsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('nousensemble:finished:events')
                ->setDescription('Le cron tourne toutes les jours pour afficher une popin.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Demarrage");
        $em = $this->getContainer()->get('doctrine')->getManager();
        $events = $em->getRepository('AppBundle:Event')->getFinishedEvents();
        foreach ($events as $event) {
            $this->getContainer()->get('mobile')->pushNotification($event->getCreateBy(), '', '', $event->getId(), false, 'off', false, false, 'off', 'yes');
            $notification = $em->getRepository("AppBundle:Notification")->findOneBy(array('event'=>$event, "participantsInformed"=>true));
     
            if (!$notification) {
                $this->getContainer()->get('notification')->notify($event->getCreateBy(), 'event', "Vous n'avez pas encore renseigné le nombre des participants de l'événement ".$event->getTitle().".", false, $event, true);
            }
        }
        
        //maintenant les pushs mobile une heure avant la date de début d'un event
        $allEvents = $em->getRepository('AppBundle:Event')->search(false, array(), null, null, null, true, 'accepted', false, null, null, null, null, null, false, false, null);
        foreach ($allEvents as $event) {
            if ($event->getEndAt()) {
                $eventDate = $event->getEndAt();
                $eventDate->modify('+1 day');
                $date = $eventDate->format('m/d/Y');
                $now = new \DateTime();
                $now= $now->format('m/d/Y');
                if ($date == $now) {
                    foreach ($event->getParticipants() as $participant) {
                        $this->getContainer()->get('mobile')->pushNotification($participant, 'NOUS-ENSEMBLE ', 'Rappel ' . $event->getTitle() . " est terminé hier. N'oublier pas de mettre des photos et textes de commentaires.", $event->getId());
                    }
                }
            }
        }
        $output->writeln("Fin");
    }
}
