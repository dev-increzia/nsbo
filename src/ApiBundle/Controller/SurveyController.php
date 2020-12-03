<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Survey;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\SurveyQuestionChoice;
use AppBundle\Entity\SurveyResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\File;

class SurveyController extends Controller
{

    /**
     * @ApiDoc(resource="/api/survey/new",
     * description="API add survey",
     * statusCodes={200="Successful"})
     */
    public function addAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas);
        $community = $em->getRepository('AppBundle:Community')->find($data['community']);
        $survey = new Survey();
        $survey->setCreateAt(new \DateTime('now'));
        $survey->setUpdateAt(new \DateTime('now'));
        $survey->setPublicAt(new \DateTime('now'));
        $survey->setCreateBy($user);
        $survey->setUpdateBy($user);
        $survey->setDescription($data['sondage']);
        $survey->setTitle($data['title']);

        $surveyQuestion = new SurveyQuestion();
        $surveyQuestion->setTitle($data['title']);
        $surveyQuestion->setSurvey($survey);
        $survey->setCommunity($community);

        foreach ($data['questions'] as $question) {
            $surveyQuestionChoice = new SurveyQuestionChoice();
            $surveyQuestionChoice->setTitle($question->value);
            $surveyQuestionChoice->setQuestion($surveyQuestion);

            $surveyQuestion->addChoice($surveyQuestionChoice);
        }

        $survey->addQuestion($surveyQuestion);
        if ($data['photo']) {
            $image = new File();
            $image->base64($data['photo']);
            $survey->setImage($image);
        }

        $em->persist($survey);
        $em->flush();

        return array("success" => true);
    }

    /**
     * @ApiDoc(resource="/api/survey/addResponse",
     * description="API add response survey",
     * statusCodes={200="Successful"})
     */
    public function addResponseAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->getEnabled()) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();
        $datas = $request->getContent();
        $data = (array)json_decode($datas,true);
        $article = $data['currentArticle'];
        $choice = $em->getRepository('AppBundle:SurveyQuestionChoice')->find($data['idResponse']);
        $existResponseUser = $em->getRepository('AppBundle:SurveyResponse')->getResponseSurvey($user,$data['idResponse']);

        if (!$existResponseUser) {
            $surveyResponse = new SurveyResponse();
            $surveyResponse->setResponse($choice)
                ->setUser($user)
                ->setAddedAt(new \DateTime('now'));

            $em->persist($surveyResponse);
            $em->flush();
            $article['nbrResponse'] ++;
            $article['questions'][0]['alreadyAnswer'] = $data['idResponse'];
            foreach ($article['questions'][0]['choices'] as $key => $choiceArticle){
                if ($choiceArticle['id'] == $data['idResponse']){
                    $article['questions'][0]['choices'][$key]['responseNumbr'] ++;
                }
            }
        }

        return array("success" => true,'article' => json_encode($article));
    }

}