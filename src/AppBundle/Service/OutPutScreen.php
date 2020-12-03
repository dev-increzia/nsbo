<?php

namespace AppBundle\Service;

class OutPutScreen
{
    public function outPutDelete($hrefContent)
    {
        return '<a alt="Supprimer" title="Supprimer" class="btn btn-default" href="' . $hrefContent . '" onclick="return confirm(\'Êtes-vous sur de vouloir supprimer ?\');"><i class="fa fa-remove"></i></a>';
    }

    public function outPutUpdate($hrefContent)
    {
        return '<a alt="Editer" title="Editer" class="btn btn-default" href="' . $hrefContent . '"><i class="fa fa-pencil"></i></a>';
    }

    public function outAddAdmin($hrefContent)
    {
        return '<a alt="Ajouter administrateur" title="Ajouter administrateur" class="btn btn-default" href="' . $hrefContent . '"><i class="fa fa-plus"></i></a>';
    }

    public function outPutView($hrefContent, $title = 'Voir')
    {
        return '<a alt="' . $title . '" title="' . $title . '" class="btn btn-default" href="' . $hrefContent . '"><i class="fa fa-eye"></i></a>';
    }

    public function outPutDuplicate($hrefContent)
    {
        return '<a alt="Dupliquer" title="Dupliquer" class="btn btn-default" href="' . $hrefContent . '"><i class="fa fa-copy"></i></a>';
    }

    public function outPutAccess($hrefContent)
    {
        return '<a alt="Renvoyer les accès super-admin par email" title="Renvoyer les accès super-admin par email" class="btn btn-default" href="' . $hrefContent . '" onclick="return confirm(\'Voulez-vous vraiment renvoyer les identifiants et mot de passe par mail?\');"><i class="fa fa-key"></i></a>';
    }

    public function outPutAccessUser($hrefContent)
    {
        return '<a alt="Envoyer le mot de passe de la communauté" title="Envoyer le mot de passe de la communauté" class="btn btn-default" href="' . $hrefContent . '" onclick="return confirm(\'Voulez-vous vraiment envoyer le mot de passe de la communauté par mail?\');"><i class="fa fa-key"></i></a>';
    }
    
    public function outPutRefuseUser($hrefContent)
    {
        return '<a alt="Envoyer un mail de refus de liaison à la communauté" title="Envoyer un mail de refus de liaison à la communauté" class="btn btn-default" href="' . $hrefContent . '" onclick="return confirm(\'Voulez-vous vraiment envoyer le mail de refus de liaison à la communauté?\');"><i class="fa fa-minus-circle "></i></a>';
    }
    

    public function outPutModalView($url, $anchor)
    {
        return '<a alt="Détail" title="Détail" class="btn btn-default ajax" href="' . $url . '" data-target="#' . $anchor . '"><i class="fa fa-eye"></i></a>';
    }

    public function outPutModerate($hrefContent, $checked)
    {
        return '<input type="checkbox" ' . ($checked ? 'checked' : '') . ' class="make-switch moderate abus" data-size="mini" data-url="' . $hrefContent . '">';
    }

    public function outPutEnabled($hrefContent, $communityName)
    {
        return '<a alt="Supprimer la relation" title="Supprimer la relation" class="btn btn-danger btn-xs" href="' . $hrefContent . '" onclick="return confirm(\'Êtes-vous sur de vouloir supprimer cette relation?\');"><i class="fa fa-remove"></i>&nbsp;'.$communityName.'</a><br>';
    }

    public function outPutImage($imagePath, $imageId)
    {
        if ($imagePath) {
            $output = '';
            $output .= '<img src="' . $imagePath . '" alt="" class="thumbnail img-responsive cursor-pointer" style="max-width: 100px;" alt="Cliquer pour agrandir" data-toggle="modal" data-target="#modalImage' . $imageId . '"/>';
            $output .= '<div id="modalImage' . $imageId . '" class="modal fade" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><img src="' . $imagePath . '" class="img-responsive"></div></div></div></div>';

            return $output;
        }
    }

    public function outPutInfo($url, $anchor, $name)
    {
        return '<a alt="Cliquer pour voir les informations" title="Cliquer pour voir les informations" class="ajax" href="' . $url . '" data-target="#' . $anchor . '">' . $name . '</i></a>';
    }

    public function outPutModerateReporting($moderate, $href)
    {
        return '<select class="form-control updateModerateReporting" data-url="' . $href . '"><option value="wait" ' . ($moderate == 'wait' ? 'selected' : '') . '>En cours</option><option value="on" ' . ($moderate == 'on' ? 'selected' : '') . '>Traité</option><option value="off" ' . ($moderate == 'off' ? 'selected' : '') . '>Non traité</option></select>';
    }

    public function outPutMailReporting($email)
    {
        return '<a alt="Envoyer un e-mail à l\'auteur" title="Envoyer un e-mail à l\'auteur" class="btn btn-default" href="mailto:' . $email . '"><i class="fa fa-envelope"></i></a>';
    }

    public function outPutUserMail($email)
    {
        return '<a alt="Envoyer un e-mail au citoyen" title="Envoyer un e-mail au citoyen" class="btn btn-default" href="mailto:' . $email . '"><i class="fa fa-envelope"></i></a>';
    }

    public function outPutUserReport($url, $anchor)
    {
        return '<a alt="Signaler le citoyen" title="Signaler le citoyen" class="btn btn-default ajax" href="' . $url . '" data-target="#' . $anchor . '"><i class="fa fa-warning"></i></a>';
    }

    public function outPutArticle($url, $article)
    {
        return '<a alt="Voir l\'article" title="Voir l\'article" class="" href="' . $url . '" >' . $article->getTitle() . '</a>';
    }

    public function outPutCities($cities)
    {
        $html = '<ul>';
        foreach ($cities as $d) {
            if (is_object($d)) {
                $html .= '<li>' . $d->getName() . '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }


    public function outPutRoles($entity)
    {
        $html = '<ul>';
        foreach ($entity->getAssociationsAdmin() as $associationAdmin) {
            if (is_object($associationAdmin)) {
                $html .= '<li>Administrateur association : ' . $associationAdmin->getName() . '</li>';
            }
        }
        foreach ($entity->getAssociationsSuAdmin() as $associationSuAdmin) {
            if (is_object($associationSuAdmin)) {
                $html .= '<li>Super-Administrateur association : ' . $associationSuAdmin->getName() . '</li>';
            }
        }
        foreach ($entity->getMerchantsAdmin() as $merchantAdmin) {
            if (is_object($merchantAdmin)) {
                $html .= '<li>Administrateur commerçant : ' . $merchantAdmin->getName() . '</li>';
            }
        }
        foreach ($entity->getMerchantsSuAdmin() as $merchantSuAdmin) {
            if (is_object($merchantSuAdmin)) {
                $html .= '<li>Super-Administrateur commerçant : ' . $merchantSuAdmin->getName() . '</li>';
            }
        }
        $html .= '</ul>';
        return $html;
    }


    public function outPutObject($entity)
    {
        $html = '<ul>';
        foreach ($entity->getObjects() as $o) {
            if (is_object($o)) {
                $html .= '<li>' . $o->getObjet() . '</li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function outPutSuAdmins($entity)
    {
        $html = '<ul>';
        foreach ($entity->getCommunitySuadmins() as $o) {
            if (is_object($o)) {
                $html .= '<li>' . $o->getEmail() . '</li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function outPutObjectPhoneBook($entity)
    {
        $html = '<ul>';
        foreach ($entity->getObjects() as $o) {
            if (is_object($o)) {
                $html .= '<li>' . $o->getName() . '</li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function outPutRolesCsv($entity)
    {
        $html = '';
        foreach ($entity->getAssociationsAdmin() as $associationAdmin) {
            if (is_object($associationAdmin)) {
                $html .= 'Administrateur association : ' . $associationAdmin->getName() . ' , ';
            }
        }
        foreach ($entity->getAssociationsSuAdmin() as $associationSuAdmin) {
            if (is_object($associationSuAdmin)) {
                $html .= 'Super-Administrateur association : ' . $associationSuAdmin->getName() . ' , ';
            }
        }
        foreach ($entity->getMerchantsAdmin() as $merchantAdmin) {
            if (is_object($merchantAdmin)) {
                $html .= 'Administrateur commerçant : ' . $merchantAdmin->getName() . ' , ';
            }
        }
        foreach ($entity->getMerchantsSuAdmin() as $merchantSuAdmin) {
            if (is_object($merchantSuAdmin)) {
                $html .= 'Super-Administrateur commerçant : ' . $merchantSuAdmin->getName() . ' , ';
            }
        }
        return $html;
    }
}
