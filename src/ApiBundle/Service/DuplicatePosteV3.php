<?php
namespace ApiBundle\Service;

use AppBundle\Entity\Article as Article;
use AppBundle\Entity\DuplicatePoste as Duplicate;
use AppBundle\Entity\Abus;
use AppBundle\Entity\Association;
use AppBundle\Entity\Community;
use AppBundle\Entity\File;
use AppBundle\Entity\Merchant;
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\SurveyQuestionChoiceRepository;
use AppBundle\Repository\SurveyQuestionRepository;
use AppBundle\Repository\SurveyRepository;
use AppBundle\Repository\SurveyResponseRepository;
use Doctrine\Common\Util\Debug; 
use Symfony\Component\HttpFoundation\Request;

class DuplicatePosteV3
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function view($request, $id)
    {
      
    }

    

}
