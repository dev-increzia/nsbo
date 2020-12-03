<?php
namespace ApiBundle\Service;

use AppBundle\Entity\Article as Article;
use AppBundle\Entity\Abus;
use AppBundle\Entity\Association;
use AppBundle\Entity\Community;
use AppBundle\Entity\CommunityUsers;
use AppBundle\Entity\File;
use AppBundle\Entity\Merchant;
use AppBundle\Entity\Push;
use AppBundle\Entity\PushLog;
use AppBundle\Repository\ArticleRepository;
use AppBundle\Repository\SurveyQuestionChoiceRepository;
use AppBundle\Repository\SurveyQuestionRepository;
use AppBundle\Repository\SurveyRepository;
use AppBundle\Repository\SurveyResponseRepository;
use Doctrine\Common\Util\Debug; 
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use \DateTime;

class ArticleV3
{
    protected $container;
    protected $em;

    public function __construct($container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function view($request, $id, $user)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $article = $this->em->getRepository("AppBundle:Article")->findArticle($id);

        $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);

        $unreadComments = $this->em->getRepository("AppBundle:Comment")->countUnreadArticleComments($article['id']);

        $article['nbreComments'] = $countComments;
        $categories = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getCategories();
        $article['categories'] = $categories;
        $article['unreadComments'] = $unreadComments;
        $article["nbLikes"] = $this->getNbrLikes($article['id']);
        $article["isLiked"] = $this->isLiked($article['id'], $user);
        if ($article['image']) {
            $image = $this->em->getRepository("AppBundle:File")->find($article['image']);

            if ($image) {
                $path = $helper->asset($image, 'file');

                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                if ($path) {
                    $article['imageURL'] = $baseurl . $path;
                }
            }
        }
        $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
        foreach ($images as $value) {
            $image = $this->em->getRepository("AppBundle:File")->find($value);
            if ($image) {
                $path = $helper->asset($image, 'file');
                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                if ($path) {
                    $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                }
            }
        }
        if ($article['userImage']) {
            $image = $this->em->getRepository("AppBundle:File")->find($article['userImage']);

            if ($image) {
                $path = $helper->asset($image, 'file');

                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                if ($path) {
                    $article['userImage'] = $baseurl . $path;
                }
            }
        }
        if ($article['merchantImage']) {
            $image = $this->em->getRepository("AppBundle:File")->find($article['merchantImage']);

            if ($image) {
                $path = $helper->asset($image, 'file');

                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                if ($path) {
                    $article['merchantImage'] = $baseurl . $path;
                }
            }
        }
        if ($article['associationImage']) {
            $image = $this->em->getRepository("AppBundle:File")->find($article['associationImage']);

            if ($image) {
                $path = $helper->asset($image, 'file');

                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                if ($path) {
                    $article['associationImage'] = $baseurl . $path;
                }
            }
        }
        if ($article['cityhallImage']) {
            $image = $this->em->getRepository("AppBundle:File")->find($article['cityhallImage']);

            if ($image) {
                $path = $helper->asset($image, 'file');

                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                if ($path) {
                    $article['cityhallImage'] = $baseurl . $path;
                }
            }
        }
        if(!isset($article['addComments']) || $article['type'] != 'community'){
            $article['addComments'] = true;
        }



        return $article;
    }

    public function update($data, $article, $user)
    {
        $mode = "current";

        if (isset($data['mode'])) {
            $mode = $data['mode'];
        }

        if ($mode === "current") {
            $this->updateArticleData($article, $data, $user);
        }
        if ($mode === 'currentAndNext') {
            $articles = $this->em->getRepository("AppBundle:Article")->getNextArticles($article);

            foreach ($articles as $article) {
                $this->updateArticleData($article, $data, $user);
            }
        }

        $this->em->flush();

        return array("success" => true);
    }

    public function updateArticleData($article, $data, $user)
    {
        foreach ($article->getCategories() as $value) {
            $article->removeCategory($value);
        }
        $this->em->flush();
        if ($article->getType() == "user") {
            $article->setUser($user);
        }

        if (isset($data["city"])) {
            $city = $this->em->getRepository("AppBundle:City")->findOneBy(array('name' => $data["city"]));
            $article->setCity($city);
        }

        if (isset($data["heading"])){
            $articleHeading = $this->em->getRepository("AppBundle:ArticleHeading")->find($data["heading"]);
            $article->setArticleHeading($articleHeading);
        }

        if (isset($data["category"])) {
            foreach ($data["category"] as $category) {
                $category = $this->em->getRepository('AppBundle:Category')->find($category);
                if($category)
                $article->addCategory($category);
            }
        }

        if (isset($data["private"])) {
            $event = $article->getEvent();
            if($data["private"]){
                $article->setPrivate($data["private"]);
                if ($event) {
                    $event->setPrivate($data["private"]);
                }
            }
            else{
                $article->setPrivate(false);
                if ($event) {
                    $event->setPrivate($data["private"]);
                }
            }
        }

        if ($article->getType() == "association") {
            $association = $article->getAssociation();
            if(method_exists($association,'getPrivateContent') && $association->getPrivateContent()) {
                $article->setPrivate(true);
            }
        }

        $article->setTitle($data["title"]);
        $event = $article->getEvent();

        if ($event) {
            $event->setTitle($data['title']);
            $event->setDescription($data['description']);
        }

        if (isset($data["event"])) {
            $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
            $event->setModerateAt(new DateTime('now'));
            $this->em->flush();

            $article->setEvent($event);
        }
        if(array_key_exists("push_enabled", $data)) 
        if ($data["push_enabled"]) {
            if(array_key_exists("push_hour", $data)) {
                $push_hour = new DateTime($data["push_hour"]);
                $push_date = new DateTime($data["push_date"]);
                $date_of_push = $push_date->format('Y-m-d') . " " . $push_hour->format('H:i:s');
                $push = $article->getPush() ? $article->getPush(): new Push();
                $push->setArticle($article);
                $push->setType('article');
                $push->setContent($data["push_content"]);
                $push->setUpdateBy($user);
                if ($article->getType() != "community" && $article->getType() != "user"){
                    $push->setCommunity($article->getAccount()->getCommunity());
                }
                else{
                    $push->setCommunity($article->getAccount());
                }
                $dateAt = new DateTime($date_of_push);
                $push->setSendAt($dateAt);
                if(!$article->getPush()) {
                    $push->setCreateBy($user);
                    $this->em->persist($push);
                }
                $article->setPush($push);
            }
        }

        if ($article->getType() == "user") {
            $message = "Vous avez mis à jour l'article " . $data["title"] . '.';

            $this->container->get('notification')->notify($user, 'article', $message, false, $article);
        }

        $art = $this->em->getRepository("AppBundle:Article")->findArticle($article->getId());
        // image 1
        if (!empty($data['photo'])) {
            $image = new File();
            $image->base64($data['photo']);
            $article->setImage($image);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                if (!$event->getImages()->contains($image)) {
                    $event->setImage($image);
                    $this->em->flush();
                }
            }
        } elseif ($data["todelete"]) {
            $article->setImage(null);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setImage(null);
                $this->em->flush();
            }
        } elseif (!empty($data["event"]) && !$data["todelete"] && empty($data["photo"]) && $art['image']) {
            $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
            $image = $this->em->getRepository("AppBundle:File")->find($art['image']);
            if (!$event->getImages()->contains($image)) {
                $event->setImage($image);
                $this->em->flush();
            }
        }

        /*if (!empty($data['video']))
        {
            $video = new File();
            $video->base64($data['video']);
            
            $article->setVideo($video);
            
            if (!empty($data["event"])) 
            {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setVideo($video);
                $this->em->flush();
            }            
        } elseif (isset($data["todeleteVideo"])) {
            $article->setVideo(null);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setVideo(null);
                $this->em->flush();
            }
        } */

        if (isset($data['deletedoc']) && $data['deletedoc']) {
            $article->setDocument(null);
        }

        if (isset($data['documentUpd']) && (!empty($data['documentUpd']) || $data['documentUpd'] != null)) {
            $document = new File();
            $document->base64($data['documentUpd']);
            $article->setDocument($document);
        }

        // image 2
        if (!empty($data['photo2'])) {
            $image = new File();
            $image->base64($data['photo2']);
            $article->addImage($image);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->addImage($image);
                $this->em->flush();
            }
        }

        if ($data["todelete2"] && $data["photoId2"]) {
            $img = $this->em->getRepository("AppBundle:File")->find($data["photoId2"]);
            $article->removeImage($img);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->removeImage($img);
                $this->em->flush();
            }
        }

        if (!empty($data["event"]) && !$data["todelete2"] && !empty($data["photoId2"])) {
            $images = $this->em->getRepository("AppBundle:Article")->find($art['id'])->getImages();
            $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
            $image = $this->em->getRepository("AppBundle:File")->find($images[0]->getId());
            if (!$event->getImages()->contains($image)) {
                $event->addImage($image);
                $this->em->flush();
            }
        }

        // image 3
        if (!empty($data['photo3'])) {
            $image = new File();
            $image->base64($data['photo3']);
            $article->addImage($image);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->addImage($image);
                $this->em->flush();
            }
        }

        if ($data["todelete3"] && $data["photoId3"]) {
            $img = $this->em->getRepository("AppBundle:File")->find($data["photoId3"]);
            $article->removeImage($img);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->removeImage($img);
                $this->em->flush();
            }
        }

        if (!empty($data["event"]) && !$data["todelete3"] && !empty($data["photoId3"])) {
            $images = $this->em->getRepository("AppBundle:Article")->find($art['id'])->getImages();
            $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
            $image = $this->em->getRepository("AppBundle:File")->find($images[1]->getId());
            if (!$event->getImages()->contains($image)) {
                $event->addImage($image);
                $this->em->flush();
            }
        }

        if (isset($data['imges'])) {
            foreach ($data['imges'] as $value) {
                if (empty($value->url)) {
                    $img = $this->em->getRepository("AppBundle:File")->find($value->id);
                    $article->removeImage($img);
                }
            }
        }

        if (isset($data['photos'])) {
            foreach ($data['photos'] as $value) {
                $image = new File();

                $image->base64($value);

                $article->addImage($image);
            }
        }

        $article->setDescription($data["description"]);
    }

    public function newArticle($request, $type, $id, $user)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $article = new Article();
        $article->setEnabled(true)
            ->setState(true)
            ->setCreateBy($user)
            ->setType($type) 
            ->setPrivate($data["private"])
            ->setTitle($data["title"]);
        if (isset($data['publishing'])) {
            if ($data['publishing'] == 'cityhall') {
                $publishing = $this->em->getRepository('AppBundle:ArticlePublishing')->find(3);
                $article->setPublishing($publishing);
            } elseif ($data['publishing'] == 'canteen') {
                $publishing = $this->em->getRepository('AppBundle:ArticlePublishing')->find(2);
                $article->setPublishing($publishing);
            } elseif ($data['publishing'] == 'project') {
                $publishing = $this->em->getRepository('AppBundle:ArticlePublishing')->find(1);
                $article->setPublishing($publishing);
            }
        }
        if (isset($data["public_At"])) {
            $article->setPublicAt(new DateTime($data["public_At"]));
        } else {
            $article->setPublicAt(new DateTime('now'));
        }


        if ($type == "association") {
            /** @var Association $account */
            $account = $this->em->getRepository("AppBundle:Association")->find($id);
            if(method_exists($account,'getPrivateContent') && $account->getPrivateContent()) {
                $article->setPrivate(true);
            }
            $article->setCommunity($account->getCommunity());
            $article->setAssociation($account);
            if (isset($data["city"])) {
                $city = $this->em->getRepository("AppBundle:City")->findOneBy(array('name' => $data["city"]));
                $article->setCity($city);
            }
            if (isset($data["categories"])) {
                foreach ($data["categories"] as $category) {
                    $category = $this->em->getRepository('AppBundle:Category')->find($category);
                    $article->addCategory($category);
                }
            }
            if ($data["event"]) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);

                $article->setEvent($event);
            }
        } else if ($type == "community") {
            /** @var Association $account */
            $account = $this->em->getRepository("AppBundle:Community")->find($id);
            $articleHeading = null;
            if ($data["heading"]){
                $articleHeading = $this->em->getRepository("AppBundle:ArticleHeading")->find($data["heading"]);
            }

            $article->setCommunity($account)
                ->setArticleHeading($articleHeading);

            if (isset($data["city"])) {
                $city = $this->em->getRepository("AppBundle:City")->findOneBy(array('name' => $data["city"]));
                $article->setCity($city);
            }
            foreach ($data["categories"] as $category) {
                $category = $this->em->getRepository('AppBundle:Category')->find($category);
                $article->addCategory($category);
            }
            if ($data["event"]) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);

                $article->setEvent($event);
            }
        } else {
            $account = $data["community"]?$this->em->getRepository("AppBundle:Community")->find($data["community"]):null;
            $article->setUser($user);
            if (isset($data["city"])) {
                $city = $this->em->getRepository("AppBundle:City")->findOneBy(array('name' => $data["city"]));
                $article->setCity($city);
            }
            if (isset($data["categories"])) {
                foreach ($data["categories"] as $category) {
                    $category = $this->em->getRepository('AppBundle:Category')->find($category);
                    $article->addCategory($category);
                }
            }
            if (isset($data["group"])) {
                $groupName = explode("-", $data["group"]);
                if ($groupName[0] == "association"){
                    $group = $this->em->getRepository("AppBundle:Association")->find($groupName[1]);
                    $article->setAssociation($group)
                        ->setCommunity($group->getCommunity());
                }
                elseif ($groupName[0] == "merchant"){
                    $group = $this->em->getRepository("AppBundle:Merchant")->find($groupName[1]);
                    $article->setMerchant($group)
                        ->setCommunity($group->getCommunity());
                }

            }
            if ($data["community"]){
                $community = $this->em->getRepository("AppBundle:Community")->find($data["community"]);
                $article->setCommunity($community);
            }
            /*$article->setCommunity($user->getCommunity());*/
        }
        if ($data['photo']) {
            $image = new File();

            $image->base64($data['photo']);

            $article->setImage($image);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setImage($image);
                $this->em->flush();
            }
        }
        
        if (!empty($data['video'])) 
        {
            $video = new File();
            $video->base64($data['video']);
            
            $article->setVideo($video);
            
            if (!empty($data["event"])) 
            {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setVideo($video);
                $this->em->flush();
            }
        }

        if (isset($data['document']) && !empty($data['document']))
        {
            $document = new File();
            $document->base64($data['document']);

            $article->setDocument($document);

            /*if (!empty($data["event"]))
            {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->setDocument($document);
                $this->em->flush();
            }*/
        }
        
        if ($data['photo2']) {
            $image = new File();

            $image->base64($data['photo2']);

            $article->addImage($image);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->addImage($image);
                $this->em->flush();
            }
        }

        if ($data['photo3']) {
            $image = new File();

            $image->base64($data['photo3']);

            $article->addImage($image);
            if (!empty($data["event"])) {
                $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                $event->addImage($image);
                $this->em->flush();
            }
        }

        if (isset($data['photos'])) {
            foreach ($data['photos'] as $value) {
                $image = new File();
                $image->base64($value);
                $article->addImage($image);

                if (!empty($data["event"])) {
                    $event = $this->em->getRepository("AppBundle:Event")->find($data["event"]);
                    $event->addImage($image);
                }
                $this->em->flush();
            }
        }

        $article->setDescription($data["description"]);

        $this->em->persist($article);
        if (isset($data["event"])) {
            $push_hour = new DateTime($data["push_hour"]);
            $push_date = new DateTime($data["push_date"]);
            $date_of_push = $push_date->format('Y-m-d') . " " . $push_hour->format('H:i:s');   
            if(array_key_exists("push_enabled", $data)) 
            if ($data["push_enabled"]) {
                $event->setPushEnabled($data["push_enabled"]);
                $push = new Push();
                $push->setEvent($event);
                $push->setContent($data["push_content"]);
                $push->setCreateBy($user);
                $push->setUpdateBy($user);
                if ($type != "community" && $type != "user"){
                    $push->setCommunity($account->getCommunity());
                }
                else{
                    $push->setCommunity($account);
                }
                $dateAt = new DateTime($date_of_push);
                $push->setSendAt($dateAt);
                $event->setPush($push);
                $this->em->persist($push);
            } else {
                $event->setPush(null);
            }
            $event->setArticle($article);
        } else {
            if(array_key_exists("push_enabled", $data)) 
            if ($data["push_enabled"]) {
                $article->setPushEnabled($data["push_enabled"]);
                $push = new Push();
                $push->setArticle($article);
                $push->setType('article');
                $push->setContent($data["push_content"]);
                $push->setCreateBy($user);
                $push->setUpdateBy($user);
                if ($type != "community" && $type != "user"){
                    $push->setCommunity($account->getCommunity());
                }
                else{
                    $push->setCommunity($account);
                }
                $push_hour = new DateTime($data["push_hour"]);
                $push_date = new DateTime($data["push_date"]);
                $date_of_push = $push_date->format('Y-m-d') . " " . $push_hour->format('H:i:s');        
                $dateAt = new DateTime($date_of_push);
                $push->setSendAt($dateAt);
                $article->setPush($push);
                $this->em->persist($push);
            } else {
                $article->setPush(null);
            }
        }
        $this->notifyUsers($this->em, $article);

        if ($type == "user") {
            $message = "Vous avez créé l'article " . $article->getTitle() . '.';

            $this->container->get('notification')->notify($user, 'article', $message, false, $article);
        }

        $this->em->flush();

        if ($type == "association" || $type == "merchant" || $type == "community") {
            if ($data["event"]) {
                // send email to anaelle@nousensemble.fr
                $content = $this->container->get('templating')->render('AppBundle:Mail:newArticleEvent.html.twig', array(
                    'entity' => $event,
                    'article' => $article,
                    'account' => $account
                ));
                $this->container->get('mail')->newArticleEvent($content);
            }
        }

        if ($article->getImage()) {
            $path = $helper->asset($article->getImage(), 'file');

            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

            if ($path) {
                $article->setImageURL($baseurl . $path);
            }
        }

        if ($article->getVideo()) {
            $path = $helper->asset($article->getVideo(), 'file');

            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

            if ($path) {
                $article->setVideoURL($baseurl . $path);
            }
        }

        if ($article->getDocument()) {
            $path = $helper->asset($article->getDocument(), 'file');

            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

            if ($path) {
                $article->setDocumentURL($baseurl . $path);
            }
        }

        if ($user = $article->getUser()) {
            if ($user->getImage()) {
                $path = $helper->asset($user->getImage(), 'file');

                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                $user->setImageURL($baseurl . $path);
            }
        }

        return $article;
    }

    public function deactivate($id)
    {
        $article = $this->em->getRepository("AppBundle:Article")->find($id);
        $article->setEnabled(false);
        $this->em->flush();

        return array("success" => true);
    }

    public function activate($id)
    {
        $article = $this->em->getRepository("AppBundle:Article")->find($id);
        $article->setEnabled(true);
        $this->em->flush();

        return array("success" => true);
    }

    public function merchants($request, $id, $user, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $articles = $this->em->getRepository("AppBundle:GoodPlan")->getArticlesByMerchant($id, $page, $limit);

        $items = array();
        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countGoodPlanComments($article['id']);
            $unreadComments = $this->em->getRepository("AppBundle:Comment")->countUnreadGoodPlanComments($article['id']);
            $article['nbreComments'] = $countComments;
            $article['unreadComments'] = $unreadComments;

            $categories = $this->em->getRepository("AppBundle:GoodPlan")->find($article['id'])->getCategories();
            $article["categories"] = $categories;
            $article["nbLikes"] = $this->getNbrLikes($article['id']);
            $article["isLiked"] = $this->isLiked($article['id'], $user);
            if(array_key_exists("isParent", $article)) {
                if($article['isParent'] == 0) {
                    $article['isParent'] = false;
                } else {
                    $article['isParent'] = true;
                }
            } else {
                $article['isParent'] = true;
            }
            if(array_key_exists("hasParent", $article)) {
                if($article['hasParent']) {
                    $article['hasParent'] = true;
                } else {
                    $article['hasParent'] = false;
                }
            } else {
                $article['hasParent'] = false;
            }

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:GoodPlan")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }

            $items[] = $article;
        }

        return $items;
    }

    /**
     * @param $request
     * @param $em
     * @param $id
     * @param $user
     * @param $page
     * @param $limit
     * @return array
     */
    public function associations($request, $id, $user, $page, $limit,$commu = null)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->em->getRepository('AppBundle:Article');

        $userType = $request->get('userType');

        if ($userType){
            if ($commu){
                $articles = $articleRepository->getArticlesByCommuUser($id, $page, $limit, $user);
            }
            else {
                $articles = $articleRepository->getArticlesByAssociationUser($id, $page, $limit, $user);
            }
        }
        else {
            if ($commu){
                $articles = $articleRepository->getArticlesByCommu($id, $page, $limit);
            }
            else {
                $articles = $articleRepository->getArticlesByAssociation($id, $page, $limit);
            }
        }

        $items = array();
        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $unreadComments = $this->em->getRepository("AppBundle:Comment")->countUnreadArticleComments($article['id']);
            $currentArticle = $articleRepository->find($article['id']);

            if ($currentArticle && $currentArticle->getPush()){
                $article["push"]['enabled'] = true;
                $article["push"]['date'] = $currentArticle->getPush()->getSendAt();

                $article["push"]['content'] = $currentArticle->getPush()->getContent();
            }else{
                $article["push"]['enabled'] = false;
                $article["push"]['date'] = "";

                $article["push"]['content'] = "";
            }
            $categories = $currentArticle->getCategories();

            $article["categories"] = $categories;
            $article['nbreComments'] = $countComments;
            $article['unreadComments'] = $unreadComments;
            $article["nbLikes"] = $this->getNbrLikes($article['id']);
            $article["isLiked"] = $this->isLiked($article['id'], $user);

            if($article['isParent'] == 0) {
                $article['isParent'] = false;
            } else {
                $article['isParent'] = true;
            }
            if($article['hasParent']) {
                $article['hasParent'] = true;
            } else {
                $article['hasParent'] = false;
            }

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if(!isset($article['addComments']) || $article['type'] != 'community'){
                $article['addComments'] = true;
            }
            if($currentArticle->getVideo()){
                $image = $this->em->getRepository("AppBundle:File")->find($currentArticle->getVideo()->getId());
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['videoFile'] = $baseurl . $path;
                    }
                }
            }else{
                $article['videoFile'] = null;
            }


            $items[] = $article;
        }

        return  array($items);
    }

    /**
     * @param $request
     * @param $em
     * @param $id
     * @param $user
     * @param $page
     * @param $limit
     * @return array
     */
    public function surveyCacheCommunity($request, $id, $user)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        /** @var SurveyRepository $surveyRepository */
        $surveyRepository = $this->em->getRepository('AppBundle:Survey');
        /** @var SurveyQuestionRepository $surveyQuestionRepository */
        $surveyQuestionRepository = $this->em->getRepository('AppBundle:SurveyQuestion');
        /** @var SurveyQuestionChoiceRepository $surveyQuestionChoiceRepository */
        $surveyQuestionChoiceRepository = $this->em->getRepository('AppBundle:SurveyQuestionChoice');
        /** @var SurveyResponseRepository $surveyResponseRepository */
        $surveyResponseRepository = $this->em->getRepository('AppBundle:SurveyResponse');

        $community[] = $this->em->getRepository('AppBundle:Community')->find($id);

        $surveys = $surveyRepository->getSurveysByCommunities($community, null, null, null);

        $userSurveys = array();
        foreach ($surveys as $survey) {
            $survey['type'] = 'survey';
            $survey['questions'] = $surveyQuestionRepository->getQuestionsSurveys($survey['id']);
            $survey['nbrResponse'] = 0;
            if (isset($survey['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($survey['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $survey['imageURL'] = $baseurl . $path;
                    }
                }
            }
            foreach ( $survey['questions'] as $key => $question){
                $survey['questions'][$key]['choices'] = $surveyQuestionChoiceRepository->getChoiceSurvey($question['id']);
                $survey['questions'][$key]['alreadyAnswer'] = null;
                foreach ( $survey['questions'][$key]['choices'] as $key2 => $response){
                    $survey['questions'][$key]['choices'][$key2]['responseNumbr'] = count($surveyResponseRepository->findBy(array('response'=> $response['id'])));
                    $survey['nbrResponse'] += $survey['questions'][$key]['choices'][$key2]['responseNumbr'];
                    $existResponseUser = $surveyResponseRepository->getResponseSurvey($user,$response['id']);
                    if ($existResponseUser){
                        $survey['questions'][$key]['alreadyAnswer'] = $existResponseUser[0]['id'];
                    }
                }
            }
            $userSurveys[] = $survey;
        }

        return  array($userSurveys);
    }

    /**
     * @param Request $request
     * @param $em
     * @param $user
     * @param $page
     * @param $limit
     * @return array
     */
    public function citzenHome($request, $user, $page, $limit)
    {

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        /** @var Community[] $followedCommunities */
        $followedCommunities = $this->em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
         /** @var Association[] $joinedAssociations */
        $joinedAssociations = $this->em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
        $adminAssociations = $this->em->getRepository('AppBundle:Association')->findUserAssociations($user);
        $joinedMerchants = $this->em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
        $associations = array_merge($joinedAssociations, $adminAssociations);

        /** @var ArticleRepository $articleRepository */
        $articleRepository = $this->em->getRepository('AppBundle:Article');

        /** @var SurveyRepository $surveyRepository */
        $surveyRepository = $this->em->getRepository('AppBundle:Survey');

        /** @var SurveyQuestionRepository $surveyQuestionRepository */
        $surveyQuestionRepository = $this->em->getRepository('AppBundle:SurveyQuestion');

        /** @var SurveyQuestionChoiceRepository $surveyQuestionChoiceRepository */
        $surveyQuestionChoiceRepository = $this->em->getRepository('AppBundle:SurveyQuestionChoice');

        /** @var SurveyResponseRepository $surveyResponseRepository */
        $surveyResponseRepository = $this->em->getRepository('AppBundle:SurveyResponse');

        // Setup filters
        $cities = $request->get('city');
        $categories = $request->get('category');
        $catIds = [];
        if($categories){
            foreach ($categories as $category) {
                $cat = $this->em->getRepository('AppBundle:Category')->find($category);
                $catName = $cat->getName();
                $cats = $this->em->getRepository('AppBundle:Category')->findByCatAssoNameAnCommunities($catName,$followedCommunities);
                foreach ($cats as $c) {
                    $catIds[] = $c->getId();

                }
            }
        }

        $j = $request->get('j');

        $publicAssociationsArticles = $articleRepository->getArticlesPublicAssociationsByUser($followedCommunities, $cities, $catIds, $j);
        $privateAssociationsArticles = $articleRepository->getArticlesPrivateAssociationsByUser($associations, $cities, $catIds, $j);


        $communityArticles = $articleRepository->getArticlesCommunitiesUser($followedCommunities, $cities, $catIds, $j);
        $associationMebmbersArticles = $articleRepository->getArticlesAssociationsMebmbersArticles($associations, $cities, $catIds, $j);
        $merchantMebmbersArticles = $articleRepository->getArticlesMerchantsMebmbersArticles($joinedMerchants, $cities, $catIds, $j);
        $citzensArticles = $articleRepository->getArticlesCitzensByCommunities($followedCommunities, $cities, $catIds, $j);
        $surveys = $surveyRepository->getSurveysByCommunities($followedCommunities, $cities, $catIds, $j);

        $userSurveys = array();
        foreach ($surveys as $survey) {
            $survey['type'] = 'survey';
            $survey['questions'] = $surveyQuestionRepository->getQuestionsSurveys($survey['id']);
            $survey['nbrResponse'] = 0;
            foreach ( $survey['questions'] as $key => $question){
                $survey['questions'][$key]['choices'] = $surveyQuestionChoiceRepository->getChoiceSurvey($question['id']);
                $survey['questions'][$key]['alreadyAnswer'] = null;
                foreach ( $survey['questions'][$key]['choices'] as $key2 => $response){
                    $survey['questions'][$key]['choices'][$key2]['responseNumbr'] = count($surveyResponseRepository->findBy(array('response'=> $response['id'])));
                    $survey['nbrResponse'] += $survey['questions'][$key]['choices'][$key2]['responseNumbr'];
                    $existResponseUser = $surveyResponseRepository->getResponseSurvey($user,$response['id']);
                    if ($existResponseUser){
                        $survey['questions'][$key]['alreadyAnswer'] = $existResponseUser[0]['id'];
                    }
                }
            }
            $userSurveys[] = $survey;
        }

        $filtredCommunityArticles = array();
        foreach($communityArticles as $communityArticle){
            /** @var Community $community */
            $community= $this->em->getRepository('AppBundle:Community')->find($communityArticle['communityId']);
            if( $community->getIsCommentArticleHeadingActive()){
                $communityArticle['addComments'] = true;
            }else{
                $communityArticle['addComments'] = false;
            }
            $filtredCommunityArticles[]= $communityArticle;
        }

        $articles = array_merge($citzensArticles, $associationMebmbersArticles, $publicAssociationsArticles, $privateAssociationsArticles,$filtredCommunityArticles,$userSurveys,$merchantMebmbersArticles);
        if ($j && ($j == 'J')) {
            usort($articles, function ($a, $b) {
                if ($a['endAt'] == $b['endAt']) {
                    return 0;
                } else {
                    return ($a['endAt'] > $b['endAt']) ? 1 : -1;
                }
            });
        } elseif ($j && ($j == 'JB2')) {
            usort($articles, function ($a, $b) {
                if ($a['endAt'] == $b['endAt']) {
                    return 0;
                } else {
                    return ($a['endAt'] < $b['endAt']) ? 1 : -1;
                }
            });
        } elseif ($j && ($j == 'JN2')) {
            usort($articles, function ($a, $b) {
                if ($a['startAt'] == $b['startAt']) {
                    return 0;
                } else {
                    return ($a['startAt'] > $b['startAt']) ? 1 : -1;
                }
            });
        }
        else {
            usort($articles, function ($a, $b) {
                if ($a['publicAt'] == $b['publicAt']) {
                    return 0;
                } else {
                    return ($a['publicAt'] < $b['publicAt']) ? 1 : -1;
                }
            });
        }

        $items = array();
        $now = new DateTime('now');
        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $article['nbreComments'] = $countComments;
            /** @var Article $currentArticle */
            $currentArticle = $this->em->getRepository("AppBundle:Article")->find($article['id']);

            if ($currentArticle && $currentArticle->getPush()){
                $article["push"]['enabled'] = true;
                $article["push"]['date'] = $currentArticle->getPush()->getSendAt();

                $article["push"]['content'] = $currentArticle->getPush()->getContent();
            }else{
                $article["push"]['enabled'] = false;
                $article["push"]['date'] = "";

                $article["push"]['content'] = "";
            }
            $article['isParent'] = false;

            if($currentArticle && $currentArticle->getDuplicatedArticles()->count() !== 0) {
                $article['isParent'] = true;
            }

            $article['hasParent'] = false;

            if ($currentArticle && $currentArticle->getParent()) {
                $article['hasParent'] = true;
            }

            if($currentArticle){
                $categories = $currentArticle->getCategories();
                $article['categories'] = $categories;

            }
            $article["nbLikes"] = $this->getNbrLikes($article['id']);
            $article["isLiked"] = $this->isLiked($article['id'], $user);

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }

            if($currentArticle) {
                if($currentArticle->getVideo()){
                    $image = $this->em->getRepository("AppBundle:File")->find($currentArticle->getVideo()->getId());
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $article['videoFile'] = $baseurl . $path;
                        }
                    }
                }else{
                    $article['videoFile'] = null;
                }

            }else{
                $article['videoFile'] = null;
            }

            if($currentArticle) {
                if($currentArticle->getDocument()){
                    $image = $this->em->getRepository("AppBundle:File")->find($currentArticle->getDocument()->getId());
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $article['path'] = $path;
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $article['document'] = $baseurl . $path;
                        }
                    }
                }else{
                    $article['document'] = null;
                }

            }else{
                $article['document'] = null;
            }

            // other photos
            if($currentArticle){
                $images = $currentArticle->getImages();
                foreach ($images as $value) {
                    $image = $this->em->getRepository("AppBundle:File")->find($value);
                    if ($image) {
                        $path = $helper->asset($image, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                        }
                    }
                }


            }
            if (isset($article['merchantImage'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['merchantImage']);

                if ($image) {
                    $path = $helper->asset($image, 'file');

                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                    if ($path) {
                        $article['merchantImage'] = $baseurl . $path;
                    }
                }
            }
            if (isset($article['associationImage'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['associationImage']);

                if ($image) {
                    $path = $helper->asset($image, 'file');

                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();

                    if ($path) {
                        $article['associationImage'] = $baseurl . $path;
                    }
                }
            }

            if (isset($article['startAt']) && isset($article['endAt']) && $article['startAt'] && $article['endAt']) {

                $diff = null;

                if ($article['startAt'] > $now) {
                    $diff = $article['startAt']->diff($now);
                } elseif ($article['endAt'] < $now) {
                    $diff = $now->diff($article['endAt']);
                }

                // TODO clean
                $format = '';
                if ($diff) {
                    if ($days = $diff->format('%a')) {
                        $format = $days . ' jour' . ($days > 1 ? 's' : '');
                    } elseif ($hours = $diff->format('%H')) {
                        $format = $hours . ' heure' . ($hours > 1 ? 's' : '');
                    } elseif ($minutes = $diff->format('%I')) {
                        $format = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                    } else {
                        $seconds = $diff->format('%S');
                        $format = $seconds . ' seconde' . ($seconds > 1 ? 's' : '');
                    }
                }

                $article['nextDayDiff'] = $format;

            }
            if(!isset($article['addComments'])) {
                $article['addComments'] = true;
            }

            $items[] = $article;
        }

        $offset = ($page - 1) * $limit;
        $pagination = array_slice($items, $offset, $limit);
        return array($pagination);
    }

    public function merchantsWall($request, $city, $category, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $articles = $this->em->getRepository("AppBundle:Article")->getAllArticlesMerchantsWallByCities($city, $category);
        $items = array();
        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $article['nbreComments'] = $countComments;
            $categories = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getCategories();
            $article['categories'] = $categories;

            if($article['isParent'] == 0) {
                $article['isParent'] = false;
            } else {
                $article['isParent'] = true;
            }
            if($article['hasParent']) {
                $article['hasParent'] = true;
            } else {
                $article['hasParent'] = false;
            }

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            // other photos
            $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (isset($article['merchantImage'])) {
                $merchantImage = $this->em->getRepository("AppBundle:File")->find($article['merchantImage']);
                if ($merchantImage) {
                    $path = $helper->asset($merchantImage, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['merchantImage'] = $baseurl . $path;
                    }
                }
            }
            $items[] = $article;
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($items, $offset, $limit);
        return $pagination;
    }

    public function associationsWall($request, $city, $category, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $articles = $this->em->getRepository("AppBundle:Article")->getAllArticlesAssociationWallByCities($city, $category);
        $items = array();
        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $article['nbreComments'] = $countComments;
            $categories = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getCategories();
            $article['categories'] = $categories;

            if($article['isParent'] == 0) {
                $article['isParent'] = false;
            } else {
                $article['isParent'] = true;
            }
            if($article['hasParent']) {
                $article['hasParent'] = true;
            } else {
                $article['hasParent'] = false;
            }

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            // other photos
            $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }
            if (isset($article['associationImage'])) {
                $associationImage = $this->em->getRepository("AppBundle:File")->find($article['associationImage']);
                if ($associationImage) {
                    $path = $helper->asset($associationImage, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['associationImage'] = $baseurl . $path;
                    }
                }
            }
            $items[] = $article;
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($items, $offset, $limit);
        return $pagination;
    }

    public function cityhallWall($request, $city, $user, $page, $limit)
    {
        $items = array();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $articles = $this->em->getRepository("AppBundle:Article")->getArticlesCityhalWallByCities($user, $city);

        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $categories = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getCategories();
            $article['categories'] = $categories;
            $article['nbreComments'] = $countComments;
            $article["nbLikes"] = $this->getNbrLikes($article['id']);
            $article["isLiked"] = $this->isLiked($article['id'], $user);

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            // other photos
            $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }



            if (isset($article['cityhallImage'])) {
                $cityhallImage = $this->em->getRepository("AppBundle:File")->find($article['cityhallImage']);
                if ($cityhallImage) {
                    $path = $helper->asset($cityhallImage, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['cityhallImage'] = $baseurl . $path;
                    }
                }
            }
            $items[] = $article;
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($items, $offset, $limit);
        return $pagination;
    }

    public function reportAbuse($request, $id, $user)
    {
        $datas = $request->getContent();
        $data = (array) json_decode($datas);
        $abuse = new Abus();
        $article = $this->em->getRepository("AppBundle:Article")->find($id);
        $abuse->setArticle($article)
            ->setMessage($data["message"])
            ->setUser($user)
            ->setModerate(false);
        $this->em->persist($abuse);
        $this->em->flush();
        return array("success" => true);
    }

    public function citzensWall($request, $city, $category, $user, $page, $limit)
    {
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $articles = $this->em->getRepository("AppBundle:Article")->getArticleCitzenByCities($category, $city);
        usort($articles, function ($a, $b) {
            if ($a['createAt'] == $b['createAt']) {
                return 0;
            } else {
                return ($a['createAt'] < $b['createAt']) ? 1 : -1;
            }
        });
        $items = array();
        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $article['nbreComments'] = $countComments;
            $article["nbLikes"] = $this->getNbrLikes($article['id']);
            $article["isLiked"] = $this->isLiked($article['id'], $user);

            if($article['isParent'] == 0) {
                $article['isParent'] = false;
            } else {
                $article['isParent'] = true;
            }
            if($article['hasParent']) {
                $article['hasParent'] = true;
            } else {
                $article['hasParent'] = false;
            }

            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }

            if (isset($article['userImage'])) {
                $userImage = $this->em->getRepository("AppBundle:File")->find($article['userImage']);
                if ($userImage) {
                    $path = $helper->asset($userImage, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['userImage'] = $baseurl . $path;
                    }
                }
            }
            $items[] = $article;
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($items, $offset, $limit);
        return $pagination;
    }

    public function getNbrLikes($id)
    {
        $article = $this->em->getRepository("AppBundle:Article")->find($id);
        if($article){
            return count($article->getLikes());
        }
        return null;
    }

    public function isLiked($id, $user)
    {
        $article = $this->em->getRepository("AppBundle:Article")->find($id);
        $isLiked = false;
        $liked = $this->em->getRepository("AppBundle:ArticleLikes")->findOneBy(array("user" => $user, "article" => $article));
        if ($liked) {
            $isLiked = true;
        }
        return $isLiked;
    }

    /**
     * @param $request
     * @param $em
     * @param $id_heading
     * @param $user
     * @param $page
     * @param $limit
     * @return array
     */
    public function articleHeading($request, $id_heading, $user, $page, $limit)
    {
        $items = array();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $articles = $this->em->getRepository("AppBundle:Article")->getArticlesByIdHeading($user, $id_heading);

        foreach ($articles as $article) {
            $countComments = $this->em->getRepository("AppBundle:Comment")->countArticleComments($article['id']);
            $currentArticle = $this->em->getRepository("AppBundle:Article")->find($article['id']);

            if ($currentArticle && $currentArticle->getPush()){
                $article["push"]['enabled'] = true;
                $article["push"]['date'] = $currentArticle->getPush()->getSendAt();

                $article["push"]['content'] = $currentArticle->getPush()->getContent();
            }else{
                $article["push"]['enabled'] = false;
                $article["push"]['date'] = "";

                $article["push"]['content'] = "";
            }

            $categories = $currentArticle->getCategories();
            $article['categories'] = $categories;
            $article['nbreComments'] = $countComments;
            $article["nbLikes"] = $this->getNbrLikes($article['id']);
            $article["isLiked"] = $this->isLiked($article['id'], $user);
            if($article['isParent'] == 0) {
                $article['isParent'] = false;
            } else {
                $article['isParent'] = true;
            }
            if($article['hasParent']) {
                $article['hasParent'] = true;
            } else {
                $article['hasParent'] = false;
            }




            if (isset($article['image'])) {
                $image = $this->em->getRepository("AppBundle:File")->find($article['image']);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['imageURL'] = $baseurl . $path;
                    }
                }
            }
            // other photos
            $images = $this->em->getRepository("AppBundle:Article")->find($article['id'])->getImages();
            foreach ($images as $value) {
                $image = $this->em->getRepository("AppBundle:File")->find($value);
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['images'][] = array('id' => $image->getId(), 'url' => $baseurl . $path);
                    }
                }
            }

           if($currentArticle->getVideo()){
                $image = $this->em->getRepository("AppBundle:File")->find($currentArticle->getVideo()->getId());
                if ($image) {
                    $path = $helper->asset($image, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['videoFile'] = $baseurl . $path;
                    }
                }
            }else{
                $article['videoFile'] = null;
            }


            if (isset($article['cityhallImage'])) {
                $cityhallImage = $this->em->getRepository("AppBundle:File")->find($article['cityhallImage']);
                if ($cityhallImage) {
                    $path = $helper->asset($cityhallImage, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $article['cityhallImage'] = $baseurl . $path;
                    }
                }
            }
            $items[] = $article;
        }
        $offset = ($page - 1) * $limit;
        $pagination = array_slice($items, $offset, $limit);
        return $pagination;
    }

    public function duplicate($data, $parent, $user)
    {
        $type = $parent->getType();
        $dateArray = $this->getRecursiveDates($data);

        foreach ($dateArray as $date) {
            $startDate = new DateTime($date);
            $article = new Article();
            if(isset($data['images'])) {
                $imagesCounter = 0;
                foreach ($data['images'] as $image) {
                    if (is_object($image)) {
                        $imagesCounter++;
                        $image = (array)$image;
                        $imageId = $image['id'];
                        $img = $this->em->getRepository('AppBundle:File')->findOneById($imageId);
                        $path = __DIR__.'/../../../public/upload/'.$img->getFilename();
                        $path = str_replace(" ", "\ ", $path);
                        $pictureType = mime_content_type($path);
                        $imgData = file_get_contents($path);
                        $base64 = 'data:'.$pictureType. ';base64,' . base64_encode($imgData);
                        $currentImage = new File();
                        $currentImage->base64($base64);
                        if(count($data['images']) == $imagesCounter && !isset($data['photo'])) {
                            $article->setImage($currentImage);
                        }
                        else
                        {
                            $article->addImage($currentImage);
                        }
                    }
                }
            }
            $article->setEnabled($parent->getEnabled())
                ->setState($parent->getState())
                ->setCreateBy($user)
                ->setType($type) 
                ->setParent($parent)
                ->setPublishing($parent->getPublishing())
                ->setPrivate($parent->getPrivate())
                ->setCommunity($parent->getCommunity())
                ->setDescription($data["description"])
                ->setArticleHeading($parent->getArticleHeading())
                ->setAssociation($parent->getAssociation())
                ->setUser($parent->getUser())
                ->setMerchant($parent->getMerchant())
                ->setPublicAt(new DateTime($date))
                ->setPushEnabled($parent->getPushEnabled())
                ->setTitle($data["title"]);

            foreach ($parent->getCategories() as $category) {
                $article->addCategory($category);
            }

            if (isset($data["city"])) {
                $city = $this->em->getRepository("AppBundle:City")->findOneBy(array('name' => $data["city"]));
                $article->setCity($city);
            }

            if ($data['photo']) {
                $image = new File();
                $image->base64($data['photo']);
                $article->setImage($image);
            }

            if (!empty($data['video'])) {
                $video = new File();
                $video->base64($data['video']);
                $article->setVideo($video);
            }

            if (isset($data['document']) && !empty($data['document'])) {
                $document = new File();
                $document->base64($data['document']);
                $article->setDocument($document);
            }

            if ($data['photo2']) {
                $image = new File();
                $image->base64($data['photo2']);
                $article->addImage($image);
            }

            if ($data['photo3']) {
                $image = new File();
                $image->base64($data['photo3']);
                $article->addImage($image);
            }

            if (isset($data['photos'])) {
              foreach ($data['photos'] as $value) {
                $image = new File();
                $image->base64($value);
                $article->addImage($image);
              }
            }
            if ($parent->getPush()) {
                $pushDate = $parent->getPush()->getSendAt();
                $pushInterval = $pushDate->diff(new DateTime($date));
                $push = new Push();
                $push->setArticle($article);
                $push->setContent($parent->getPush()->getContent());
                $push->setCreateBy($user);
                $push->setUpdateBy($user);
                $push->setCommunity($parent->getPush()->getCommunity());
                $push->setSendAt($startDate->sub($pushInterval));
                $article->setPush($push);
                $this->em->persist($push);
            }
            $this->em->persist($article);
            $this->notifyUsers($this->em, $article);
        }

        $this->em->flush();
    }

    private function notifyUsers($em, $article)
    {
        $users = $em->getRepository('UserBundle:User')->search(false, array(), null, array('ROLE_CITIZEN'), null, null, null, null, null, null);
        if ($article->getType() == 'merchant') {
            if ($article->getAssociation() && $article->getAssociation()->getEnabled() && $article->getAssociation()->getModerate() == 'accepted') {
                $category = $article->getAssociation()->getCategory() ? $article->getAssociation()->getCategory() : false;
                $merchantUsers = $article->getMerchant()->getUsers();
                foreach ($merchantUsers as $merchantUser) {
                    if($merchantUser->getType() == 'approved') {
                        $user = $merchantUser->getUser();
                        $joinedMerchants = $em->getRepository('AppBundle:Merchant')->getJoinedMerchant($user);
                        if (in_array($article->getMerchant(), $joinedMerchants)) {
                            $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'articleCounter', false,$article);
                            $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter', false, $article);
                        }
                    }
                }
            }
        } else if ($article->getType() == 'article' || $article->getType() == 'association') {
            if ($article && $article->getEnabled()) {
                if ($article->getAssociation() && $article->getAssociation()->getEnabled() && $article->getAssociation()->getModerate() == 'accepted') {
                    foreach ($users as $user) {
                        if ($article->getPrivate()) {
                            $joinedAssociations = $em->getRepository('AppBundle:Association')->getJoinedAssociations($user);
                            if (in_array($article->getAssociation(), $joinedAssociations)) {
                                $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'articleCounter', false,$article);
                                $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter', false, $article);
                            }
                        } else {
                            $followedCommunities = $em->getRepository('AppBundle:Community')->getFollowedCommunities($user);
                            if (in_array($article->getCommunity(), $followedCommunities)) {
                                $this->container->get('mobile')->pushNotification($user, 'NOUS-Ensemble-Counter', 'articleCounter', false,$article);
                                $this->container->get('notification')->notify($user, 'NOUS-Ensemble-Counter', 'articleCounter', false, $article);
                            }
                        }
                    }
                }
            }
        }

    }

    public function getRecursiveDates($data)
    {
        $recursivityEnd = $data['recursivity_end'];
        $recursivityPeriod = $data['recurivity_period'];
        $recursivityDay = $data['recursivity_day'];
        $dateArray = [];

        if ($recursivityPeriod === 'weekly') {
            $now = strtotime('now');
            $endDate = strtotime($recursivityEnd);
            for($i = strtotime($recursivityDay, $now); $i <= $endDate; $i = strtotime('+1 week', $i)) {
                $dateArray[] = date('Y-m-d',$i);
            }
        }

        if ($recursivityPeriod === 'daily') {
            $now = new DateTime('now');
            $endDate = new DateTime($recursivityEnd);
            $period = new \DatePeriod($now, new \DateInterval('P1D'), $endDate->modify('+1 day'));

            foreach($period as $date) {
                $dateArray[] = $date->format('Y-m-d');
            }
        }

        if ($recursivityPeriod === 'monthly') {
            $start = new DateTime('now');
            $start->modify('first day of this month');

            $end = new DateTime($recursivityEnd);
            $end->modify('first day of next month');
            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($start, $interval, $end);

            foreach ($period as $dt) {
                $date = $dt->format('Y-m-' . $recursivityDay);
                if ($this->validateDate($date)) {
                    $dateArray[] = $date;
                }
            }
        }

        return $dateArray;
    }

    public function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }

    public function delete($mode, Article $article, $apiVersion)
    {
        if ($apiVersion === '3') {
            $this->em->remove($article);
            $this->em->flush();
            return true;
        }

        if ($article->getDuplicatedArticles()->count() !== 0) {
            return false;
        }

        if ($mode === "current") {
            $this->em->remove($article);
            $this->em->flush();
        }

        if ($mode === 'currentAndNext') {
            $this->em->getRepository("AppBundle:Article")->removeNextArticles($article);
        }

        return true;
    }
}
