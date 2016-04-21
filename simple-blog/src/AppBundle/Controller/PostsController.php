<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Post;
use AppBundle\Entity\Counter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use WideImage\WideImage;

class PostsController extends Controller {

    /**
     * @Route("/posts/csv", name="posts-csv")
     * @Method("GET")
     */
    public function getPostsAsCsv(Request $request) {
        $repository = $this -> getDoctrine() -> getRepository('AppBundle:Post');

        $qb = $repository -> createQueryBuilder('posts');
        $qb -> select('posts.title, posts.image') -> orderBy('posts.id', 'DESC');

        $posts = $qb->getQuery() -> getResult();
        
        $fd = fopen('php://temp/maxmemory:1048576', 'w');
        if($fd === FALSE) {
            throw new Exception('Failed to open temporary file');
        }

        $headers = array('title', 'image');
        $baseurl = $this -> getBaseUrl($request);

        fputcsv($fd, $headers);
        foreach($posts as &$post) {
            $post['imageUrl'] = $baseurl . '/img-posts/' . $post['image'];
            fputcsv($fd, [$post['title'], $post['imageUrl']]);
        }

        rewind($fd);
        $csv = stream_get_contents($fd);
        fclose($fd);

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
    }

    /**
     * @Route("/views/count", name="viewCount")
     * @Method("GET")
     */
    public function getViewCount() {
        $repository = $this -> getDoctrine() -> getRepository('AppBundle:Counter');

        $qb = $repository -> createQueryBuilder('counter');
        $qb->select('counter.value, counter.name') 
           -> where('counter.name = :counterName') 
           -> setParameter('counterName', 'views');

        $count = $qb->getQuery() -> getOneOrNullResult();
        $response = [];
        $response[$count['name']] = $count['value'];

        return new JsonResponse($response);
    }

    /**
     * @Route("/posts/count", name="postCount")
     * @Method("GET")
     */
    public function getPostCount() {
        $repository = $this -> getDoctrine() -> getRepository('AppBundle:Counter');

        $qb = $repository -> createQueryBuilder('counter');
        $qb -> select('counter.value, counter.name') 
            -> where('counter.name = :counterName') 
            -> setParameter('counterName', 'posts');

        $count = $qb -> getQuery() -> getOneOrNullResult();
        $response = [];

        $response[$count['name']] = $count['value'];

        return new JsonResponse($response);
    }

    /**
     * @Route("/posts/counts", name="counts")
     * @Method("GET")
     */
    public function counts(Request $request) {
        $repository = $this -> getDoctrine() -> getRepository('AppBundle:Counter');

        $qb = $repository -> createQueryBuilder('counter');
        $qb->select('counter.value, counter.name');

        $counts = $qb -> getQuery() -> getResult();
        $response = [];

        foreach ($counts as $count) {
            $response[$count['name']] = $count['value'];
        }
        return new JsonResponse($response);
    }

    /**
     * @Route("/posts", name="root")
     * @Method("GET")
     */
    public function listPosts(Request $request) {
        $repository = $this -> getDoctrine() -> getRepository('AppBundle:Post');

        $offset = $request -> get('offset');
        if (!$offset) {
            $offset = 0;
        }

        $query = $repository -> createQueryBuilder('p')
            -> setMaxResults(10)
            -> setFirstResult($offset)
            -> orderBy('p.id', 'DESC')
            -> getQuery();

        $queryCount = $repository -> createQueryBuilder('p')
            -> setMaxResults(1)
            -> setFirstResult($offset+1)
            -> orderBy('p.id', 'DESC')
            -> getQuery();

        $count = count($queryCount->getResult());

        $posts = $query->getResult();

        $baseurl = $this->getBaseUrl($request);
        
        foreach ($posts as &$post) {
            $image = $post -> getImage();
            $post->imageUrl = $baseurl . '/img-posts/' . $image;
            $post->imageUrlFull = $baseurl . '/img-posts/' . str_replace('.', '-small.', $image);
        }

        return new JsonResponse(['posts' => $posts, 'hasNext' => $count]);
    }
    
    /**
     * @Route("/posts/{id}")
     * @Method("GET")
     */
    public function getPostData(Request $request) {
        try {
            $id = $request -> get('id');

            if (!$id) {
                return new JsonResponse(['error' => 1, 'text' => 'ID cant be empty']);
            }

            $repository = $this -> getDoctrine() -> getRepository('AppBundle:Post');
            $post = $repository -> find($id);
            if (!$post) {
                return new JsonResponse(['error' => 1, 'text' => 'Couldnt find a post with id ' . $id]);
            }

            return new JsonResponse($post);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 1, 'text' => 'Couldnt retrieve post data']);
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

        $extension = strtolower($uploadedFile -> getClientOriginalExtension());

        if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
            return new JsonResponse(['error' => 1, 'text' => 'Invalid format. Should be jpg, jpeg, gif or png']);
        }

        $destination = $this -> get('kernel') -> getRootDir() . '/../web/img-posts';
        $name = md5(uniqid());
        $fileName = $name . '.' . $extension;
        $fileNameFull = $name . '-full.' . $extension;
        $uploadedFile -> move($destination, $fileNameFull);
        $imgPathFull = $destination . '/' . $fileNameFull;
        $imgPath = $destination . '/' . $fileName;
        $img = WideImage::load($imgPathFull);

        if ($img -> getWidth() > 1920) {
            return new JsonResponse(['error' => 1, 'text' => 'Image width should be <= 1920']);
        }

        if ($img -> getHeight() > 1080) {
            return new JsonResponse(['error' => 1, 'text' => 'Image height should be <= 1080']);
        }

        if (filesize($imgPathFull)/1000000 > 2) {
            return new JsonResponse(['error' => 1, 'text' => 'Maximum filesize of 2MB exceded']);
        }
        
        $width = 400;
        $img = $img -> resize($width);
        $img -> saveToFile($imgPath);
        $title = $request -> get('title');
        if (!$title) {
            $title = '';
        }
        $post = new Post();
        $post->setTitle($title);
        $post->setImage($fileName);
        $post->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'UPDATE AppBundle:Counter c
             SET c.value = c.value + 1
             WHERE c.name = :name'
        ) -> setParaMeter('name', 'posts');
        $query -> execute();

        $baseurl = $this->getBaseUrl($request);
        $post->imageUrl = $baseurl . '/img-posts/' . $fileName;
        $post->imageUrlFull = $baseurl . '/img-posts/' . $fileNameFull;

        return new JsonResponse($post);
    }

    /**
     * @Route("/views/increment")
     * @Method("POST")
     */
    public function incrementViews(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'UPDATE AppBundle:Counter c
             SET c.value = c.value + 1
             WHERE c.name = :name'
        ) -> setParaMeter('name', 'views');
        $query -> execute();

        return new JsonResponse(null);
    }

    private function getBaseUrl(Request $request) {
        return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
    }
}