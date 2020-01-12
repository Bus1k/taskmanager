<?php

namespace App\Controller;

use App\Entity\Subtask;
use App\Entity\Task;
use App\Forms\TaskFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class TaskController extends AbstractController
{
    /**
     * @Route("/", name="to_do_list" )
     */
    public function index()
    {
        if(!empty($this->getUser()))
        {
            $id = $this->getUser()->getId();
            $user = $this->getUser($id);

            $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy(['user' => $id]);
            $subtasks = $this->getDoctrine()->getRepository(Subtask::class)->findAll();

            return $this->render('Task/index.html.twig', ['tasks' => $tasks, 'user' => $user, 'subtasks' => $subtasks]);
        }

        return $this->redirectToRoute('login');
    }

    /**
     * Return user for partial view _navbar
     */
    public function navbar()
    {
        if(!empty($this->getUser()))
        {
            $id = $this->getUser()->getId();
            $user = $this->getUser($id);

            return $this->render('_navbar.html.twig', ['user' => $user]);
        }

        return $this->render('_navbar.html.twig');
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
        $task->setUser($this->getUser());

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
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
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

        return $this->render('Task/edit.html.twig', [
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
