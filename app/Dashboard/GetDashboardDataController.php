<?php

namespace App\Dashboard;

use App\Shared\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;

final class GetDashboardDataController
{

    public function __construct(private DashboardService $dashboardService) {}

    public function __invoke(ServerRequestInterface $request)
    {

        try {
            $data = $this->dashboardService->getData();

            return JsonResponse::ok($data);
        } catch (\Throwable $e) {
            echo "Error: " . $e->getTraceAsString() . PHP_EOL;

            return JsonResponse::internalServerError($e->getMessage());
        }
    }
}
