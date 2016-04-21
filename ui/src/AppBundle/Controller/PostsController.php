<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Unirest\Request as UniRequest;
use Unirest\Request\Body as UniBody;

class PostsController extends BaseController
{

    /**
     * @Route("/posts/csv", name="posts-csv")
     * @Method("GET")
     */
    public function postsAsCsv(Request $request) {
        $response = UniRequest::get($this -> apiEndpoint . 'posts/csv');
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private',false);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.uniqid().'.csv"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        return new Response($response -> body);
    }

    /**
     * @Route("/posts/image", name="image")
     */
    public function upload(Request $request) {
        
        $destination = $this->get('kernel')->getRootDir() . '/../web/img-posts';
        $name = md5(uniqid());
        $uploadedFile = $request -> files -> get('post-image');
        $extension = strtolower($uploadedFile -> getClientOriginalExtension());

        if (!$uploadedFile) {
            return new JsonResponse([
                'error' => 1,
                'text' => 'You have to upload an image'
            ]);
        }

        if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
            return new JsonResponse([
                'error' => 1,
                'text' => 'Invalid image extension. Should be: jpg, jpeg, gif, png'
            ]);
        }

        $fileName = $name . '.' . $uploadedFile -> getClientOriginalExtension();
        $uploadedFile -> move($destination, $fileName);
        $resp = array(
            'url' => 'img-posts/' . $fileName
        );
        
        return new JsonResponse($resp);
    }

    /**
     * @Route("/posts", name="save-post")
     * @Method("POST")
     */
    public function savePost(Request $request) {
        $title = $request -> get('title');
        $image = $request -> get('image');
        $imagePath = $this->get('kernel')->getRootDir() . '/../web/img-posts/'.$image;

        $data = array('title' => $request -> get('title'));
        $files = array('image' => $imagePath);

        $body = UniBody::multipart($data, $files);

        $response = UniRequest::post($this -> apiEndpoint . 'posts', null, $body);
        return new JsonResponse($response -> body);
    }

    /**
     * @Route("/posts", name="list-posts")
     * @Method("GET")
     */
    public function listPosts(Request $request) {

        $params = array('offset' => $request -> get('offset'));
        $response = UniRequest::get($this -> apiEndpoint . 'posts', null, $params);
        return new JsonResponse($response -> body);
    }

    /**
     * @Route("/posts/counts", name="counts")
     * @Method("GET")
     */
    public function counts(Request $request) {
        $response = UniRequest::get($this -> apiEndpoint . 'posts/counts');
        return new JsonResponse($response -> body);
    }

    /**
     * @Route("/views/increment", name="views-increment")
     * @Method("POST")
     */
    public function incrementViews(Request $request) {
        $response = UniRequest::get($this -> apiEndpoint . 'posts/views/increment');
        return new JsonResponse($response -> body);
    }

}
