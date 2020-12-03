<?php

namespace ApiBundle\Service;

use AppBundle\Entity\Comment as Comment;

class CommentV3
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function commentsArticleDetails($request, $em, $article)
    {
        $result = array();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        /** @var Comment[] $comments */
        $comments = $em->getRepository("AppBundle:Comment")->findArticleComments($article);
        foreach ($comments as $comment) {
            $replies = array();
            $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment['id']);
            switch ($comment['type']) {
                case 'citizen':
                    if (isset($comment["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($comment["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($comment["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            if (isset($comment["commentImg"])) {
                $img = $em->getRepository("AppBundle:File")->find($comment["commentImg"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["commentImg"] = $baseurl . $path;
                    }
                }
            }
            if (isset($comment["commentDoc"])) {
                $doc = $em->getRepository("AppBundle:File")->find($comment["commentDoc"]);
                if ($doc) {
                    $path = $helper->asset($doc, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["document"] = $baseurl . $path;
                    }
                }
            }
            foreach ($secondLevel as $value) {
                if (isset($value["commentImg"])) {
                    $img = $em->getRepository("AppBundle:File")->find($value["commentImg"]);
                    if ($img) {
                        $path = $helper->asset($img, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["commentImg"] = $baseurl . $path;
                        }
                    }
                }
                if (isset($value["commentDoc"])) {
                    $doc = $em->getRepository("AppBundle:File")->find($value["commentDoc"]);
                    if ($doc) {
                        $path = $helper->asset($doc, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["document"] = $baseurl . $path;
                        }
                    }
                }
                switch ($value['type']) {
                    case 'citizen':
                        if (isset($value["userImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user.jpg";
                        }

                        break;

                    case 'association':
                        if (isset($value["associationImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    case 'merchant':
                        if (isset($value["merchantImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    default:
                        break;
                }
                if (($value['type'] == "citizen" && $value["userId"]) ||
                        ($value['type'] == "association" && $value["associationId"]) ||
                        ($value['type'] == "merchant" && $value["merchantId"])) {
                    $replies[] = $value;
                }
            }
            $comment['replies'] = $replies;
            if (($comment['type'] == "citizen" && $comment["userId"]) ||
                    ($comment['type'] == "association" && $comment["associationId"]) ||
                    ($comment['type'] == "merchant" && $comment["merchantId"])) {
                $result[] = $comment;
            }
        }
        return $result;
    }

    public function commentsEventDetails($request, $em, $event)
    {
        $result = array();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $comments = $em->getRepository("AppBundle:Comment")->findEventComments($event);
        foreach ($comments as $comment) {
            $replies = array();
            $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment['id']);
            switch ($comment['type']) {
                case 'citizen':
                    if (isset($comment["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($comment["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($comment["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            if (isset($comment["commentImg"])) {
                $img = $em->getRepository("AppBundle:File")->find($comment["commentImg"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["commentImg"] = $baseurl . $path;
                    }
                }
            }
            foreach ($secondLevel as $value) {
                if (isset($value["commentImg"])) {
                    $img = $em->getRepository("AppBundle:File")->find($value["commentImg"]);
                    if ($img) {
                        $path = $helper->asset($img, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["commentImg"] = $baseurl . $path;
                        }
                    }
                }
                switch ($value['type']) {
                    case 'citizen':
                        if (isset($value["userImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user.jpg";
                        }

                        break;

                    case 'association':
                        if (isset($value["associationImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    case 'merchant':
                        if (isset($value["merchantImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    default:
                        break;
                }
                
                if (($value['type'] == "citizen" && $value["userId"]) ||
                        ($value['type'] == "association" && $value["associationId"]) ||
                        ($value['type'] == "merchant" && $value["merchantId"])) {
                    $replies[] = $value;
                }
            }
            $comment['replies'] = $replies;
            if (($comment['type'] == "citizen" && $comment["userId"]) ||
                    ($comment['type'] == "association" && $comment["associationId"]) ||
                    ($comment['type'] == "merchant" && $comment["merchantId"])) {
                $result[] = $comment;
            }
        }
        return $result;
    }

    public function commentsGoodPlanDetails($request, $em, $event)
    {
        $result = array();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $comments = $em->getRepository("AppBundle:Comment")->findGoodPlanComments($event);
        foreach ($comments as $comment) {
            $replies = array();
            $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment['id']);
            switch ($comment['type']) {
                case 'citizen':
                    if (isset($comment["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($comment["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($comment["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($comment["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $comment["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $comment["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            if (isset($comment["commentImg"])) {
                $img = $em->getRepository("AppBundle:File")->find($comment["commentImg"]);
                if ($img) {
                    $path = $helper->asset($img, 'file');
                    $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                    if ($path) {
                        $comment["commentImg"] = $baseurl . $path;
                    }
                }
            }
            foreach ($secondLevel as $value) {
                if (isset($value["commentImg"])) {
                    $img = $em->getRepository("AppBundle:File")->find($value["commentImg"]);
                    if ($img) {
                        $path = $helper->asset($img, 'file');
                        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                        if ($path) {
                            $value["commentImg"] = $baseurl . $path;
                        }
                    }
                }
                switch ($value['type']) {
                    case 'citizen':
                        if (isset($value["userImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user.jpg";
                        }

                        break;

                    case 'association':
                        if (isset($value["associationImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    case 'merchant':
                        if (isset($value["merchantImg"])) {
                            $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                            if ($img) {
                                $path = $helper->asset($img, 'file');
                                $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                                if ($path) {
                                    $value["imageURL"] = $baseurl . $path;
                                }
                            }
                        } else {
                            $value["imageURL"] = "assets/img/user_default.png";
                        }

                        break;

                    default:
                        break;
                }

                if (($value['type'] == "citizen" && $value["userId"]) ||
                    ($value['type'] == "association" && $value["associationId"]) ||
                    ($value['type'] == "merchant" && $value["merchantId"])) {
                    $replies[] = $value;
                }
            }
            $comment['replies'] = $replies;
            if (($comment['type'] == "citizen" && $comment["userId"]) ||
                ($comment['type'] == "association" && $comment["associationId"]) ||
                ($comment['type'] == "merchant" && $comment["merchantId"])) {
                $result[] = $comment;
            }
        }
        return $result;
    }

    public function commentRepliesDetails($request, $em, $comment)
    {
        $result = array();
        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        $secondLevel = $em->getRepository("AppBundle:Comment")->commentReplies($comment);
        foreach ($secondLevel as $value) {
            switch ($value['type']) {
                case 'citizen':
                    if (isset($value["userImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($value["userImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $value["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $value["imageURL"] = "assets/img/user.jpg";
                    }

                    break;

                case 'association':
                    if (isset($value["associationImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($value["associationImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $value["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $value["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                case 'merchant':
                    if (isset($value["merchantImg"])) {
                        $img = $em->getRepository("AppBundle:File")->find($value["merchantImg"]);
                        if ($img) {
                            $path = $helper->asset($img, 'file');
                            $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
                            if ($path) {
                                $value["imageURL"] = $baseurl . $path;
                            }
                        }
                    } else {
                        $value["imageURL"] = "assets/img/user_default.png";
                    }

                    break;

                default:
                    break;
            }
            $result[] = $value;
        }
        return $result;
    }
}
