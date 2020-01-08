<?php

namespace App\Controller;

use App\Entity\User;

use App\Forms\UserEditFormType;
use App\Forms\UserRegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class RegisterController extends AbstractController
{
    /**
     *
     * Request z pakietu HttpFoundation, który pozwoli nam w łatwy sposób obsłużyć formularz
     * passwordEncoder – obiekt umożliwiający nam zakodowanie naszego plain password
     * entityManager, który pozwoli nam na zapisanie nowej encji do bazy danych
     * session z pakietu HttpFoundation. Pozwala on na tworzenie flash message, aby powiadomić użytkownika czy jego żądanie zostało przetworzone poprawnie
     */

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     * @param Session $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, Session $session)
    {
        $form = $this->createForm(UserRegisterFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $user = new User($form->get('username')->getData());
            $password = $passwordEncoder->encodePassword($user, $form->get('password')->getData());
            $user->setPassword($password);

            try
            {
                $entityManager->persist($user);
                $entityManager->flush();
                $session->getFlashBag()->add('success', sprintf('Account %s has been created!', $user->getUsername()));

                return $this->redirectToRoute('login');
            }
            catch(UniqueConstraintViolationException $exception)
            {
                $session->getFlashBag()->add('danger','Email and username has to be unique');
            }
        }


        return $this->render('User/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/deleteUser/{id}", name="delete_user")
     */
    public function delete(User $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('to_do_list');
    }

    /**
     * @Route("/editUser/{id}", name="edit_user")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createForm(UserEditFormType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $password = $passwordEncoder->encodePassword($user, $form->get('password')->getData());
            $user->setPassword($password);
            $entityManager->flush();

            return $this->redirectToRoute('to_do_list');
        }

        return $this->render('User/edit.html.twig', [
            'editUserForm' => $form->createView(),
        ]);

    }

}
