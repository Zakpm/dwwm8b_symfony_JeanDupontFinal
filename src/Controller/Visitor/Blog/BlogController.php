<?php

namespace App\Controller\Visitor\Blog;

use App\Entity\Tag;
use App\Entity\Post;
use App\Entity\Category;
use App\Repository\TagRepository;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    #[Route('/blog/all-posts', name: 'visitor.blog.index')]
    public function index(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository,
        PaginatorInterface $paginator,
        Request $request,
        ): Response
    {
        
     
        
        $categories = $categoryRepository->findAll();
        $tags = $tagRepository->findAll();
        $posts = $postRepository->findBy(['isPublished' => true]); 
        
        $posts_paginated = $paginator->paginate(
            $posts, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );


        return $this->render('pages/visitor/blog/index.html.twig', compact('categories', 'tags', 'posts_paginated'));
    }
    
    
    #[Route('/blog/post/{id<\d+>}/{slug}', name: 'visitor.blog.post.show')]
    public function show(Post $post) : Response
    {
        return $this->render('pages/visitor/blog/show.html.twig', compact('post'));
    }
    
    
    #[Route('/blog/posts/filter_by_category/{id<\d+>}/{slug}', name: 'visitor.blog.posts.filter_by_category')]
    public function filterByCategory(
        Category $category,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository,
        PaginatorInterface $paginator,
        Request $request
        ) : Response
        {
            $categories = $categoryRepository->findAll();
            $tags       = $tagRepository->findAll();
            $posts      = $postRepository->filterPostsByCategory($category->getId());
            
            $posts_paginated = $paginator->paginate(
                $posts, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                6 /*limit per page*/
            );
            
            return $this->render('pages/visitor/blog/index.html.twig', compact('categories', 'tags', 'posts_paginated'));
            
        }
        
    #[Route('/blog/posts/filter_by_tag/{id<\d+>}/{slug}', name: 'visitor.blog.posts.filter_by_tag')]
    public function filterByTag(
        Tag $tag,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PostRepository $postRepository,
        PaginatorInterface $paginator,
        Request $request,
        ) : Response 
    {

        $categories = $categoryRepository->findAll();
        $tags       = $tagRepository->findall();
        $posts      = $postRepository->filterPostsByTag($tag);

        $posts_paginated = $paginator->paginate(
            $posts, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 /*limit per page*/
        );

        return $this->render("pages/visitor/blog/index.html.twig", compact('categories', 'tags', 'posts_paginated'));
    }




}
