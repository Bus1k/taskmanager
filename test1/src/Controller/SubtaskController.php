<?php

namespace App\Controller;

use App\Entity\Subtask;
use App\Entity\Task;
use App\Forms\TaskFormType;
use App\Forms\SubtaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubtaskController extends AbstractController
{
    /**
     * @Route("/addSubtask/{id}", name="add_subtask")
     * @param Request $request
     * @param Task $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addSubtask(Request $request, Task $id)
    {
        $form = $this->createForm(TaskFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $subtask = new Subtask();
            $subtask->setMainTask($id);
            $subtask->setSubTitle($form->get('title')->getData());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subtask);
            $entityManager->flush();

            return $this->redirectToRoute('to_do_list');
        }

        return $this->render('Subtask/create.html.twig', [
            'subTaskForm' => $form->createView(),
        ]);

    }

    /**
     * @Route("/editSubtask/{id}", name="edit_subtask")
     * @param Request $request
     * @param Subtask $subtask
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editSubtask(Request $request, Subtask $subtask)
    {
        $form = $this->createForm(SubtaskFormType::class, $subtask);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('to_do_list');
        }

        return $this->render('Subtask/edit.html.twig', [
            'subTaskForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/deleteSubtask/{id}", name="delete_subtask")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteSubtask(Subtask $id)
    {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->remove($id);
            $entityManager->flush();

            return $this->redirectToRoute('to_do_list');
    }
}
