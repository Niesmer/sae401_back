<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Catalogue\Livre;
use App\Entity\Catalogue\Musique;
use App\Entity\Catalogue\Piste;
use App\Entity\Catalogue\Article;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Doctrine\DBAL\Exception\ConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ApiRestController extends AbstractController
{
	private $entityManager;
	private $serializer;
	private $logger;

	public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, SerializerInterface $serializer)
	{
		$this->entityManager = $entityManager;
		$this->logger = $logger;
		$this->serializer = $serializer;
	}

	#[Route('/wp-json/wc/v3/products', name: 'list-all-products', methods: ['GET'])]
	public function listAllProducts(): Response
	{
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a");
		$articles = $query->getArrayResult();
		$response = new Response();
		$response->setStatusCode(Response::HTTP_OK); // 200 https://github.com/symfony/http-foundation/blob/5.4/Response.php
		$response->setContent(json_encode($articles));
		$response->headers->set('Content-Type', 'application/json');
		$response->headers->set('Access-Control-Allow-Origin', '*');
		return $response;
	}

	#[Route('/wp-json/wc/v3/products', name: 'allow-create-a-product', methods: ['OPTIONS'])]
	#[Route('/wp-json/wc/v3/products/{id}', name: 'allow-retrieve-a-product', methods: ['OPTIONS'])]
	public function allowCreateAProduct(Request $request): Response
	{
		$response = new Response();
		$response->setStatusCode(Response::HTTP_OK); // 200 https://github.com/symfony/http-foundation/blob/5.4/Response.php
		$response->headers->set('Access-Control-Allow-Origin', '*');
		$response->headers->set('Access-Control-Allow-Methods', $request->headers->get('Access-Control-Request-Method'));
		$response->headers->set('Access-Control-Allow-Headers', $request->headers->get('Access-Control-Request-Headers'));
		return $response;
	}

	#[Route('/wp-json/wc/v3/products', name: 'create-a-product', methods: ['POST'])]
	public function createAProduct(Request $request): Response
	{
		$article = json_decode($request->getContent(), true);
		if (isset($article["article_type"])) {
			if ($article["article_type"] == "musique") {
				$entity = new Musique();
				$formBuilder = $this->createFormBuilder($entity, array('csrf_protection' => false));
				$formBuilder->add("id", TextType::class);
				$formBuilder->add("titre", TextType::class);
				$formBuilder->add("artiste", TextType::class);
				$formBuilder->add("prix", NumberType::class);
				$formBuilder->add("disponibilite", IntegerType::class);
				$formBuilder->add("image", TextType::class);
				$formBuilder->add("dateDeParution", TextType::class);
				// Generate form
				$form = $formBuilder->getForm();
				$form->submit($article);
				if ($form->isSubmitted()) {
					try {
						$entity = $form->getData();
						$id = hexdec(uniqid()); // $id must be of type int
						$entity->setId($id);
						$this->entityManager->persist($entity);
						$this->entityManager->flush();
						$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a where a.id like :id");
						$query->setParameter("id", $id);
						$article = $query->getArrayResult();
						$response = new Response();
						$response->setStatusCode(Response::HTTP_CREATED); // 201 https://github.com/symfony/http-foundation/blob/5.4/Response.php
						$response->setContent(json_encode($article));
						$response->headers->set('Content-Type', 'application/json');
						$response->headers->set('Content-Location', '/wp-json/wc/v3/products/' . $id);
						$response->headers->set('Access-Control-Allow-Origin', '*');
						$response->headers->set('Access-Control-Allow-Headers', '*');
						$response->headers->set('Access-Control-Expose-Headers', 'Content-Location');

						return $response;
					} catch (ConstraintViolationException $e) {
						$errors = $form->getErrors();
						$response = new Response();
						$response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY); // 422 https://github.com/symfony/http-foundation/blob/5.4/Response.php
						$response->setContent(json_encode(array('message' => 'Invalid input', 'errors' => 'Constraint violation')));
						$response->headers->set('Content-Type', 'application/json');
						$response->headers->set('Access-Control-Allow-Origin', '*');
						$response->headers->set('Access-Control-Allow-Headers', '*');
						return $response;
					}
				} else {
					$errors = $form->getErrors();
					$response = new Response();
					$response->setStatusCode(Response::HTTP_BAD_REQUEST); // 400 https://github.com/symfony/http-foundation/blob/5.4/Response.php
					$response->setContent(json_encode(array('message' => 'Invalid input', 'errors' => $errors->__toString())));
					//$response->setContent(json_encode(array('message' => 'Invalid input'))) ;
					$response->headers->set('Content-Type', 'application/json');
					$response->headers->set('Access-Control-Allow-Origin', '*');
					$response->headers->set('Access-Control-Allow-Headers', '*');
					return $response;
				}
			}
			if ($article["article_type"] == "livre") {
				$entity = new Livre();
				$formBuilder = $this->createFormBuilder($entity, array('csrf_protection' => false));
				$formBuilder->add("id", TextType::class);
				$formBuilder->add("titre", TextType::class);
				$formBuilder->add("auteur", TextType::class);
				$formBuilder->add("prix", NumberType::class);
				$formBuilder->add("disponibilite", IntegerType::class);
				$formBuilder->add("image", TextType::class);
				$formBuilder->add("ISBN", TextType::class, ['required' => true]);
				$formBuilder->add("nbPages", IntegerType::class);
				$formBuilder->add("dateDeParution", TextType::class);
				// Generate form
				$form = $formBuilder->getForm();
				$form->submit($article);
				if ($form->isSubmitted()) {
					try {
						$entity = $form->getData();
						$id = hexdec(uniqid()); // $id must be of type int
						$entity->setId($id);
						$this->entityManager->persist($entity);
						$this->entityManager->flush();
						$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a where a.id like :id");
						$query->setParameter("id", $id);
						$article = $query->getArrayResult();
						$response = new Response();
						$response->setStatusCode(Response::HTTP_CREATED); // 201 https://github.com/symfony/http-foundation/blob/5.4/Response.php
						$response->setContent(json_encode($article));
						$response->headers->set('Content-Type', 'application/json');
						$response->headers->set('Content-Location', '/wp-json/wc/v3/products/' . $id);
						$response->headers->set('Access-Control-Allow-Origin', '*');
						$response->headers->set('Access-Control-Allow-Headers', '*');
						$response->headers->set('Access-Control-Expose-Headers', 'Content-Location');
						return $response;
					} catch (ConstraintViolationException $e) {
						$errors = $form->getErrors();
						$response = new Response();
						$response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY); // 422 https://github.com/symfony/http-foundation/blob/5.4/Response.php
						$response->setContent(json_encode(array('message' => 'Invalid input', 'errors' => 'Constraint violation')));
						$response->headers->set('Content-Type', 'application/json');
						$response->headers->set('Access-Control-Allow-Origin', '*');
						$response->headers->set('Access-Control-Allow-Headers', '*');
						return $response;
					}
				} else {
					$errors = $form->getErrors();
					$response = new Response();
					$response->setStatusCode(Response::HTTP_BAD_REQUEST); // 400 https://github.com/symfony/http-foundation/blob/5.4/Response.php
					$response->setContent(json_encode(array('message' => 'Invalid input', 'errors' => $errors->__toString())));
					$response->headers->set('Content-Type', 'application/json');
					$response->headers->set('Access-Control-Allow-Origin', '*');
					$response->headers->set('Access-Control-Allow-Headers', '*');
					return $response;
				}
			}
		} else {
			$response = new Response();
			$response->setStatusCode(Response::HTTP_BAD_REQUEST); // 400 https://github.com/symfony/http-foundation/blob/5.4/Response.php
			$response->setContent(json_encode(array('message' => 'Invalid input: No article_type found')));
			$response->headers->set('Content-Type', 'application/json');
			$response->headers->set('Access-Control-Allow-Origin', '*');
			$response->headers->set('Access-Control-Allow-Headers', '*');
			return $response;
		}
	}

	#[Route('/wp-json/wc/v3/products/{id}', name: 'retrieve-a-product', methods: ['GET'])]
	public function retrieveAProduct(string $id): Response
	{
		// http://127.0.0.1:8000/wp-json/wc/v3/products/B07KBT4ZRG
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a where a.id like :id");
		$query->setParameter("id", $id);
		$article = $query->getArrayResult();
		if (count($article) !== 0) {
			$response = new Response();
			$response->setStatusCode(Response::HTTP_OK); // 200 https://github.com/symfony/http-foundation/blob/5.4/Response.php
			$response->setContent(json_encode($article));
			$response->headers->set('Content-Type', 'application/json');
			$response->headers->set('Access-Control-Allow-Origin', '*');
			return $response;
		} else {
			$response = new Response();
			$response->setStatusCode(Response::HTTP_NOT_FOUND); // 404 https://github.com/symfony/http-foundation/blob/5.4/Response.php
			$response->setContent(json_encode(array('message' => 'Resource not found: No article found for id ' . $id)));
			$response->headers->set('Content-Type', 'application/json');
			$response->headers->set('Access-Control-Allow-Origin', '*');
			return $response;
		}
	}

	#[Route('/wp-json/wc/v3/products/{id}', name: 'replace-a-product', methods: ['PUT'])]
	public function replaceAProduct(string $id, Request $request): Response
	{
		$data = json_decode($request->getContent(), true);
		$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a where a.id like :id");
		$query->setParameter("id", $id);
		$articles = $query->getArrayResult();
		if (isset($articles)) {
			$article = $articles[0];
			if ($article["article_type"] == "musique") {
				$formBuilder = $this->createFormBuilder($article, array('csrf_protection' => false));
				$formBuilder->add("id", IntegerType::class);
				$formBuilder->add("article_type", TextType::class, ['empty_data' => '']);
				$formBuilder->add("titre", TextType::class, ['empty_data' => '']);
				$formBuilder->add("artiste", TextType::class, ['empty_data' => '']);
				$formBuilder->add("prix", NumberType::class, ['empty_data' => 0]);
				$formBuilder->add("disponibilite", IntegerType::class, ['empty_data' => 0]);
				$formBuilder->add("image", TextType::class, ['empty_data' => '']);
				$formBuilder->add("dateDeParution", TextType::class, ['empty_data' => '']);
				$form = $formBuilder->getForm();
				$updatedArticleData = array_merge($article, array_filter($data, function ($value) {
					return $value !== null;
				}));
				$form->submit(
					$updatedArticleData
				);
				if ($form->isSubmitted()) {
					$article = $form->getData();
					$entity = $this->entityManager->getRepository(Article::class)->find($id);;
					$this->serializer->deserialize(json_encode($article), Musique::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $entity]);
					$this->entityManager->persist($entity);
					$this->entityManager->flush();
					$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a where a.id like :id");
					$query->setParameter("id", $id);
					$article = $query->getArrayResult();
					$response = new Response();
					$response->setStatusCode(Response::HTTP_OK); // 200
					$response->setContent(json_encode($article));
					$response->headers->set('Content-Type', 'application/json');
					$response->headers->set('Content-Location', '/wp-json/wc/v3/products/' . $id);
					$response->headers->set('Access-Control-Allow-Origin', '*');
					return $response;
				}
			}
			if ($article["article_type"] == "livre") {
				$formBuilder = $this->createFormBuilder($article, array('csrf_protection' => false));
				$formBuilder->add("id", IntegerType::class);
				$formBuilder->add("article_type", TextType::class, ['empty_data' => '']);
				$formBuilder->add("titre", TextType::class, ['empty_data' => '']);
				$formBuilder->add("auteur", TextType::class, ['empty_data' => '']);
				$formBuilder->add("prix", NumberType::class, ['empty_data' => 0]);
				$formBuilder->add("disponibilite", IntegerType::class, ['empty_data' => 0]);
				$formBuilder->add("image", TextType::class, ['empty_data' => '']);
				$formBuilder->add("ISBN", TextType::class, ['empty_data' => '']);
				$formBuilder->add("nbPages", IntegerType::class, ['empty_data' => 0]);
				$formBuilder->add("dateDeParution", TextType::class, ['empty_data' => '']);
				$form = $formBuilder->getForm();
				$updatedArticleData = array_merge($article, array_filter($data, function ($value) {
					return $value !== null;
				}));
				$form->submit(
					$updatedArticleData
				);
				if ($form->isSubmitted()) {
					$article = $form->getData();
					$entity = $this->entityManager->getRepository(Article::class)->find($id);;
					$this->serializer->deserialize(json_encode($article), Livre::class, 'json', [AbstractObjectNormalizer::OBJECT_TO_POPULATE => $entity]);
					$this->entityManager->persist($entity);
					$this->entityManager->flush();
					$query = $this->entityManager->createQuery("SELECT a FROM App\Entity\Catalogue\Article a where a.id like :id");
					$query->setParameter("id", $id);
					$article = $query->getArrayResult();
					$response = new Response();
					$response->setStatusCode(Response::HTTP_OK); // 200
					$response->setContent(json_encode($article));
					$response->headers->set('Content-Type', 'application/json');
					$response->headers->set('Content-Location', '/wp-json/wc/v3/products/' . $id);
					$response->headers->set('Access-Control-Allow-Origin', '*');
					return $response;
				} else {
				}
			}
		} else {
			$response = new Response();
			$response->setStatusCode(Response::HTTP_NOT_FOUND); // 404
			$response->setContent(json_encode(array('message' => 'Resource not found: No article found for id ' . $id)));
			$response->headers->set('Content-Type', 'application/json');
			$response->headers->set('Access-Control-Allow-Origin', '*');
			return $response;
		}
	}
}
