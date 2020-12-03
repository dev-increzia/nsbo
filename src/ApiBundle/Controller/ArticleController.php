<?php
namespace ApiBundle\Controller;

use ApiBundle\Service\ArticleV3;
use AppBundle\Entity\Association;
use AppBundle\Entity\Community;
use AppBundle\Repository\ArticleRepository;
use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerBuilder;
use AppBundle\Entity\ArticleLikes;
use UserBundle\Entity\User;
use AppBundle\Entity\Article;

class ArticleController extends Controller
{

    /**
     * @ApiDoc(resource="/api/article/citzen/home/{page}/{limit}",
     * description="Ce webservice permet de recupérer liste des articles des association, commercant, utilisateurs et mairies.",
     * statusCodes={200="Successful"})
     */
    public function citzenHomeAction(Request $request, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        /** @var ArticleV3 $articleV3 */
        $articleV3 = $this->get('article.v3');

        $assoId = $request->get('assoId');
        $commuId = $request->get('communityId'); 
        $survey = $request->get('survey');

        if ($assoId){
            $pagination = $articleV3->associations($request, $assoId, $user, $page, $limit);
        } elseif ($commuId) {
            if ($survey){
                $pagination = $articleV3->surveyCacheCommunity($request, $commuId, $user);
            } else {
                $pagination = $articleV3->associations($request, $commuId, $user, $page, $limit, true);
            }
        } else {
            $pagination = $articleV3->citzenHome($request, $user, $page, $limit);
        }

        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/association/{page}/{limit}",
     * description="Ce webservice permet de recupérer liste des articles d'une association.",
     * statusCodes={200="Successful"})
     */
    public function associationsAction(Request $request, $id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $articles = $this->get('article.v3')->associations($request, $id, $user, $page, $limit);

        return $articles;
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/merchant/{page}/{limit}",
     * description="Ce webservice permet de recupérer liste des articles d'un commercant.",
     * statusCodes={200="Successful"})
     */
    public function merchantsAction(Request $request, $id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $articles = $this->get('article.v3')->merchants($request, $id, $user, $page, $limit);

        return $articles;
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/activate",
     * description="Ce webservice permet d'activer un article.",
     * statusCodes={200="Successful"})
     */
    public function activateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $article = $em->getRepository("AppBundle:Article")->find($id);
        $type = $article->getType();

        if ($type == "association") {
            $association = $article->getAssociation();
            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "merchant") {
            $merchant = $article->getMerchant();
            if (!$merchant->getAdmins()->contains($user)|| !$merchant->getEnabled()) {
                if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } else {
            throw $this->createAccessDeniedException();
        }
        $result = $this->get('article.v3')->activate($id);

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/deactivate",
     * description="Ce webservice permet de desactiver un article.",
     * statusCodes={200="Successful"})
     */
    public function deactivateAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $article = $em->getRepository("AppBundle:Article")->find($id);
        $type = $article->getType();
        if ($type == "association") {
            $association = $article->getAssociation();
            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "merchant") {
            $merchant = $article->getMerchant();
            if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
                if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } else {
            throw $this->createAccessDeniedException();
        }
        $result = $this->get('article.v3')->deactivate($id);

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/new",
     * description="Ce webservice permet d'ajouter un article.",
     * statusCodes={200="Successful"})
     */
    public function newAction(Request $request, $type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($type == "association") {
            $association = $em->getRepository("AppBundle:Association")->find($id);
            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "community") {
            $community = $em->getRepository("AppBundle:Community")->find($id);
            if (!$user->isCommunityAdmin($community) || !$community->getEnabled()) {
                if (!$user->isCommunitySuAdmin($community) || !$community->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        }
        $article = $this->get('article.v3')->newArticle($request, $type, $id, $user);

        return $em->getRepository("AppBundle:Article")->findArticle($article->getId());
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/update",
     * description="Ce webservice permet de mettre à jour un article.",
     * statusCodes={200="Successful"})
     */
    public function updateAction(Request $request, Article $article)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        if ($article->getType() == "association") {
            $association = $article->getAssociation();

            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    if ($article->getUser() != $user) {
                        throw $this->createAccessDeniedException();
                    }
                }
            }
        } elseif ($article->getType() == "merchant") {
            $merchant = $article->getMerchant();

            if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
                if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                    if ($article->getUser() != $user) {
                        throw $this->createAccessDeniedException();
                    }
                }
            }
        }

        $data = (array) json_decode($request->getContent());
        $result = $this->get('article.v3')->update($data, $article, $user);

        return $em->getRepository("AppBundle:Article")->findArticle($article->getId());
    }

    /**
     * @ApiDoc(resource="/api/article/view/{id}",
     * description="Ce webservice permet de récuperer les informations d'un article d'une association.",
     * statusCodes={200="Successful"})
     */
    public function viewAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $articles = $this->get('article.v3')->view($request, $id, $user);

        return $articles;
    }

    /**
     * @ApiDoc(resource="/api/article/delete/{id}",
     * description="Ce webservice permet de supprimer un article.",
     * statusCodes={200="Successful"})
     */
    public function deleteAction(Request $request, Article $article)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $this->checkPermissions($article, $user);
        $mode = $request->get('mode', 'current');
        try{
            $result = $this->get('article.v3')->delete($mode, $article, $apiVersion);
        } catch (\Exception $exception){
            return array("success"  =>  false);
        }

        return array("success"  =>  $result);
    }

    private function checkPermissions(Article $article, User $user)
    {
        $type = $article->getType();

        if ($type == "association") {
            $association = $article->getAssociation();
            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "merchant") {
            $merchant = $article->getMerchant();
            if (!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) {
                if ($merchant->getSuAdmin() != $user || !$merchant->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "community") {
            $community = $article->getCommunity();
            if (!$user->isCommunityAdmin($community) || !$community->getEnabled()) {
                if (!$user->isCommunitySuAdmin($community) || !$community->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "user") {
            $community = $article->getCommunity();
            $merchant = $article->getMerchant();
            $association = $article->getAssociation();

            if ($association) {
                if ((!$association->getAdmins()->contains($user) || !$association->getEnabled()) && ($article->getCreateBy() != $user)) {
                    if (($association->getSuAdmin() != $user || !$association->getEnabled()) && ($article->getCreateBy() != $user)){
                        throw $this->createAccessDeniedException();
                    }
                }
            }
            elseif ($merchant){
                if ((!$merchant->getAdmins()->contains($user) || !$merchant->getEnabled()) && ($article->getCreateBy() != $user)) {
                    if (($merchant->getSuAdmin() != $user || !$merchant->getEnabled()) && ($article->getCreateBy() != $user)){
                        throw $this->createAccessDeniedException();
                    }
                }
            }
            else {
                if ((!$user->isCommunityAdmin($community) || !$community->getEnabled() ) && ($article->getCreateBy() != $user)) {
                    if ((!$user->isCommunitySuAdmin($community) || !$community->getEnabled())&& ($article->getCreateBy() != $user)){
                        throw $this->createAccessDeniedException();
                    }
                }
            }
        } else {
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @ApiDoc(resource="/api/article/merchants/wall/{page}/{limit}",
     * description="Ce webservice permet de récuperer les articles des commercants de même intercommunalité.",
     * statusCodes={200="Successful"})
     */
    public function merchantsWallAction(Request $request, $city, $category, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $pagination = $this->get('article.v3')->merchantsWall($request, $city, $category, $page, $limit);

        return $pagination;
    }

    /**
     * Ce web service récupérer la liste des articles des associations de même association filtré
     * @param Request $request
     * @param type $filter
     * @param type $page
     * @return type
     * @throws type
     */
    public function associationsWallAction(Request $request, $city, $category, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $pagination = $this->get('article.v3')->associationsWall($request, $city, $category, $page, $limit);

        return $pagination;
    }

    /**
     * Ce web service permet de récuperer les articles communauté
     * @param Request $request
     * @param type $city
     * @param type $page
     * @return type
     * @throws type
     */
    public function cityhallWallAction(Request $request, $city, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $pagination = $this->get('article.v3')->cityhallWall($request, $city, $user, $page, $limit);

        return $pagination;
    }

    /**
     * Ce web service permet de sauvegarder un abus sur un article
     * @return type
     * @throws type
     */
    public function reportAbuseAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $result = $this->get('article.v3')->reportAbuse($request, $id, $user);

        return $result;
    }

    /**
     * @ApiDoc(resource="/api/article/citzen/category/{category}/wall/{page}",
     * description="Ce webservice permet de récuperer les articles citoyen.",
     * statusCodes={200="Successful"})
     */
    public function citzensWallAction(Request $request, $city, $category, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $apiVersion = $this->getUser()->getApiVersion();
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $pagination = $this->get('article.v3')->citzensWall($request, $city, $category, $user, $page, $limit);

        return $pagination;
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/like",
     * description="Ce webservice permet d'aimer un article.",
     * statusCodes={200="Successful"})
     */
    public function likeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $article = $em->getRepository("AppBundle:Article")->find($id);
        $articlelikes = $article->getLikes();
        $likes = array();
        foreach ($articlelikes as $like) {
            $likes[] = $like->getUser();
        }

        if (!in_array($user, $likes)) {
            $like = new ArticleLikes();
            $like->setUser($user);
            $like->setArticle($article);
            $em->persist($like);
            $article->addLike($like);
            $em->flush();
            if ($article->getType() == 'user' && $article->getCreateBy()) {
                $this->container->get('notification')->notify($article->getCreateBy(), 'article', $user->getFirstname()." ".$user->getLastname()." aime votre article " . $article->getTitle() , false, $article);
                $this->container->get('mobile')->pushNotification($article->getCreateBy(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            } elseif ($article->getType() == 'association' && $article->getAssociation() && $article->getAssociation()->getSuAdmin()) {
                $this->container->get('notification')->notify($article->getAssociation()->getSuAdmin(), 'article', $user->getFirstname()." ".$user->getLastname()." aime votre article " . $article->getTitle(), false, $article);
                $this->container->get('mobile')->pushNotification($article->getAssociation()->getSuAdmin(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            } elseif ($article->getType() == 'merchant' && $article->getMerchant() && $article->getMerchant()->getSuAdmin()) {
                $this->container->get('notification')->notify($article->getMerchant()->getSuAdmin(), 'article', "Votre article " . $article->getTitle() . ' a été aimé.', false, $article);
                $this->container->get('mobile')->pushNotification($article->getMerchant()->getSuAdmin(), 'NOUS-ENSEMBLE ', "", false, false, 'on');
            }
        }

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/unlike",
     * description="Ce webservice permet de ne plus aimer un article.",
     * statusCodes={200="Successful"})
     */
    public function unlikeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $article = $em->getRepository("AppBundle:Article")->find($id);
        $like = $em->getRepository("AppBundle:ArticleLikes")->findOneBy(array('article' => $article, 'user' => $user));
        $em->remove($like);
        $em->flush();

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/likes",
     * description="Ce webservice permet de récupérer les utilisateurs qui ont aimé l'article.",
     * statusCodes={200="Successful"})
     */
    public function likesAction(Request $request, $id)
    {
        $result = array();
        $em = $this->getDoctrine()->getManager();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $user = $this->getUser();

        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        $likes = $em->getRepository("AppBundle:Article")->findLikes($id);

        foreach ($likes as $like) {
            if (isset($like["image"])) {
                $img = $em->getRepository("AppBundle:File")->find($like["image"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $like["imageURL"] = $baseurl . $path;
                    }
                }
            } else {
                $like["imageURL"] = "assets/img/user.jpg";
            }
            $result[] = $like;
        }

        return $result;
    }

    public function articleHeadingAction(Request $request, $id_heading, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }
        // Call V2 function, preferably located inside a service ...
        $heading = $em->getRepository('AppBundle:ArticleHeading')->find($id_heading);

        $pagination = $this->get('article.v3')->articleHeading($request, $heading, $user, $page, $limit);

        return $pagination;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function JTagsAction(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $em->getRepository('AppBundle:Article');

        /** @var Community[] $followedCommunities */
        $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
        /** @var Association[] $joinedAssociations */
        $joinedAssociations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
        $adminAssociations = $em->getRepository('AppBundle:Association')->findUserAssociations($user);
        $joinedMerchants = $em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
        $associations = array_merge($joinedAssociations, $adminAssociations);

        $publicAssociationsArticles = $articleRepository->getArticlesPublicAssociationsByUser($followedCommunities);
        $privateAssociationsArticles = $articleRepository->getArticlesPrivateAssociationsByUser($associations);
        $communityArticles = $articleRepository->getArticlesCommunitiesUser($followedCommunities);
        $associationMebmbersArticles = $articleRepository->getArticlesAssociationsMebmbersArticles($joinedAssociations);
        $merchantMebmbersArticles = $articleRepository->getArticlesMerchantsMebmbersArticles($joinedMerchants);
        $citzensArticles = $articleRepository->getArticlesCitzensByCommunities($followedCommunities);

        $articles = array_merge($citzensArticles, $associationMebmbersArticles, $publicAssociationsArticles, $privateAssociationsArticles, $communityArticles, $merchantMebmbersArticles);

        $jTags = array('J' => 0, 'JB2' => 0, 'JN2' => 0);

        $now = new \DateTime('now');
        $before = clone $now;
        $before->sub(new \DateInterval('P2D'));
        $after = clone $now;
        $after->add(new \DateInterval('P2D'));

        foreach ($articles as $article) {

            if (!$article['startAt'] || !$article['endAt'])
                continue;

            if ($article['startAt'] <= $now && $article['endAt'] > $now)
                $jTags['J']++;
            elseif ($article['startAt'] >= $now && $article['startAt'] <= $after)
                $jTags['JN2']++;
            elseif ($article['endAt'] <= $now && $article['endAt'] >= $before)
                $jTags['JB2']++;

        }

        return $jTags;
    }


    public function downloadDocAction(Request $request, $id) {

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $article = $em->getRepository("AppBundle:Article")->find($id);
        $document = $article->getDocument();

        $path = ".".$helper->asset($document, 'file');

        return $this->file($path);
    }

    public function findEventAction(Request $request, $event)
    {
        $em = $this->getDoctrine()->getManager();
        $article = $em->getRepository("AppBundle:Article")->findOneBy(['event' => $event]);

        return $article->getId();
    }

    /**
     * @ApiDoc(resource="/api/article/{id}/duplicate",
     * description="Ce webservice permet de dupliquer un article.",
     * statusCodes={200="Successful"})
     */
    public function duplicateAction(Request $request, Article $parent)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $parent = $parent->getParent() ? $parent->getParent() :$parent;
        $type = $parent->getType();

        if ($type == "association") {
            $association = $parent->getAssociation();
            if (!$association->getAdmins()->contains($user) || !$association->getEnabled()) {
                if ($association->getSuAdmin() != $user || !$association->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        } elseif ($type == "community") {
            $community = $parent->getCommunity();
            if (!$user->isCommunityAdmin($community) || !$community->getEnabled()) {
                if (!$user->isCommunitySuAdmin($community) || !$community->getEnabled()){
                    throw $this->createAccessDeniedException();
                }
            }
        }

        $data = (array) json_decode($request->getContent());
        $this->get('article.v3')->duplicate($data, $parent, $user);

        return array("success" => true);
    }
}
