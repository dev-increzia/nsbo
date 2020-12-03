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

class FileCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this   ->setName('nousensemble:file')
                ->setDescription('Le cron tourne toutes les jours a minuits pour supprimer toute les fichiers dana tailles est plus que 500 Ko et la date de parution est plus que 3 mois.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("Demarrage");
        $em = $this->getContainer()->get('doctrine')->getManager();
        $files = $em->getRepository('AppBundle:File')->findAll();
        $date = new \DateTime('now');
        $lastThreeMonths = $date->modify('-3 month');
        foreach ($files as $file) {
            /** @var File $file */
            if($file->getFile()) {
                $path = $file->getFile()->getPathname();
                if(file_exists ($path)) {
                    $fileDate = new \DateTime(date("Y-m-d H:i:s", $file->getFile()->getCTime()) );
                    if ($fileDate <= $lastThreeMonths) {
                        $size = filesize($path);
                        if($size>=500000) {
                            $em->remove($file);
                        }
        
                    }
                }
            }
        }
        try {
            $em->flush();
        } catch (\Exception $ex) {
            $output->writeln("Un erreur est survenue");
        }
        $output->writeln("Fin");
    }
}
