<?php

namespace App\Controller\Admin\Post;

use App\Entity\Post;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/admin/post', name: 'admin.post.index')]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('pages/admin/post/index.html.twig', [
            "posts" => $postRepository->findAll()
        ]);
    }


    #[Route('/admin/post/create', name: 'admin.post.create')]
    public function create(
        Request $request, 
        PostRepository $postRepository, 
        CategoryRepository $categoryRepository
    ): Response
    {

        if( ! $categoryRepository->findAll() )
        {
            $this->addFlash('warning', "Vous devez créer au moins une catégorie avant de rédiger des articles.");
            return $this->redirectToRoute('admin.category.index');
        }

        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $post->setUser($this->getUser());
            $postRepository->save($post, true);
            $this->addFlash('success', "Votre article a été créé avec succès.");
            return $this->redirectToRoute('admin.post.index');
        }

        return $this->render('pages/admin/post/create.html.twig', [
            "form" => $form->createView()
        ]);
    }


    #[Route('/admin/post/{id<\d+>}/published', name: 'admin.post.published', methods: ['POST'])]
    public function published(Post $post, PostRepository $postRepository)
    {
        if ( $post->isIsPublished() === false ) 
        {
            $post->setIsPublished(true);
            $post->setPublishedAt(new \DateTimeImmutable('now'));
            $postRepository->save($post, true);
            $this->addFlash("success", $post->getTitle() . " a été publié!");
        }
        else
        {
            $post->setIsPublished(false);
            $post->setPublishedAt(null);
            $postRepository->save($post, true);
            $this->addFlash("success", $post->getTitle() . " a été retiré de la liste des publications!");
        }

        return $this->redirectToRoute('admin.post.index');
    }


    #[Route('/admin/post/{id<\d+>}/show', name: 'admin.post.show', methods: ['GET'])]
    public function show(Post $post) : Response
    {
        return $this->render("pages/admin/post/show.html.twig", compact('post'));
    }


    #[Route('/admin/post/{id<\d+>}/edit', name: 'admin.post.edit', methods: ['GET', 'POST'])]
    public function edit(
        Post $post, 
        Request $request, 
        PostRepository $postRepository,
        CategoryRepository $categoryRepository
    ) : Response
    {

        if( ! $categoryRepository->findAll() )
        {
            $this->addFlash('warning', "Vous devez créer au moins une catégorie avant de rédiger des articles.");
            return $this->redirectToRoute('admin.category.index');
        }

        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) 
        {
            $post->setUser($this->getUser());
            $postRepository->save($post, true);
            $this->addFlash('success', $post->getTitle() . " a été modifié!");
            return $this->redirectToRoute("admin.post.index");
        }

        return $this->render("pages/admin/post/edit.html.twig", [
            "form" => $form->createView(),
            "post" => $post
        ]);
    }


    #[Route('/admin/post/{id<\d+>}/delete', name: 'admin.post.delete', methods: ['POST'])]
    public function delete(Post $post, Request $request, PostRepository $postRepository) : Response
    {
        if ( $this->isCsrfTokenValid('post_' . $post->getId(), $request->request->get('_csrf_token')) ) 
        {
            $postRepository->remove($post, true);
            $this->addFlash('success', "L'article " . $post->getTitle() ." a été supprimé.");
        }

        return $this->redirectToRoute('admin.post.index');
    }


}
