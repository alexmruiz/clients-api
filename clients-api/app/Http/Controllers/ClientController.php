<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ClientRequest;


class ClientController extends Controller
{
    /**
     * Display a listing of the clients with pagination and optional filtering.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Pagination parameters
        $perPage = (int) ($request->get('per_page', 10));
        $page = (int) ($request->get('page', 1));

        // Optional filtering by status
        $status = $request->get('status');

        // Build the base query
        $sql = "SELECT * FROM clients";
        $bindings = [];

        if ($status !== null) {
            $sql .= " WHERE status = ?";
            $bindings[] = $status;
        }

        // Get total count for pagination
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) as total', $sql);
        $total = DB::selectOne($countSql, $bindings)->total;

        // Apply LIMIT and OFFSET for pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $bindings[] = $perPage;
        $bindings[] = $offset;

        // Execute the final query
        $clients = DB::select($sql, $bindings);

        return response()->json(new LengthAwarePaginator(
            $clients,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        ));
    }

    /**
     * Insert a newly created client in storage.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ClientRequest $request)
    {
        DB::insert(
            "INSERT INTO clients (first_name, last_name, email, status, legacy_id, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))",
            [
                $request->first_name,
                $request->last_name,
                $request->email,
                $request->input('status', 1),
                $request->legacy_id
            ]
        );

        return response()->json(['message' => 'Client created successfully'], 201);
    }

    /**
     * Display the specified client.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $client = DB::select("SELECT * FROM clients WHERE id = ?", [$id]);

        if (empty($client)) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client[0]);
    }

    /**
     * Update the specified client in storage.
     * @param \App\Http\Requests\ClientRequest $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ClientRequest $request, $id)
    {

        $field = [];
        $bindings = [];

        foreach (['first_name', 'last_name', 'email', 'status', 'legacy_id'] as $key) {
            if ($request->has($key)) {
                $field[] = "$key = ?";
                $bindings[] = $request->$key;
            }
        }

        if (empty($field)) {
            return response()->json(['message' => 'No fields to update'], 400);
        }

        $bindings[] = $id;

        $sql = "UPDATE clients SET " . implode(', ', $field) . ", updated_at = datetime('now') WHERE id = ?";
        DB::update($sql, $bindings);

        return response()->json(['message' => 'Client updated successfully']);
    }

    /**
     * Remove the specified client from storage.
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $deleted = DB::delete("DELETE FROM clients WHERE id = ?", [$id]);

        if ($deleted) {
            return response()->json(['message' => 'Client deleted successfully']);
        } else {
            return response()->json(['message' => 'Client not found'], 404);
        }
    }
}
