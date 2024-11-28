<?php
namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\CategoryRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/article')]
class PostController extends AbstractController
{
    #[Route('/', name: 'post_list')]
    public function list(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();
        return $this->render('article/list.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/new', name: 'post_new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('picture')->getData();

            if ($file) {
                // Si un fichier est téléchargé, gérer le fichier
                $newFilename = uniqid() . '.' . $file->guessExtension();  // Générer un nom unique

                // Déplacer le fichier
                $file->move(
                    $this->getParameter('uploads_directory'),  // Assure-toi d'avoir configuré ce paramètre
                    $newFilename
                );

                // Assigner le nom du fichier à l'entité
                $post->setPicture($newFilename);
            } else {
                // Si aucun fichier n'est téléchargé, ne rien changer, picture restera à null
                $post->setPicture(null);
            }
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }

        return $this->render('article/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'post_edit')]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('post_list');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_list');
    }

    #[Route('/category/{id}', name: 'category_show')]
    public function showCategory(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        }

        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}', name: 'post_show')]
    public function show(Post $post): Response
    {
        return $this->render('article/show.html.twig', [
            'post' => $post,
        ]);
    }
    
    public function index(PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $posts = $postRepository->findAll(); // Récupère tous les articles
        $categories = $categoryRepository->findAll(); // Récupère toutes les catégories

        return $this->render('main/index.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }


}