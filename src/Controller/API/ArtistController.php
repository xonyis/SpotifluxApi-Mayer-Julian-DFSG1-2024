<?php

namespace App\Controller\API;

use App\Entity\Artist;
use App\Entity\Movie;
use App\Form\ArtistType;
use App\Repository\ArtistRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class ArtistController extends AbstractController
{

    public function __construct(
        private ArtistRepository $artistRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,

    )
    {
        // ...
    }

    #[Route('/artist', name: 'app_api_artist', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Artist::class, groups: ['read']))
        )
    )]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $artist = $this->artistRepository->findAll();

        $data = $paginator->paginate(
            $artist,
            $request->query->get('page', 1),
            5
        );

        return $this->json([
            'data' => $data,
            'currentPageNumber' => $data->getCurrentPageNumber()
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/artist/{id}', name: 'app_api_artist_id', methods: ['GET'])]
    public function get( int $id): JsonResponse
    {
        $artist = $this->artistRepository->find($id);


        if (!$artist) {
            return $this->json([
                'error' => 'Artist not found',
            ], 404);
        }

        return $this->json([
            'artist' => $artist,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/artist', name: 'app_api_artist_add', methods: ['POST'])]
    public function add(#[MapRequestPayload('json')] Artist $artist): JsonResponse
    {
        $this->em->persist($artist);
        $this->em->flush();

        return $this->json($artist, 200,[],[
            'groups' => ['read']
        ]);
    }

    #[Route('/artist/{id}', name: 'app_api_artist_update',  methods: ['PUT'])]
    public function update(Artist $artist, Request $request): JsonResponse
    {

        $data = $request->getContent();
        $this->serializer->deserialize($data, Artist::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $artist,
            'groups' => ['update']
        ]);

        $this->em->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read'],
        ]);
    }

    #[Route('/artist/{id}', name: 'app_api_artist_delete', methods: ['DELETE'])]
    public function delete(Artist $artist = null): JsonResponse
    {
        if (!$artist) {
            return $this->json(['error' => 'Artist non trouvé.'], 404);
        }

        $this->em->remove($artist);
        $this->em->flush();

        return $this->json(['message' => 'Arstist supprimé avec succès.']);
    }
}
