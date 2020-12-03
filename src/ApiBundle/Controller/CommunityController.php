<?php

namespace ApiBundle\Controller;


use AppBundle\Entity\Community;
use AppBundle\Repository\CommunityRepository;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommunityController extends Controller
{
    public function getInfosDedicatedAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $tabResponse = [];
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        /** @var Community $community */
        $community = $em->getRepository("AppBundle:Community")->find($id);
        if(!$community)
        {
            return array('success' => false);
        }

        if($community->getVideo()){
            $image = $em->getRepository("AppBundle:File")->find($community->getVideo()->getId());
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $tabResponse['videoFile'] = $baseurl . $path;
                }
            }
        }else{
            $tabResponse['videoFile'] = null;
        }

        $images = $community->getImages();
        foreach ($images as $value) {
            $image = $em->getRepository("AppBundle:File")->find($value);
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $tabResponse['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                }
            }
        }


        if ($community->getPresentationImage() != null) {
            $tabResponse['blason'] = $request->getScheme() . '://' .$request->getHttpHost() . $request->getBasePath() . $helper->asset($community->getPresentationImage(), 'file');
        } else {
            $tabResponse['blason'] = $request->getScheme() . '://' .$request->getHttpHost() .$this->container->get('assets.packages')->getUrl('bundles/app/images/user_default100.png');
        }

        /** @var CommunitySetting[] $settings */
        $settings= $community->getSettings();
        $comment_allowed = false;
        if(count($settings) != 0){
            foreach($settings as $setting)
            {
                if($setting->getSlug()=='activate_comments')
                {
                    $comment_allowed = true;
                }
            }
        }
        $tabResponse['comment_allowed'] = $comment_allowed;
        $tabResponse['comment_article_heading_allowed'] = $community->getIsCommentActive();


        $tabResponse['title'] = $community->getPresentationTitle();
        $tabResponse['description'] = $community->getPresentationDescription();
        return $tabResponse;

    }

    public function getHeadingsDedicatedAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Community $community */
        $community = $em->getRepository("AppBundle:Community")->find($id);
        if(!$community)
        {
            return array('success' => false);
        }
        /** @var CommunityRepository $repo */
        $repo = $em->getRepository("AppBundle:ArticleHeading");
        $articleHeadings = $repo->findArticleheadingsByCommunity($community);

        $repoMap = $em->getRepository("AppBundle:MapHeading");
        $mapHeadings = $repoMap->findArticleheadingsByCommunity($community);

        $repoPhoneBook = $em->getRepository("AppBundle:PhoneBookHeading");
        $phoneBookHeadings = $repoPhoneBook->findArticleheadingsByCommunity($community);

        $repoReporting = $em->getRepository("AppBundle:ReportingHeading");
        $reportingHeadings = $repoReporting->findArticleheadingsByCommunity($community);

        $repoUsefullLink = $em->getRepository("AppBundle:UsefullLinkHeading");
        $usefullLinkHeadings = $repoUsefullLink->findArticleheadingsByCommunity($community);

        $tabResult=array(   'articleHeadings'       => $articleHeadings,
                            'mapHeadings'           => $mapHeadings,
                            'phoneBookHeadings'    => $phoneBookHeadings,
                            'reportingHeadings'    => $reportingHeadings,
                            'usefullLinkHeadings'  => $usefullLinkHeadings);

        return $tabResult;

    }

    /**
     * @ApiDoc(resource="/api/community/{id}/update",
     * description="API update community",
     * statusCodes={200="Successful"})
     */
    public function updateAction($id, Request $request)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $datas = $request->getContent();

        $data = (array) json_decode($datas);

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $community = $em->getRepository('AppBundle:Community')->find($id);
        $imageUrl = null;

        if (!$community) {
            return array("success" => false);
        }

        if (!$community->getAdmins()->contains($user) || !$community->getEnabled()) {
            if ( $community->getSuAdmin() != $user  || !$community->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }


        $community->setName($data['name']);
        $community->setPhone($data['phone']);
        /*$category = $em->getRepository('AppBundle:Category')->find($data['category']);
        $association->setCategory($category);*/
        $community->setEmail($data['email']);
        $city = $em->getRepository('AppBundle:City')->find($data['city']);
        $community->setCity($city);

        if (!empty($data['photo'])) {
            $image = new File();

            $image->base64($data['photo']);

            $community->setImage($image);
        } elseif ($data["todelete"]) {
            $community->setImage(null);
        }

        $em->flush();

        if ($community->getImage()) {
            $path = $helper->asset($community->getImage(), 'file');

            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

            if ($path) {
                $community->setImageURL($baseurl . $path);
                $imageUrl = $baseurl . $path;
            }
        }


        return array("success" => true,'imageUrl' => $imageUrl );
    }

    /**
     * @ApiDoc(resource="/api/community/{id}/admins/{page}/{limit}",
     * description="API get Community admins",
     * statusCodes={200="Successful"})
     */
    public function adminsAction($id, Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $community = $em->getRepository("AppBundle:Community")->find($id);

        if (!$community) {
            return array("success" => false);
        }

        if (!$community->getAdmins()->contains($user) || !$community->getEnabled()) {
            if ($community->getSuAdmin() != $user || !$community->getEnabled()){
                throw $this->createAccessDeniedException();
            }
        }

        $admins = $community->getAdmins();
        $adminsFormated = [];

        foreach ($admins as $admin) {
            if ($admin->getImage()) {
                $path = $helper->asset($admin->getImage(), 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $admin->setImageURL($baseurl . $path);
                }
            }
            $adminsFormated[]= array('id'=> $admin->getId(),'lastname' => $admin->getLastname(),'firstname' => $admin->getFirstname());
        }

        $offset = ($page - 1) * $limit;

        $pagination = array_slice($adminsFormated, $offset, $limit);

        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/community/{community}/admin/remove",
     * description="API get Merchant volunteers",
     * statusCodes={200="Successful"})
     */
    public function removeAdminsAction(Community $community, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $result = $this->get('community.v3')->removeAdmins($request, $em, $user, $community, $data);


        return $result;
    }


}
