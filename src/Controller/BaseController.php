<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Symfony\Component\Mime\Email;

use App\Form\UserType;
use App\Entity\User;
use App\Form\UserRoleType;
use App\entity\Contact;
use App\Form\ContactType;






class BaseController extends AbstractController
{
    #[Route('/base', name: 'app_base')]
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [
            'controller_name' => 'BaseController',
        ]);
    }

    #[Route('/profil', name: 'profil')]
    public function profil(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        return $this->render('base/profil.html.twig');
   
    }

    #[Route('/liste-user', name: 'liste-user')]
    public function listeUser(EntityManagerInterface $entityManagerInterface): Response
    {

        $repoUser = $entityManagerInterface->getRepository(User::class);
        $users = $repoUser->findAll();
        return $this->render('base/liste_user.html.twig', [
            'users' => $users
        ]);
    }
    
    #[Route('/supprimerUser/{id}', name: 'supprimerUser')]
    public function supprimerUser(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $id = $request->get('id');
        $repoUser = $entityManagerInterface->getRepository(User::class);
        $user=$repoUser->find($id);
        $entityManagerInterface->remove($user);
        $entityManagerInterface->flush();
        $this->addFlash('notice','Modifications effectué');

        return $this->redirectToRoute('liste-user');
    }



    #[Route('/editIsVerified/{id}', name: 'editIsVerified')]
    public function editIsVerified(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $id = $request->get('id');
        $repoUser = $entityManagerInterface->getRepository(User::class);
        $user = $repoUser->find($id);
    
        $user->setIsVerified(true); 
    
        $entityManagerInterface->flush();
    
        $this->addFlash('notice', 'Modifications effectuées');

        return $this->redirectToRoute('liste-user');
    }


    #[Route('/admin/editUser/{id}', name: 'editUser')]
    public function editUser(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $id = $request -> get('id');
        $UserRepository = $entityManagerInterface -> getRepository(User::class);

        $user = $UserRepository -> find($id);
        $form = $this->createForm(UserType::class, $user);
        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid()){
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();
                $this->addFlash('notice','Modifications effectué');
            }else{ $this->addFlash('notice','Modifications non effectué');}
        }
        return $this->render('base/editUser.html.twig', [
            'form' => $form ->createview()
     ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);

        if($request->isMethod('POST')){
            $form->handleRequest($request);
            if ($form->isSubmitted()&&$form->isValid()){   
                $email = (new TemplatedEmail())
                ->from($form->get('email')->getData())
                ->to('ultrabaga@hotmail.com')
                ->subject($form->get('sujet')->getData())
                ->htmlTemplate('base/email.html.twig')
                ->context([
                    'nom'=> $form->get('email')->getData(),
                    'sujet'=> $form->get('sujet')->getData(),
                    'message'=> $form->get('message')->getData()
                ]);
              
                $mailer->send($email);
                $this->addFlash('notice','Message envoyé');
            }
        }
        return $this->render('base/contact.html.twig', [
            'form' => $form ->createview()
     ]);    }
}
