<?php

namespace Librecores\ProjectRepoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Librecores\ProjectRepoBundle\Entity\User;

class UserController extends Controller {

    /**
     * View a user's profile
     *
     * @param User $user
     * @return Response
     */
    public function viewAction(Request $request, User $user)
    {
        return $this->render('LibrecoresProjectRepoBundle:User:view.html.twig',
            array('user' => $user));
    }
}
