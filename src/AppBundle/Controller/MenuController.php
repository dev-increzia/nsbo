<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArticleHeading;
use AppBundle\Entity\Community;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UserBundle\Entity\User;

/**
 * Class MenuController
 * @package AppBundle\Controller
 */
class MenuController extends Controller
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sidebarAction()
    {
        /** @var Community $community */
        $community = $this->container->get('session.community')->getCommunity();
        /** @var User $user */
        $user= $this->getUser();
        $hasArticleHeadingCount = 0;
        $hasArticleHeading = false;
        if($community){
            /** @var ArticleHeading[] $headings */
            $headings = $community->getArticleHeadings();
            foreach ($headings as $heading)
            {
                if(($heading->getEmailAdmin() == $user->getEmail() || $heading->getAdmins()->contains($user)) && $heading->getEnabled())
                {
                    $hasArticleHeadingCount ++;
                }
            }
            if($hasArticleHeadingCount > 0) {
                $hasArticleHeading = true;
            }
        }

        return $this->render('AppBundle:Menu:sidebar.html.twig', array(
            'community' => $community,
            'hasArticleHeading' => $hasArticleHeading
        ));
    }

}