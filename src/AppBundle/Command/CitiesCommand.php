<?php

namespace AppBundle\Command;

use AppBundle\Entity\City;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;
use League\Csv\Reader;
use Symfony\Component\Console\Style\SymfonyStyle;
use Nahid\JsonQ\Jsonq;

class CitiesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('nousensemble:load:cities')
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
        $file = __DIR__ . '/../../../web/upload/cities.json';

        $jsonq = new Jsonq($file);
        //dump($jsonq);
        $res = $jsonq->from('.')
            ->whereContains('name','pa')
            ->get();
        dump($res);

        /*$em = $this->getContainer()->get('doctrine')->getManager();
        $reader = Reader::createFromPath('%kernel.root_dir%/../public/upload/villes.csv');
        $reader->setDelimiter(";");
        $results = $reader->fetchAssoc();

        foreach ($results as $row) {
            $exist = $em->getRepository('AppBundle:City')->findOneBy(array('name'=>utf8_encode($row['Ville']),'zipcode'=>$row['Zip']));
            if(!$exist){
                $city = (new City())
                    ->setName(utf8_encode($row['Ville']))

                    ->setZipcode($row['Zip']);


                $em->persist($city);
                $em->flush();
                $io->success(utf8_encode($row['Ville']));
            }else{
                $io->warning(utf8_encode($row['Ville']));
            }

        }*/

        $io->warning("Fin");
    }
}
