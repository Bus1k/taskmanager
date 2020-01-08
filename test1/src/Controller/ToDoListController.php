<?php

namespace App\Controller;


use App\Entity\Task;
use App\Entity\User;
use App\Forms\TaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ToDoListController extends AbstractController
{
    /**
     * @Route("/", name="to_do_list" )
     */
    public function index()
    {
        $id = $this->getUser()->getId();
        $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy(['user' => $id]);

        //var_dump($tasks);
        return $this->render('index.html.twig', ['tasks' => $tasks]);
    }


    /**
     * @Route("/create", name="create_task", methods={"POST"})
     * @param Request $request
     * @return RedirectResponse
     */
    public function create(Request $request)
    {
        $title = trim($request->request->get('taskTitle'));

        if(empty($title))
        {
            return $this->redirectToRoute('to_do_list');
        }

        //objekt do zapisywania danych do bazy danych
        $entityManager = $this->getDoctrine()->getManager();

        $task = new Task();
        $task->setTitle($title);
        $task->setaddDate();

        //prepare task to add
        $entityManager->persist($task);
        //add to database
        $entityManager->flush();

        return $this->redirectToRoute('to_do_list');
    }


    /**
     * @Route("/update/{id}", name="update_task")
     * @param $id
     * @return RedirectResponse
     */
    public function updateStatus($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        //pobranie rekodu o danym id z bazy danych
        $task = $entityManager->getRepository(Task::class)->find($id);
        //ustawienie statusu przeciwnego do obecnego statusu taska
        $task->setStatus(!$task->getStatus());
        $entityManager->flush();

        return $this->redirectToRoute('to_do_list');
    }

    /**
     * @Route("/edit/{id}", name="edit_task")
     * @param Request $request
     * @param Task $task
     * @return RedirectResponse
     */
    public function editTitle(Request $request, Task $task)
    {
        $form = $this->createForm(TaskFormType::class, $task);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('to_do_list');
        }


        return $this->render('edit.html.twig', [
            'taskForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/delete/{id}", name="delete_task")
     * @param $id
     * @return RedirectResponse
     */

    //usuwam cały task dlatego podaje Task $id
    public function delete(Task $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($id);
        $entityManager->flush();

        return $this->redirectToRoute('to_do_list');
    }

}
