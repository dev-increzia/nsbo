<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Welp\MailchimpBundle\Event\SubscriberEvent;
use Welp\MailchimpBundle\Subscriber\Subscriber;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;

class SubscriberCommand extends ContainerAwareCommand
{
    public $currentUserInfo;
    
    protected function configure()
    {
        $this->setName('nousensemble:subscribermailchimp')
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
        
        // Liste ID Mailchimp
        
        $listAllUsersId             ="3743da33d7";
        $listAssociationId          ="9994a7e9ef";
        $listMerchantId             ="8f28aaf829";
        $listAccountHavePublishedId ="6e0de62be2";
        $listInactiveAccountId      ="59b7cc44c5";
        

        // liste all users
        
        $usersInMailChimp=array(
                    $listAllUsersId             =>  $this->getUsersFromMailChimp($listAllUsersId),
                    $listAssociationId          =>  $this->getUsersFromMailChimp($listAssociationId),
                    $listMerchantId             =>  $this->getUsersFromMailChimp($listMerchantId),
                    $listAccountHavePublishedId =>  $this->getUsersFromMailChimp($listAccountHavePublishedId),
                    $listInactiveAccountId      =>  $this->getUsersFromMailChimp($listInactiveAccountId)
                );
        
        // liste desabled users
        
        $desabledUsers                          =   $em->getRepository('UserBundle:User')->findBy(array('enabled' => false));
        
        $roleListIds=array(
            'allusers'              =>$listAllUsersId,
            'association'           =>$listAssociationId,
            'merchant'              =>$listMerchantId,
            'accountHavePublished'  =>$listAccountHavePublishedId,
            'inactiveAccount'       =>$listInactiveAccountId,
            
        );
        
        $accountHavePublished = $em->getRepository('UserBundle:User')->findAccountHavePublished();
        $inactiveAccount      = $em->getRepository('UserBundle:User')->findInactiveAccount();
        $associationSuAdmin   = $em->getRepository('UserBundle:User')->search(null, null, null, null, null, null, null, "associationSuAdmin", null, null, null, null);
        $associationAdmin     = $em->getRepository('UserBundle:User')->search(null, null, null, null, null, null, null, "associationAdmin", null, null, null, null);
        $merchantSuAdmin      = $em->getRepository('UserBundle:User')->search(null, null, null, null, null, null, null, "merchantSuAdmin", null, null, null, null);
        $merchantAdmin        = $em->getRepository('UserBundle:User')->search(null, null, null, null, null, null, null, "merchantAdmin", null, null, null, null);
        $allUsers             = $em->getRepository('UserBundle:User')->findAll();
        
        $association = array_unique(array_merge($associationSuAdmin, $associationAdmin));
        $merchant    = array_unique(array_merge($merchantSuAdmin, $merchantAdmin));
        
        $users= array(
            "accountHavePublished"  =>  $this->getArrayUsers($accountHavePublished),
            "inactiveAccount"       =>  $this->getArrayUsers($inactiveAccount),
            "association"           =>  $this->getArrayUsers($association),
            "merchant"              =>  $this->getArrayUsers($merchant),
            "allusers"              =>  $this->getArrayUsers($allUsers)
        );
        
        unset($accountHavePublished);
        unset($inactiveAccount);
        unset($association);
        unset($merchant);
        unset($allUsers);
        
        foreach ($roleListIds as $role => $ListId) {
                
                //Add new
            foreach ($users[$role] as $user) {
                if ($user["email"] && strpos($user["email"], 'yopmail') === false) {
                    //
                    echo $user["email"]."\n\t";
                    try {
                        if (filter_var($user["email"], FILTER_VALIDATE_EMAIL) && !in_array($user["email"], $usersInMailChimp[$ListId])) {
                            $subscriber = new Subscriber($user["email"], [
                                        'FNAME' => $user["firstname"],
                                        'LNAME' => $user["lastname"]
                                            ], [
                                        'language' => 'fr'
                                    ]);
                                    
                            $this->currentUserInfo=$user["id"];
                                    
                            $dispatcher=$this->getContainer()->get('event_dispatcher');

                            $dispatcher->dispatch(
                                            SubscriberEvent::EVENT_SUBSCRIBE,
                                        new SubscriberEvent($ListId, $subscriber)
                                    );
                        }
                    } catch (\Exception $e) {
                        mail('sayda@celaneo.com', 'Hello Sayda sorry there is a Mailchimp Interruption', sprintf('Oops, exception thrown %s', $e->getMessage()." https://www.nous-ensemble.fr/admin/user/update/".$this->currentUserInfo));
                        continue;
                    }
                }
            }
        }
            
        //unsubscribe (users desabled) from mailchimp
                
        foreach ($desabledUsers as $desabledUser) {
            foreach ($usersInMailChimp as $mailchimpListId => $arrayEmailMailchimp) {
                if (in_array($desabledUser->getEmail(), $arrayEmailMailchimp)) {
                    $subscriber = new Subscriber($desabledUser->getEmail());

                    $this->container->get('event_dispatcher')->dispatch(
                            SubscriberEvent::EVENT_UNSUBSCRIBE,
                            new SubscriberEvent($mailchimpListId, $subscriber)
                        );
                }
            }
        }
            
            
        $output->writeln("Fin");
    }
    public function getUsersByType($usersMailChimp)
    {
        $users=array();
        
        $em = $this->getContainer()->get('doctrine')->getManager();
        
        foreach ($usersMailChimp as $userMailChimp) {
            $users[]=$em->getRepository('UserBundle:User')->findOneBy(array("email" => $userMailChimp->getEmail()));
        }
        return $users;
    }
    
    public function getUsersFromMailChimp($list_id)
    {
        $MailChimp = $this->getContainer()->get('welp_mailchimp.mailchimp_master');

        $countResult = $MailChimp->get("lists/$list_id/members");
        
        $result = $MailChimp->get("lists/$list_id/members", [
                    'count' => $countResult["total_items"],
                ]);

        $memberMailchimpAll=array();
        
        if ($MailChimp->success()) {
            foreach ($result["members"] as $member) {
                $memberMailchimpAll[]=$member["email_address"];
            }
        } else {
            echo $MailChimp->getLastError();
        }
        return $memberMailchimpAll;
    }
    public function getArrayUsers($users)
    {
        $arrayUsers=array();
        foreach ($users as $user) {
            $arrayUsers[]=array("id" => $user->getId(),"email" => $user->getEmail(),"firstname" => $user->getFirstname(),"lastname" => $user->getLastname());
        }
        return $arrayUsers;
    }
}
