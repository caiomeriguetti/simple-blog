<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Post;
use AppBundle\Entity\Counter;
use AppBundle\DIContainer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostsController extends Controller {

    private $postService;

    function __construct() {
        $this -> postService = DIContainer::service('posts');
    }

    /**
     * @Route("/posts/csv", name="posts-csv")
     * @Method("GET")
     */
    public function getPostsAsCsv(Request $request) {
        try {
            $csv = $this -> postService -> getCsv($this->getDoctrine(), $request);

            $response = new Response($csv);
            $response -> headers -> set('Pragma', 'public');
            $response -> headers -> set('Expires', '0');
            $response -> headers -> set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response -> headers -> set('Cache-Control', 'private');
            $response -> headers -> set('Content-Type', 'text/csv');
            $response -> headers -> set('Content-Disposition', 'attachment; filename="'.uniqid().'.csv"');
            $response -> headers -> set('Content-Transfer-Encoding', 'binary');
            $response -> headers -> set('Connection', 'close');
            return $response;
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => 'Problem generating csv']);
            $r->setStatusCode(500);
            return $r;
        }
    }

    /**
     * @Route("/views/count", name="viewCount")
     * @Method("GET")
     */
    public function getViewCount() {
        try {
            $count = $this -> postService ->getViewCount($this -> getDoctrine());
            return new JsonResponse($count);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }
    }

    /**
     * @Route("/posts/count", name="postCount")
     * @Method("GET")
     */
    public function getPostCount() {
        try {
            $count = $this -> postService -> getPostCount($this->getDoctrine());
            return new JsonResponse($count);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }
    }

    /**
     * @Route("/posts/counts", name="counts")
     * @Method("GET")
     */
    public function counts(Request $request) {
        try {
            $counts = $this -> postService -> getCounts($this->getDoctrine());
            return new JsonResponse($counts);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }
    }

    /**
     * @Route("/posts", name="root")
     * @Method("GET")
     */
    public function listPosts(Request $request) {
        try {
            $offset = $request -> get('offset');
            $posts = $this -> postService -> getList($this->getDoctrine(), $request, $offset);
            $count = $this -> postService -> hasNextPage($this->getDoctrine(), $offset);

            return new JsonResponse(['posts' => $posts, 'hasNext' => $count]);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }

    }
    
    /**
     * @Route("/posts/{id}")
     * @Method("GET")
     */
    public function getPostData(Request $request) {
        try {
            $id = $request -> get('id');
            $post = $this -> postService -> getPostData($this->getDoctrine(), $id);
            return new JsonResponse($post);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }
    }

    /**
     * @Route("/posts")
     * @Method("POST")
     */
    public function save(Request $request) {
        $uploadedFile = $request -> files -> get('image');

        if (!$uploadedFile) {
            $resp = new JsonResponse([
                'error' => 1, 
                'text' => 'You should send an image']
            );
            return  $resp -> setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $rootDir = $this -> get('kernel') -> getRootDir();
        $title = $request -> get('title');
        if (!$title) {
            $title = '';
        }
        
        $post = new Post();
        $post->setTitle($title);

        try {
            $this -> postService -> save(
                $this->getDoctrine(), $request, 
                $uploadedFile, $rootDir, 
                $post
            );
            return new JsonResponse($post);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }
    }

    /**
     * @Route("/views/increment")
     * @Method("POST")
     */
    public function incrementViews(Request $request) {
        try {
            $this -> postService -> incrementViews($this -> getDoctrine());
            return new JsonResponse(null);
        } catch (\Exception $e) {
            $r = new JsonResponse(['error' => 1, 'text' => $e ->  getMessage()]);
            $r->setStatusCode(500);
            return $r;
        }
    }

    private function getBaseUrl(Request $request) {
        return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
    }
}