<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SubtaskController extends AbstractController
{
    /**
     * @Route("/subtask", name="subtask")
     */
    public function index()
    {
        return $this->render('subtask/index.html.twig', [
            'controller_name' => 'SubtaskController',
        ]);
    }
}
