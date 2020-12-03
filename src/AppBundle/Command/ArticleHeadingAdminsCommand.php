<?php
/**
 * Created by PhpStorm.
 * User: medamine.ab
 * Date: 21/02/2019
 * Time: 10:09
 */

namespace AppBundle\Command;


use AppBundle\Entity\ArticleHeading;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\PushLog;
use UserBundle\Entity\User;


class ArticleHeadingAdminsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('nousensemble:article:admins')
            ->setDescription('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Demarrage");
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var ArticleHeading[] $articleHeadings */
        $articleHeadings = $em->getRepository('AppBundle:ArticleHeading')->findAll();

        foreach ($articleHeadings as $articleHeading) {

            /** @var User $admin */
            $admin = $em->getRepository("UserBundle:User")->findOneBy(array('email'=>$articleHeading->getEmailAdmin()));

            if($admin and !$admin->getArticleHeadings()->contains($articleHeading)) {
                $articleHeading->addAdmin($admin);
                $admin->addArticleHeading($articleHeading);
                $output->writeln($admin->getEmail()." ==== ".$articleHeading->getTitle());
            }


        }
        $em->flush();

        $output->writeln("Fin");
    }

}