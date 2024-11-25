<?php

namespace Order\Http\Controllers;

use Order\DTOs\OrderDTO;
use Order\Services\Order\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;
use Spatie\RouteAttributes\Attributes\Post;

#[Prefix('api/order')]
#[Resource(
    resource: 'orders',
    apiResource: true,
    shallow: true,
    names: 'api.order',
    except: ['destroy'],
)]
class OrderController extends Controller {

    private OrderService $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    #[Post('create')]
    public function create(): JsonResponse {
        try {
            $orderDTO = $this->orderService->createOrder();

            return response()->json($orderDTO, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
