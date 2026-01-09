<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all active services
     */
    public function index()
    {
        $services = Service::active()
            ->ordered()
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'code' => $service->code,
                    'description' => $service->description,
                    'icon' => $service->icon,
                    'color' => $service->color,
                    'average_service_time' => $service->average_service_time,
                    'waiting_count' => $service->waiting_count,
                    'is_queue_full' => $service->isQueueFull(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    /**
     * Get a specific service with details
     */
    public function show(Service $service)
    {
        $service->load('counters');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $service->id,
                'name' => $service->name,
                'code' => $service->code,
                'description' => $service->description,
                'icon' => $service->icon,
                'color' => $service->color,
                'average_service_time' => $service->average_service_time,
                'max_queue_size' => $service->max_queue_size,
                'waiting_count' => $service->waiting_count,
                'today_completed_count' => $service->today_completed_count,
                'average_wait_time' => $service->average_wait_time,
                'current_serving' => $service->current_serving?->queue_number,
                'is_queue_full' => $service->isQueueFull(),
                'counters' => $service->counters->map(function ($counter) {
                    return [
                        'id' => $counter->id,
                        'name' => $counter->name,
                        'code' => $counter->code,
                        'status' => $counter->status,
                        'location' => $counter->location,
                    ];
                }),
            ],
        ]);
    }
}
