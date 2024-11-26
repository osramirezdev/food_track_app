<?php

namespace Order\Http\Controllers;

use Order\Services\Order\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Get;
use Order\Events\OrderUpdated;

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

    #[Get('all')]
    public function getAll(): JsonResponse {
        try {
            $ordersDTO = $this->orderService->getOrders();
            return response()->json($ordersDTO, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Post('create')]
    public function create(): JsonResponse {
        try {
            $orderDTO = $this->orderService->createOrder();
            event(new OrderUpdated($orderDTO));
            return response()->json($orderDTO, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
