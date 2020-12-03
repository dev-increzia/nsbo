<?php
namespace ApiBundle\Service;

use Entities\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserBundle\UserBundle;

class CommunityV3
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param $request
     * @param $em
     * @param \UserBundle\Entity\User $user
     * @param $community
     * @param $data
     * @return array
     */
    public function removeAdmins($request, $em, $user, $community, $data)
    {
        if (!$user->isCommunitySuAdmin($community) || !$community->getEnabled()) {
            throw new AccessDeniedException();
        }
         $admin = $em->getRepository("UserBundle:User")->find($data['admins']);
        $community->removeAdmin($admin);
        $admin->setCommunityAdmin(null);
        $adminRights = $em->getRepository("AppBundle:AccessAdminCommunity")->findBy(array('accessUsers' => $admin,'community' => $community ));
        foreach ($adminRights as $adminRight){
            $em->remove($adminRight);
        }
        $message = "Vous n'êtes plus un administrateur de la communauté " . $community->getName() . ". ";
        $this->container->get('notification')->notify($admin, 'admin', $message, false);
        $this->container->get('mobile')->pushNotification($admin, 'NOUS-ENSEMBLE ', "", false, false, 'on');
        $em->flush();

        return array("success" => true);
    }
}