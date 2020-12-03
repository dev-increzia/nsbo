<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;

class CommunitiesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('nousensemble:Community:passwords')
                ->setDescription('Le cron tourne toutes les 10 minutes pour envoyer des notifications pushs.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $output->writeln("Demarrage Réinitialisation du mot de passe des communauté privées");
        $datetime = new \DateTime('now');
        $communities = $em->getRepository('AppBundle:Community')->findBy(array('expirationDate' => $datetime));
        foreach ($communities as $community) {
            $communityPassword = rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90)) . rand(0, 9) . chr(rand(65, 90));
            $community->setPassword($communityPassword);
            $validity = $this->getContainer()->getParameter('community_validity');
            $expirationDate = new \DateTime('now');
            $expirationDate->modify('+' . $validity . ' day');
            $community->setExpirationDate($expirationDate);
        }
        $em->flush();
        
        $output->writeln("Fin");


        $output->writeln("Demarrage Suppression des notifications de plus qu'une semaine");
        $notifications = $em->getRepository('AppBundle:Notification')->deleteMoreThanWeek();
        $output->writeln($notifications . " deleted");
        $output->writeln("Fin");


        $output->writeln("Demarrage Suppression des fichiers > 500 Ko et qui dépassent 3 mois");

        $docs = $em->getRepository('AppBundle:File')->getDocsMoreThanThreeMonths();
        $appPath = $this->getContainer()->getParameter('kernel.root_dir');
        $helper = $this->getContainer()->get('vich_uploader.templating.helper.uploader_helper');

        foreach ($docs as $doc) {
            $filePath = $appPath.'/../public'.$helper->asset($doc, 'file');
            if(file_exists($filePath)  && filesize($filePath) > 512000) {
                unlink($filePath);
                $output->writeln("Delete File ".$filePath);
                $em->remove($doc);
                $em->flush();
            }


        }

        $output->writeln("Fin");


    }
}
