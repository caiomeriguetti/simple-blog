<?php
namespace AppBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use WideImage\WideImage;

class PostService {

	public function save($doctrine, $request, $uploadedFile, $rootDir, &$post) {
		$extension = strtolower($uploadedFile -> getClientOriginalExtension());

        if (!in_array($extension, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new \Exception('Invalid format. Should be jpg, jpeg, gif or png');
        }

        $destination = $rootDir . '/../web/img-posts';
        $name = md5(uniqid());
        $fileName = $name . '.' . $extension;
        $fileNameFull = $name . '-full.' . $extension;
        $uploadedFile -> move($destination, $fileNameFull);
        $imgPathFull = $destination . '/' . $fileNameFull;
        $imgPath = $destination . '/' . $fileName;
        $img = WideImage::load($imgPathFull);

        if ($img -> getWidth() > 1920) {
            throw new \Exception('Image width should be <= 1920');
        }

        if ($img -> getHeight() > 1080) {
            throw new \Exception('Image height should be <= 1080');
        }

        if (filesize($imgPathFull)/1000000 > 2) {
            throw new \Exception('Maximum filesize of 2MB exceded');
        }
        
        $width = 400;
        $img = $img -> resize($width);
        $img -> saveToFile($imgPath);
        
        $post->setImage($fileName);
        $post->setCreatedAt(new \DateTime());

        $em = $doctrine->getManager();
        $em->persist($post);
        $em->flush();

        $em = $doctrine->getManager();
        $query = $em->createQuery(
            'UPDATE AppBundle:Counter c
             SET c.value = c.value + 1
             WHERE c.name = :name'
        ) -> setParaMeter('name', 'posts');
        $query -> execute();

        $baseurl = $this->getBaseUrl($request);
        $post->imageUrl = $baseurl . '/img-posts/' . $fileName;
        $post->imageUrlFull = $baseurl . '/img-posts/' . $fileNameFull;
	}

	public function incrementViews($doctrine) {
		$em = $doctrine->getManager();
        $query = $em->createQuery(
            'UPDATE AppBundle:Counter c
             SET c.value = c.value + 1
             WHERE c.name = :name'
        ) -> setParaMeter('name', 'views');
        $query -> execute();
	}

	public function getPostData($em, $id) {
		
        if (!$id) {
            throw new Exception('ID cant be empty');
        }

        $repository = $em -> getRepository('AppBundle:Post');
        $post = $repository -> find($id);
        if (!$post) {
            throw new \Exception('Couldnt find a post with id ' . $id);
        }
        return $post;
	}

	public function getCounts($em) {
		$repository = $em -> getRepository('AppBundle:Counter');

        $qb = $repository -> createQueryBuilder('counter');
        $qb->select('counter.value, counter.name');

        $counts = $qb -> getQuery() -> getResult();
        $response = [];

        foreach ($counts as $count) {
            $response[$count['name']] = $count['value'];
        }
        return $response;
	}

	public function getPostCount($em) {
 		$repository = $em -> getRepository('AppBundle:Counter');

        $qb = $repository -> createQueryBuilder('counter');
        $qb -> select('counter.value, counter.name') 
            -> where('counter.name = :counterName') 
            -> setParameter('counterName', 'posts');

        $count = $qb -> getQuery() -> getOneOrNullResult();
        $response = [];

        $response[$count['name']] = $count['value'];
        return $response;
	}

	public function getViewCount($em) {
		$repository = $em -> getRepository('AppBundle:Counter');

        $qb = $repository -> createQueryBuilder('counter');
        $qb->select('counter.value, counter.name') 
           -> where('counter.name = :counterName') 
           -> setParameter('counterName', 'views');

        $count = $qb->getQuery() -> getOneOrNullResult();
        $response = [];
        $response[$count['name']] = $count['value'];

        return $response;
	}

	public function hasNextPage($em, $offset) {
		$repository = $em -> getRepository('AppBundle:Post');
		$queryCount = $repository -> createQueryBuilder('p')
        -> setMaxResults(1)
        -> setFirstResult($offset+1)
        -> orderBy('p.id', 'DESC')
        -> getQuery();

        $count = count($queryCount->getResult());
        return $count;
	}

	public function getList($em, $request, $offset) {
        $repository = $em -> getRepository('AppBundle:Post');

        if (!$offset) {
            $offset = 0;
        }

        $query = $repository -> createQueryBuilder('p')
            -> setMaxResults(10)
            -> setFirstResult($offset)
            -> orderBy('p.id', 'DESC')
            -> getQuery();

        $posts = $query->getResult();
        $baseurl = $this->getBaseUrl($request);
        
        foreach ($posts as &$post) {
            $image = $post -> getImage();
            $post->imageUrl = $baseurl . '/img-posts/' . $image;
            $post->imageUrlFull = $baseurl . '/img-posts/' . str_replace('.', '-small.', $image);
        }

        return $posts;
	}

	public function getCsv($em, $request) {
		$repository = $em -> getRepository('AppBundle:Post');

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

        return $csv;
	}

	private function getBaseUrl(Request $request) {
      return $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
  }
}