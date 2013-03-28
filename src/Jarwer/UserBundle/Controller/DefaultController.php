<?php

namespace Jarwer\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Jarwer\UserBundle\Entity\User;
use Jarwer\UserBundle\Form\UserType;

class DefaultController extends Controller
{
    /**
     * @Route("/login", name="user_login")
     * @Template()
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
        );
    }
    
    /**
     * @Route("/login_check", name="user_login_check")
     */
    public function loginCheckAction()
    {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="user_logout")
     */
    public function logoutAction()
    {
        // The security layer will intercept this request
    }
    
    /**
     * @Route("/registro", name="user_register")
     * @Template()
     */
    public function registerAction()
    {
        
        $request = $this->getRequest();
        
        $user = new User();
        $form = $this->createForm(new UserType(), $user);
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {

                $encoder = $this->get('security.encoder_factory')
                    ->getEncoder($user);
                $user->setSalt(md5(time()));
                $passwordEncoded = $encoder->encodePassword(
                    $user->getPassword(),
                    $user->getSalt()
                );
                $user->setPassword($passwordEncoded);
                
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($user);
                $em->flush();
                
                $this->get('session')->setFlash('info',
                    '¡Enhorabuena! Te has registrado correctamente.');
                
                $token = new UsernamePasswordToken(
                    $user,
                    $user->getPassword(),
                    'users',
                    $user->getRoles()
                );
                $this->container->get('security.context')->setToken($token);
                
                return $this->redirect($this->generateUrl('_welcome'));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
    
}
