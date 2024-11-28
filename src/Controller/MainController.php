<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;

class MainController extends AbstractController
{
    #[Route('/main', name: 'main_index')]
    public function index(CategoryRepository $categoryRepository, PostRepository $postRepository): Response
    {
        $categories = $categoryRepository->findAll();
        $posts = $postRepository->findAll();

        return $this->render('main/index.html.twig', [
            'categories' => $categories,
            'posts' => $posts,
        ]);
    }
}
