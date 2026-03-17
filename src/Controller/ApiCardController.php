<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Card;
use App\Repository\CardRepository;
use App\Repository\ArtistRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly CardRepository $cardRepository,
        private readonly ArtistRepository $artistRepository,
        private readonly LoggerInterface $logger
    ) {
    }
    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Get(description: 'Return a paginated list of cards with optional set filtering')]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'set', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'List of cards')]
    public function cardAll(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $set = $request->query->get('set');
        $artistId = $request->query->getInt('artist');
        $limit = 100;

        $criteria = [];
        if ($set) {
            $criteria['setCode'] = $set;
        }
        if ($artistId) {
            $criteria['artist'] = $artistId;
        }

        $cards = $this->cardRepository->findBy(
            $criteria, 
            ['name' => 'ASC'], 
            $limit, 
            ($page - 1) * $limit
        );

        return $this->json($cards);
    }

    #[Route('/sets', name: 'list_sets', methods: ['GET'])]
    #[OA\Get(description: 'List all unique set codes')]
    #[OA\Response(response: 200, description: 'List of unique set codes')]
    public function listSets(): Response
    {
        return $this->json($this->cardRepository->findAllUniqueSetCodes());
    }

    #[Route('/artists', name: 'list_artists', methods: ['GET'])]
    #[OA\Get(description: 'List all artists')]
    #[OA\Response(response: 200, description: 'List of artists')]
    public function listArtists(): Response
    {
        return $this->json($this->artistRepository->findAllSorted());
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    #[OA\Get(description: 'Search cards by name with optional set and artist filtering')]
    #[OA\Parameter(name: 'q', description: 'The search query', in: 'query', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'set', description: 'Filter by set code', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'artist', description: 'Filter by artist ID', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'List of found cards')]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $set = $request->query->get('set');
        $artistId = $request->query->getInt('artist');
        
        if (strlen($query) < 3) {
            return $this->json([]);
        }

        $cards = $this->cardRepository->searchByName($query, $set, $artistId ?: null);

        return $this->json($cards);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Put(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $card = $this->cardRepository->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            return $this->json(['error' => 'Card not found'], 404);
        }
        return $this->json($card);
    }
}
