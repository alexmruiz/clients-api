<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ClientService
{
    /**
     * Summary of getClients
     * @param mixed $status
     * @param int $perPage
     * @param int $page
     * @return array{clients: array, total: mixed}
     */
    public function getClients(?int $status, int $perPage, int $page): array
    {
        $sql = "SELECT * FROM clients";
        $bindings = [];

        if ($status !== null) {
            $sql .= " WHERE status = ?";
            $bindings[] = $status;
        }

        // Count results totales
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*) AS total', $sql);
        $total = DB::selectOne($countSql, $bindings)->total;

        // Pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        $bindings[] = $perPage;
        $bindings[] = $offset;

        $clients = DB::select($sql, $bindings);

        return [
            'clients' => $clients,
            'total' => $total,
        ];
    }

    /**
     * Create a new client.
     * @param array $data
     * @return void
     */
    public function createClient(array $data): void
    {
        DB::insert(
            "INSERT INTO clients (first_name, last_name, email, status, legacy_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['status'] ?? 1,
                $data['legacy_id'] ?? null,
                now(),
                now()
            ]
        );
    }

    /**
     * Get a client by ID.
     * @param int $id
     */
    public function getClient(int $id)
    {
        $client = DB::select("SELECT * FROM clients WHERE id = ?", [$id]);
        return $client[0] ?? null;
    }

    /**
     * Update a client by ID with provided data.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateClient(int $id, array $data): bool
    {
        $fields = [];
        $bindings = [];

        foreach (['first_name', 'last_name', 'email', 'status', 'legacy_id'] as $key) {
            if (isset($data[$key])) {
                $fields[] = "$key = ?";
                $bindings[] = $data[$key];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = ?";
        $bindings[] = now();
        $bindings[] = $id;

        $sql = "UPDATE clients SET " . implode(', ', $fields) . " WHERE id = ?";
        DB::update($sql, $bindings);

        return true;
    }

    /**
     * Delete a client by ID.
     * @param int $id
     * @return bool
     */
    public function deleteClient(int $id): bool
    {
        $deleted = DB::delete("DELETE FROM clients WHERE id = ?", [$id]);
        return $deleted > 0;
    }
}
