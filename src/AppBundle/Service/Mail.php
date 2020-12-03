<?php

namespace AppBundle\Service;

use AppBundle\Entity\Carpooling;

use AppBundle\Entity\ReportingObjectHeading;
use Swift_Attachment;
use UserBundle\Entity\User;

class Mail
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function suAdminCityhallAccess($user, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Accès de votre compte super-admin communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function adminCityhallAccess($user, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Accès de votre compte admin communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function updateReportingModerate($user, $content, $reporting)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre signalement ' . $reporting->getTitle())
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function userReport($content, $object, $to)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Signalement de compte citoyen : ' . $object)
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($to);
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableUser($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre compte citoyen a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }
    
    public function acceptUser($user, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre demande de liaison à une Communauté a été acceptée')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }
    
    public function refuseUser($user, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre demande de liaison à une Communauté a été refusée')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableUserAdmin($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre compte admin communauté a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function commentDelete($mails, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Suppression d’un commentaire')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($mails);
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function moderateAssociation($user, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre groupe/association a été accepté par la communauté.')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableAssociation($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre groupe / association a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function moderateMerchant($user, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre commerce / partenaire a été accepté par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableMerchant($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre commerce / partenaire a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function updateArticle($user, $content, $article)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre article ' . $article->getTitle() . ' a été mis à jour')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableArticle($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre article a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function updateEvent($user, $content, $event)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre événement ' . $event->getTitle() . ' a été mis à jour')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableEvent($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre événement a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function enableGoodPlan($user, $content, $enable)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble Votre bon plan a été ' . ($enable ? 'activé' : 'désactivé') . ' par la communauté')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function moderateEvent($user, $content,$entity)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Votre événement a été '.($entity->getModerate() == 'accepted' ? 'accepté' : 'refusé').' par la communauté')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function moderateGoodPlan($user, $content)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble Votre bon plan a été accepté par la communauté')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }
    
    public function newArticleEvent($content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Un événement associé à un article a été créé')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($this->container->getParameter('event_email'));
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }
    
    public function deleteAccountMail($subject, $content)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($this->container->getParameter('admin_email'));
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function addSuAdminAssociation($user, $content, $association)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Vous êtes maintenant super-administrateur du groupe / association ' . $association->getName())
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function removeSuAdminAssociation($user, $content, $association)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Vous n\êtes plus super-administrateur du groupe / association ' . $association->getName())
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function addSuAdminMerchant($user, $content, $merchant)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Vous êtes maintenant super-administrateur du commerce / partenaire ' . $merchant->getName())
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function removeSuAdminMerchant($user, $content, $merchant)
    {
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Vous n\êtes plus super-administrateur du commerce / partenaire ' . $merchant->getName())
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());
        $message->addPart($content, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    //api mails


    public function contactMail($body, $to, $objet, $filepath=null)
    {
        /*$body = $this->container->get('templating')->renderResponse('AppBundle:Mail:contact.html.twig', [
            'user' => $user,
            'content' => $content,
            'photo' => $photo,
            'location' => $location
        ]);*/

        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble - ' . $objet)
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($to);
        if($filepath && file_exists($filepath)) {
            $message->attach(\Swift_Attachment::fromPath($filepath));
        }

        /*dump($message->attach(\Swift_Attachment::fromPath($filepath)));
        exit;*/

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function contactConfirmationMail($body, $object, $user)
    {


        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble - ' . $object)
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());


        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendCreationMail($user, $type)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:accountCreation.html.twig', [
            'user' => $user,
            'type' => $type,
        ]);

        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Compte ' . $type . ' en attente de validation')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendResettingMail($user, $password)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:resetting_password.html.twig', [
            'password' => $password,
        ]);
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Demande de réinitialisation du mot de passe')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($user->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendBenevolesMail($sender, $user, $object, $email, $event, $account, $type)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:benvoles.html.twig', [
            'email' => $email,
            'event' => $event,
            'account' => $account,
            'type' => $type == "merchant" ? "du commerce / partenaire" : "du groupe / association",
        ]);

        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble ' . $object)
                ->setFrom($sender->getEmail())
                ->setTo($user->getEmail());

        $message->addPart($body, 'text/html');

        $this->container->get('mailer')->send($message);
    }

    public function sendRoomMail($sender, $user, $event, $content)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:roomAsking.html.twig', [
            'sender' => $sender,
            'event' => $event,
            'content' => $content,
        ]);

        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble Demande de réservation d\'une salle')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            //->setFrom($sender->getEmail())
            ->setTo($user);

        $message->addPart($body, 'text/html');

        $this->container->get('mailer')->send($message);
    }

    public function sendConfirmationMail($user, $email, $password)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:confirmation.html.twig', [
            'email' => $email,
            'password' => $password,
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
        ]);

        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble Validation Compte')
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($email);

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendInfoAdminMail($email, $user, $type, $entity)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:addAdmin.html.twig', [
            'user' => $user,
            'type' => $type,
            'entity' => $entity
        ]);
        $account = ($type == 'association') ? " du groupe / association " : " du commerce / partenaire ";
        $subject ="NOUS Ensemble Vous êtes maintenant administrateur  " . $account . " " . $entity->getName();
        $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($email);

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendInvitationMail($email, $user)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:invitation.html.twig', [
            'user' => $user,
        ]);
        $message = \Swift_Message::newInstance()
                ->setSubject('NOUS Ensemble ' . $user->getLastname() . " vous a invité à rejoindre NOUS-Ensemble")
                ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
                ->setTo($email);

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendJoinPrivateCommunity($email, $user,$community)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:joinPrivateCommunity.html.twig', [
            'user' => $user,
            'community' => $community
        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble ' . $user->getLastname() . " souhaite rejoindre votre communauté privée ".$community->getName())
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($email);

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendCarpoolAnswerCreator($carpool, $user,$phoneUser)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:carpoolAnswerCreator.html.twig', [
            'user' => $user,
            'phoneUser' => $phoneUser,
            'carpool' => $carpool
        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble ' . $user->getLastname() . " souhaite rejoindre votre covoiturage")
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($carpool->getCreateBy()->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }
    public function sendCarpoolAnswerUser($carpool, $user)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:carpoolAnswerUser.html.twig', [
            'user' => $user,
            'carpool' => $carpool
        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble demande de covoiturage')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($user->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendCancelCarpoolAnswerCreator($carpool, $user)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:carpoolCancelAnswerCreator.html.twig', [
            'user' => $user,

            'carpool' => $carpool
        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble ' . $user->getLastname() . " a annulé sa demande de covoiturage")
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($carpool->getCreateBy()->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }
    public function sendCancelCarpoolAnswerUser($carpool, $user)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:carpoolCancelAnswerUser.html.twig', [
            'user' => $user,
            'carpool' => $carpool
        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble annulation de la demande de covoiturage')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($user->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function sendJoinMerchantOrAssociation($user,$merchant,$phone, $merchantOrAssociation)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:joinMerchantOrAssociation.html.twig', [
            'user' => $user,
            'phone' => $phone,
            'merchant' => $merchant,
            'merchantOrAssociation' => $merchantOrAssociation
        ]);
        if($merchantOrAssociation == 'merchant') {
            $subject = 'NOUS Ensemble ' . $user->getLastname() . " souhaite rejoindre Votre commerce / partenaire ".$merchant->getName();
        }else{
            $subject = 'NOUS Ensemble ' . $user->getLastname() . " souhaite rejoindre votre Groupe / Association ".$merchant->getName();
        }
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($merchant->getSuAdmin()->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    public function contactMerchant($goodplan, $user, $data)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:contactMerchant.html.twig', [
            'user' => $user,
            'goodplan' => $goodplan,
            'data' => $data
        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble d\'information')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($goodplan->getMerchant()->getSuAdmin()->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }

    /**
     * @param Carpooling $carpool
     * @param User $user
     */
    public function sendCarpoolRecap($carpool, $user)
    {
        $body = $this->container->get('templating')->render('AppBundle:Mail:mailRecapCarpool.html.twig', [
            'user' => $user,
            'carpool' => $carpool,

        ]);
        $message = \Swift_Message::newInstance()
            ->setSubject('NOUS Ensemble d\'information')
            ->setFrom(array($this->container->getParameter('no_reply_email') => $this->container->getParameter('no_reply_name')))
            ->setTo($user->getEmail());

        $message->addPart($body, 'text/html');
        $this->container->get('mailer')->send($message);
    }
}
