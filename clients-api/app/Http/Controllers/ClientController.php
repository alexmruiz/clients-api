<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Requests\ClientRequest;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Summary of service
     * @var ClientService
     */
    protected ClientService $service;

    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    /**
     * Summary of index
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request):JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $status = $request->get('status');

        $result = $this->service->getClients($status, $perPage, $page);

        return response()->json(new LengthAwarePaginator(
            $result['clients'],
            $result['total'],
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        ));
    }

    /**
     * Summary of store
     * @param \App\Http\Requests\ClientRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ClientRequest $request):JsonResponse
    {
        $this->service->createClient($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully'
        ], 201);
    }

    /**
     * Summary of show
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id):JsonResponse
    {
        $client = $this->service->getClient($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Client retrieved successfully'
        ]);
    }

    /**
     * Summary of update
     * @param \App\Http\Requests\ClientRequest $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ClientRequest $request, $id):JsonResponse
    {
        $updated = $this->service->updateClient($id, $request->validated());

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'No fields to update or client not found'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully'
        ]);
    }

    /**
     * Summary of destroy
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id):JsonResponse
    {
        $deleted = $this->service->deleteClient($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully'
        ]);
    }
}
