<?php

namespace Store\Http\Controllers;

use Store\Service\StoreService;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;
use Spatie\RouteAttributes\Attributes\Get;

#[Prefix('api/store')]
#[Resource(
    resource: 'store',
    apiResource: true,
    shallow: true,
    names: 'api.store',
    except: ['destroy'],
)]
class StoreController extends Controller {

    private StoreService $storeService;

    public function __construct(StoreService $storeService) {
        $this->storeService = $storeService;
    }

    #[Get('all')]
    public function getAll(): JsonResponse {
        try {
            $storeDTO = $this->storeService->getIngredients();
            return response()->json($storeDTO, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
