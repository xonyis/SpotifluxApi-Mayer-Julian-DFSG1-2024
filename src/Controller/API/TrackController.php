<?php

namespace App\Controller\API;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Movie;
use App\Entity\Track;
use App\Form\ArtistType;
use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\MovieRepository;
use App\Repository\TrackRepository;
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
class TrackController extends AbstractController
{

    public function __construct(
        private TrackRepository $trackRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,

    )
    {
        // ...
    }

    #[Route('/track', name: 'app_api_track', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Track::class, groups: ['read']))
        )
    )]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $track = $this->trackRepository->findAll();

        $data = $paginator->paginate(
            $track,
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

    #[Route('/track/{id}', name: 'app_api_track_id', methods: ['GET'])]
    public function get( int $id): JsonResponse
    {
        $track = $this->trackRepository->find($id);


        if (!$track) {
            return $this->json([
                'error' => 'Track not found',
            ], 404);
        }

        return $this->json([
            'track' => $track,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/track', name: 'app_api_track_add', methods: ['POST'])]
    public function add(#[MapRequestPayload('json')] Track $track): JsonResponse
    {
        $this->em->persist($track);
        $this->em->flush();

        return $this->json($track, 200,[],[
            'groups' => ['read']
        ]);
    }

    #[Route('/track/{id}', name: 'app_api_track_update',  methods: ['PUT'])]
    public function update(Track $track, Request $request): JsonResponse
    {

        $data = $request->getContent();
        $this->serializer->deserialize($data, Track::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $track,
            'groups' => ['update']
        ]);

        $this->em->flush();

        return $this->json($track, 200, [], [
            'groups' => ['read'],
        ]);
    }

    #[Route('/track/{id}', name: 'app_api_track_delete', methods: ['DELETE'])]
    public function delete(Track $track = null): JsonResponse
    {
        if (!$track) {
            return $this->json(['error' => 'Track non trouvé.'], 404);
        }

        $this->em->remove($track);
        $this->em->flush();

        return $this->json(['message' => 'Track supprimé avec succès.']);
    }
}
