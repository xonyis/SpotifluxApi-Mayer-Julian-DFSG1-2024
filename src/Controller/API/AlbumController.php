<?php

namespace App\Controller\API;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Movie;
use App\Form\ArtistType;
use App\Repository\AlbumRepository;
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
class AlbumController extends AbstractController
{

    public function __construct(
        private AlbumRepository $albumRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,

    )
    {
        // ...
    }

    #[Route('/album', name: 'app_api_album', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Album::class, groups: ['read']))
        )
    )]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $album = $this->albumRepository->findAll();

        $data = $paginator->paginate(
            $album,
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

    #[Route('/album/{id}', name: 'app_api_album_id', methods: ['GET'])]
    public function get( int $id): JsonResponse
    {
        $album = $this->albumRepository->find($id);


        if (!$album) {
            return $this->json([
                'error' => 'Album not found',
            ], 404);
        }

        return $this->json([
            'artist' => $album,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/album', name: 'app_api_album_add', methods: ['POST'])]
    public function add(#[MapRequestPayload('json')] Album $album): JsonResponse
    {
        $this->em->persist($album);
        $this->em->flush();

        return $this->json($album, 200,[],[
            'groups' => ['read']
        ]);
    }

    #[Route('/album/{id}', name: 'app_api_album_update',  methods: ['PUT'])]
    public function update(Album $album, Request $request): JsonResponse
    {

        $data = $request->getContent();
        $this->serializer->deserialize($data, Album::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $album,
            'groups' => ['update']
        ]);

        $this->em->flush();

        return $this->json($album, 200, [], [
            'groups' => ['read'],
        ]);
    }

    #[Route('/album/{id}', name: 'app_api_album_delete', methods: ['DELETE'])]
    public function delete(Album $album = null): JsonResponse
    {
        if (!$album) {
            return $this->json(['error' => 'Album non trouvé.'], 404);
        }

        $this->em->remove($album);
        $this->em->flush();

        return $this->json(['message' => 'Album supprimé avec succès.']);
    }
}
