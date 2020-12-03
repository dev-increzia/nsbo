<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\ReportingObjectHeading;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use AppBundle\Entity\Reporting;
use AppBundle\Entity\File;
use Nahid\JsonQ\Jsonq;

class DefaultController extends Controller
{

    /**
     * @ApiDoc(resource="/api/number/categories",
     * description="API get categories",
     * statusCodes={200="Successful"})
     */
    public function numberCategoriesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("AppBundle:NumberCategory")->findBy([], ['name' => 'ASC']);

        return $categories;
    }

    /**
     * @ApiDoc(resource="/api/communities/{id}",
     * description="API get Community Name",
     * statusCodes={200="Successful"})
     */
    public function getCommunityNameAction($id)
    {
        $em =   $this->getDoctrine()->getManager();
        /** @var Community $community */
        $community  =   $em->getRepository("AppBundle:Community")->findOneById($id);
        if(!$community) {
            return array('success' => false);
        }
        return array(
            'success'   =>  true,
            'id'        =>  $id,
            'name'      =>  $community->getName(),
            'isPrivate' =>  $community->getIsPrivate()
        );
    }

    /**
     * @ApiDoc(resource="/api/number/LocalActor/{id}",
     * description="API get Local Actor",
     * statusCodes={200="Successful"})
     */
    public function localActorAction($id)
    {
      $tabResponse=[];
      $em = $this->getDoctrine()->getManager();
      $categories = $em->getRepository("AppBundle:CategoryPhoneBookHeading")->findLocalActorByHeading($id);

      foreach ( $categories as $item) {
        {
          $tabResponse[]= array('id'=>$item->getId(),'name'=>$item->getName());
        }
      }

      return $tabResponse;
    }

    /**
     * @ApiDoc(resource="/api/cities",
     * description="API get cities",
     * statusCodes={200="Successful"})
     */
    public function citiesAction()
    {
        $em = $this->getDoctrine()->getManager();

        $cities = $em->getRepository("AppBundle:City")->findAll();
        $path = $this->get('kernel')->getRootDir() . '/../src/ApiBundle/Resources/Data/cities.json';
        $cities = json_decode(file_get_contents($path),true);
        $output = array_slice($cities, 0, 800);

        return $cities;
    }

    /**
     * @ApiDoc(resource="/api/serachcities",
     * description="API get cities",
     * statusCodes={200="Successful"})
     */
    public function searchCitiesAction(Request $request)
    {
        $finaRes = [];
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $path = $this->get('kernel')->getRootDir() . '/../src/ApiBundle/Resources/Data/citiesFull.json';

        $jsonq = new Jsonq($path);
        $var=$data['pattern'];
        $res = $jsonq->from('.')
            ->whereStartsWith('name',$var)
            ->orWhere('code','startswith',$var)
            ->sortBy('name')
            ->get();

        foreach ($res as $v){
            $finaRes[]= $v;
        }

        return array_slice($finaRes,0,100);
    }

    /**
     * @ApiDoc(resource="/api/websiteCommunities",
     * description="API get all communities for website",
     * statusCodes={200="Successful"})
     */
    public function CommunityWebsiteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $private = $em->getRepository("AppBundle:Community")->findAllPrivateCommunitiesWebsite();
        $public = $em->getRepository("AppBundle:Community")->findAllPublicCommunitiesWebsite();

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

        return array('public'=>$public,'private'=>$private,'baseUrl'=> $baseurl);
    }

    /**
     * @ApiDoc(resource="/api/intercommunal/{city}",
     * description="API get intercommunal cities",
     * statusCodes={200="Successful"})
     */
    public function intercommunalCitiesAction($city)
    {
        $em = $this->getDoctrine()->getManager();
        $primaryCity = $em->getRepository('AppBundle:City')->findOneBy(array("name" => $city));

        $cities = $em->getRepository("AppBundle:City")->findIntercommunalCities($primaryCity);
        $names = array();
        foreach ($cities as $city) {
            $names[] = $city->getName();
        }

        return $names;
    }

    /**
     * @ApiDoc(resource="/api/reporting/categories",
     * description="Ce webservice permet de recupérer liste des articles d'une association ou merchant.",
     * statusCodes={200="Successful"})
     */
    public function categoriesReportingAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository("AppBundle:ReportingCategory")->findAll();

        return $categories;
    }

    /**
     * @ApiDoc(resource="/api/reportHeading/objects/{id}",
     * description="Ce webservice permet de recupérer liste des articles d'une association ou merchant.",
     * statusCodes={200="Successful"})
     * @param $id
     * @return ReportingObjectHeading[]
     */
    public function reportingHeadingObjectsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $reportHeading = $em->getRepository("AppBundle:ReportingHeading")->find($id);
        $categories = $em->getRepository("AppBundle:ReportingObjectHeading")->findObjectsByHeading($reportHeading);

        return $categories;
    }



    /**
     * @ApiDoc(resource="/api/reporting",
     * description="Ce webservice permet de recupérer liste des articles d'une association ou merchant.",
     * statusCodes={200="Successful"})
     */
    public function reportingAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $signalement = new Reporting();
        $signalement->setAddress($data["address"]);
        $signalement->setDescription($data["description"]);
        $signalement->setModerate("wait");
        $signalement->setTitle($data["title"]);

        // category
        $category = $em->getRepository("AppBundle:ReportingCategory")->find($data["category"]);
        $signalement->setCategory($category);

        // image
        $image = new File();
        $image->base64($data['image']);
        $signalement->setImage($image);
        $em->persist($signalement);
        $em->flush();

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/user/contact",
     * description="Ce webservice permet de traiter le formulaire de contact.",
     * statusCodes={200="Successful"})
     */
    public function contactAction(Request $request)
    {
      $filepath= null;
      $em = $this->getDoctrine()->getManager();

      $user = $this->getUser();
      $datas = $request->getContent();
      $data = (array) json_decode($datas);

      /** @var ReportingObjectHeading $object */
      $objectTmp = $em->getRepository("AppBundle:ReportingObjectHeading")->find($data["object"]);
      $object = $objectTmp->getObjet();
      $content = $data["description"];
      $document = $data['document'];

      if(!empty($document)) {
        $doc = new File();
        $doc->base64($document);
        $em->persist($doc);
        $em->flush();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $filepath = $request->server->get('DOCUMENT_ROOT').$request->getBasePath().$helper->asset($doc, 'file');

      }

      $location = isset($data["location"]) ? $data['location'] : null;

      if (isset($data["photo"])) {
        $image = new File();
        $image->base64($data['photo']);
        $em->persist($image);
        $em->flush();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $path = $helper->asset($image, 'file');
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $photo = $baseurl . $path;
      } else {
        $photo = null;
      }

      $to = $objectTmp->getRecipient();
      $body = $this->renderView('AppBundle:Mail:contact.html.twig', array("message"=>array('msn' => $object,
        'user' => $user,
        'content' => $content,
        'photo' => $photo,
        'location' => $location,))
      );

      $body2 = $this->renderView('AppBundle:Mail:contact.html.twig', [
        'msn' => $object,
        'user' => $user,
        'content' => $content,
        'photo' => null,
        'location' => $location
      ]);

      $this->container->get('mail')->contactMail($body,  $to, $object,$filepath);
      $this->container->get('mail')->contactConfirmationMail($body2, $object, $user);
      return array("success" => true);
    }

    /**
     * * @ApiDoc(resource="/api/interest/category/{id}",
     * description="Ce webservice permet de récuperer les catégorie des centres d'interêt du carte pratique",
     * statusCodes={200="Successful"})
     * @param Request $request
     * @param integer $id
     * @return type
     */
    public function interestCategoriesAction(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();
      $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
      $interests = $em->getRepository("AppBundle:InterestCategory")->findBy(array('mapHeading'=>$id));

      foreach ($interests as $interest) {
        if ($interest->getImage()) {
          $path = $helper->asset($interest->getImage(), 'file');
          $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

          if ($path) {
            $interest->setImageURL($baseurl . $path);
          }
        }
      }

      return $interests;
    }

    public function postVideoAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $file = $request->files->get('filename');
      $myFile = new File();
      $myFile->setFile($file);
      $em->persist($myFile);
      $em->flush();

      return $myFile;
    }
}
