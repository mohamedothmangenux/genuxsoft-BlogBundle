<?php

namespace Acme\BlogBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\BlogFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends Controller
{
    /**
     *
     * @Route("list", name="acme_blog_list")
     */
    public function indexAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $posts = $entityManager->getRepository('AppBundle:Post')
            ->findAllPublished();

        return $this->render('@AcmeBlog/blog/list.html.twig', [
            'posts' => $posts,
        ]);
    }
    /**
     * @Route("new", name="acme_blog_new")
     */
    public function newAction(Request $request)
    {
        $form = $this->createForm(BlogFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post->setUpdated();
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created!');
            return $this->redirectToRoute('acme_blog_list');
        }

        return $this->render('@AcmeBlog/blog/new.html.twig', [
            'blogform' => $form->createView()
        ]);
    }
    /**
     * @Route("view/{id}", name="acme_blog_show")
     */
    public function showAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository('AppBundle:Post')
            ->findOneById($id);
        if (!$post) {
            throw $this->createNotFoundException('No Found Post');
        }
        $comments = $entityManager->getRepository('AppBundle:Comment')
            ->findAllRecentCommentsForPost($post);

        return $this->render('@AcmeBlog/blog/post.html.twig', [
            'post' => $post,
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("comments/{id}", name="acme_blog_comments")
     */
    public function getCommentsAction(Post $post)
    {
        foreach ($post->getComments() as $comment) {
            $comments[] = [
                'id' => $comment->getId(),
                'title' => $comment->getTitle(),
                'comment' => $comment->getComment(),
                'email' => $comment->getEmail(),
                'created_at' => $comment->getCreated_at(),
            ];
        }
        $data = [
            'comment' => $comments,
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/edit/{id}", name="acme_blog_edit")
     */
    public function editAction(Request $request, Post $post)
    {
        $form = $this->createForm(BlogFormType::class , $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post->setUpdated();
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post Updated!');
            return $this->redirectToRoute('acme_blog_list');
        }

        return $this->render('@AcmeBlog/blog/edit.html.twig', [
            'blogform' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="acme_blog_delete")
     */
    public function deleteAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post Deleted!');
        return $this->redirectToRoute('acme_blog_list');

    }
}
