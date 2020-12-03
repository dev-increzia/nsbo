<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;

class LikesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('nousensemble:likes')
                ->setDescription('Le cron tourne toutes les 10 minutes pour envoyer des notifications pushs.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Demarrage");
        /*$em = $this->getContainer()->get('doctrine')->getManager();
        $likes = $em->getRepository('AppBundle:ArticleLikes')->findAll();
        $date = new \DateTime('now');
        $articles = array();
        foreach ($likes as $like) {
            if (strtotime($date->format('Y-m-d H:i:s'))  - strtotime($like->getCreateAt()->format('Y-m-d H:i:s')) <= 600) {
                $articles[]= $like->getArticle()->getId();
            }
        }
        $occurrence = array_count_values($articles);
        foreach ($occurrence as $key => $value) {
            $article = $em->getRepository('AppBundle:Article')->find($key);
            $message = ($value == 1) ? $value." nouvelle personne a aimé votre article ". $article->getTitle(): $value." nouvelles personnes ont aimé votre article ". $article->getTitle() ;
            $this->getContainer()->get('mobile')->pushNotification($like->getArticle()->getCreateBy(), 'NOUS-ENSEMBLE ', $message, false, $article->getId());
            $this->getContainer()->get('notification')->notify($article->getCreateBy(), 'article', $message, false, $article);
            $this->getContainer()->get('mobile')->pushNotification($article->getCreateBy(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
        }*/
        $output->writeln("Fin");
    }
}
