<?php

namespace App\Http\Controllers;

use App\DTOs\OrderDTO;
use App\Services\Order\Impl\OrderServiceImpl;
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

    private OrderServiceImpl $orderService;

    public function __construct(OrderServiceImpl $orderService) {
        $this->orderService = $orderService;
    }

    #[Post('create')]
    public function create(): OrderDTO {
        $orderDTO = $this->orderService->createOrder();
        $this->orderService->publishOrderToQueue($orderDTO);
        return $orderDTO;
    }

    #[Post('update-recipe')]
    public function updateRecipeName(OrderDTO $dto): void {
        $this->orderService->updateOrderRecipe($dto);
    }

    #[Post('update-status')]
    public function updateStatus(OrderDTO $dto): void {
        $this->orderService->updateOrderStatus($dto);
    }
}
