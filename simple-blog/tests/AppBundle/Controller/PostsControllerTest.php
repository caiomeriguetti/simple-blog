<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WideImage\WideImage;

class PostsControllerTest extends WebTestCase {

    public function testGetViewCountAndPostCount() {
        $client = static::createClient();

        //getting current counts
        $client -> request('GET', '/views/count');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $viewsCount = $data -> views;

        $client -> request('GET', '/posts/count');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $postsCount = $data -> posts;

        //creating a post, and therefore incrementing the post count
        $img = $this -> getTestImage();
        $img -> saveToFile('tmp.jpg');
        $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        
        $title = 'Test title';
        $client -> request('POST', '/posts', 
            ['title' => $title], 
            ['image' => $uploadedFile]
        );
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());

        //incrementing views count
        $client -> request('POST', '/views/increment');

        //retrieving the counts
        $client -> request('GET', '/views/count');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $newViewsCount = $data -> views;

        $client -> request('GET', '/posts/count');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $newPostsCount = $data -> posts;

        //checking the counts
        $this -> assertEquals($postsCount + 1, $newPostsCount);
        $this -> assertEquals($viewsCount + 1, $newViewsCount);
    }

    public function testListPosts() {
        $client = static::createClient();

        //creating a post to ensure a not-empty list
        $img = $this -> getTestImage();
        $img -> saveToFile('tmp.jpg');
        $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        
        $title = 'Test title';
        $client -> request('POST', '/posts', 
            ['title' => $title], 
            ['image' => $uploadedFile]
        );
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $id = $data -> id;

        //listing posts
        $client -> request('GET', '/posts');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent(), true);
        
        $this -> assertTrue(count($data['posts']) >= 1, 'Post list cant be empty');
        $this -> assertEquals($data['posts'][0]['title'], $title, 'Post list cant be empty');
        $this -> assertEquals($data['posts'][0]['id'], $id, 'First post id should be '.$id);
    }

    public function testCsvDownload() {
        $client = static::createClient();

        //creating a post to ensure a not-empty csv
        $img = $this -> getTestImage();
        $img -> saveToFile('tmp.jpg');
        $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        
        $title = 'Test title';
        $client -> request('POST', '/posts', 
            ['title' => $title], 
            ['image' => $uploadedFile]
        );
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $title = $data -> title;
        $image = $data -> image;

        //getting current counts
        $client -> request('GET', '/posts/csv');
        $csvContent = $client -> getResponse() -> getContent();

        $lines = explode(PHP_EOL, $csvContent);
        $array = array();
        foreach ($lines as $line) {
            $array[] = str_getcsv($line);
        }
        $this->assertEquals('title', $array[0][0]);
        $this->assertEquals('image', $array[0][1]);

        $this->assertEquals($title, $array[1][0]);
        
        $this->assertFalse(strpos($array[1][1], $image) === false, 'Wrong image in csv');
    }

    public function testCounters() {
        $client = static::createClient();

        //getting current counts
        $client -> request('GET', '/posts/counts');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $postsCount = $data -> posts;
        $viewsCount = $data -> views;

        //creating a post, and therefore incrementing the post count
        $img = $this -> getTestImage();
        $img -> saveToFile('tmp.jpg');
        $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        
        $title = 'Test title';
        $client -> request('POST', '/posts', 
            ['title' => $title], 
            ['image' => $uploadedFile]
        );
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $id = $data -> id;

        //incrementing views count
        $client -> request('POST', '/views/increment');

        //retrieving the counts
        $client -> request('GET', '/posts/counts');
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());

        //checking the counts
        $this -> assertEquals($postsCount + 1, $data -> posts);
        $this -> assertEquals($viewsCount + 1, $data -> views);
    }

    public function testGetPostData() {
        //creating a post
        $img = $this -> getTestImage();
        $img -> saveToFile('tmp.jpg');
        $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        $client = static::createClient();
        $title = 'Test title';
        $client -> request('POST', '/posts', 
            ['title' => $title], 
            ['image' => $uploadedFile]
        );
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $id = $data -> id;

        //retrieving the new post
        $client -> request('GET', '/posts/' . $id);
        $response = $client -> getResponse();
        $data = json_decode($response -> getContent());
        $this -> assertEquals($title, $data -> title);
    }

    public function testSavePost() {
        //happy path
        $img = $this -> getTestImage();
        $img -> saveToFile('tmp.jpg');
	    $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        $client = static::createClient();
        $client -> request('POST', '/posts', 
        	['title' => 'Test title'], 
        	['image' => $uploadedFile]
        );

        $response = $client -> getResponse();

        $this->assertEquals(200, $response -> getStatusCode());

        //without image
        $client -> request('POST', '/posts',
            ['title' => 'Test title']
        );
        $response = $client -> getResponse();
        $this->assertEquals(400, $response -> getStatusCode());

        //without title
        $img -> saveToFile('tmp.jpg');
        $uploadedFile = new UploadedFile('tmp.jpg', 'tmp.jpg');
        $client -> request('POST', '/posts', 
            [],
            ['image' => $uploadedFile]
        );
        $response = $client -> getResponse();
        $content = json_decode($response -> getContent());

        $this->assertEquals(200, $response -> getStatusCode());
        $this->assertTrue(array_key_exists('id', $content), 'Response with no id');
        $this->assertTrue(array_key_exists('title', $content), 'Response with no title');
        $this->assertTrue(array_key_exists('imageUrl', $content), 'Response with no imageUrl');
        $this->assertTrue(array_key_exists('imageUrlFull', $content), 'Response with no imageUrlFull');
        $this->assertTrue(array_key_exists('created_at', $content), 'Response with no created_at');

        //no data
        $client -> request('POST', '/posts');
        $response = $client -> getResponse();
        $content = json_decode($response -> getContent());
        $this->assertEquals($content -> error, 1);
        $this->assertEquals($response -> getStatusCode(), 400);
    }

    private function getTestImage() {
        $imageData = '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';
        $img = WideImage::loadFromString(base64_decode($imageData));
        return $img;
    }
}