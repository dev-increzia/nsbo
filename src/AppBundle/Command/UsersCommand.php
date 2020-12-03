<?php

namespace AppBundle\Command;

use AppBundle\Entity\City;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUsers;
use MyProject\Proxies\__CG__\stdClass;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;
use League\Csv\Reader;
use Symfony\Component\Console\Style\SymfonyStyle;
use UserBundle\Entity\User;

class UsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('nousensemble:load:users')
                ->setDescription('Le cron tourne toutes les jours pour afficher une popin.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->warning("Démarrage");
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var User[] $users */
        $users = $em->getRepository('UserBundle:User')->findAll();
        foreach ($users as $row) {
            /** @var Community $userAdmin */
            $userAdmin = $row->getCommunityAdmin();
            /** @var Community $userSuAdmin */
            $userSuAdmin = $row->getCommunitySuAdmin();
            if($userAdmin && !$userSuAdmin){

                $row->addAdminCommunity($userAdmin);
                $userAdmin->addCommunityAdmin($row);
                $io->writeln("Admin: ".$row->getEmail()." - Community".$userAdmin->getName());
            }
            elseif($userSuAdmin){
                $row->addSuAdminCommunity($userSuAdmin);
                $userSuAdmin->addCommunitySuadmin($row);
                $io->writeln("SuAdmin: ".$row->getEmail()." - Community".$userSuAdmin->getName());
            }


        }
        $em->flush();

        $io->warning("Fin");
    }
}
