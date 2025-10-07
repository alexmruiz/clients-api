<?php

namespace Tests\Feature;

use App\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ClientService $clientService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->clientService = new ClientService();
    }

    public function testCreateClient(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'juan@doe.com',
        ];

        $this->clientService->createClient($data);

        $client = DB::table('clients')->first();
        $this->assertNotNull($client);
        $this->assertEquals('John', $client->first_name);
        $this->assertEquals('Doe', $client->last_name);
        $this->assertEquals('juan@doe.com', $client->email);
        $this->assertEquals(1, $client->status);
    }

    public function test_it_retrieves_a_client_by_id(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'status' => 1,
            'legacy_id' => 123,
        ];

        $this->clientService->createClient($data);
        $client = DB::table('clients')->first();

        $retrieved = $this->clientService->getClient($client->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals('Jane', $retrieved->first_name);
        $this->assertEquals('Smith', $retrieved->last_name);
        $this->assertEquals('jane.smith@example.com', $retrieved->email);
    }

    public function test_it_updates_a_client(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'status' => 1,
            'legacy_id' => 123,
        ];

        $this->clientService->createClient($data);
        $updated = $this->clientService->updateClient(1, [
            'first_name' => 'Diego',
            'status' => 0
        ]);
        $this->assertTrue($updated);
        $client = DB::table('clients')->first();
        $this->assertEquals('Diego', $client->first_name);
        $this->assertEquals(0, $client->status);
        $this->assertNotNull($client->updated_at);
    }

    public function test_it_fails_to_update_nonexistent_client(): void
    {
        $updated = $this->clientService->updateClient(999, [
            'first_name' => 'NonExistent'
        ]);
        $this->assertFalse($updated);
    }

    public function test_it_can_delete_a_client(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'status' => 1,
            'legacy_id' => 123,
        ];

        $this->clientService->createClient($data);
        $deleted = $this->clientService->deleteClient(1);
        $this->assertTrue($deleted);
        $this->assertNull(DB::table('clients')->first());
    }

   
    public function it_can_get_clients_with_pagination()
    {
        // Insert 15 clients
        for ($i = 1; $i <= 15; $i++) {
            DB::table('clients')->insert([
                'first_name' => "User $i",
                'last_name' => "Test",
                'email' => "user$i@example.com",
                'status' => 1,
                'legacy_id' => null,
                'created_at' => now(),
                'updated_at' => null
            ]);
        }

        $result = $this->clientService->getClients(null, 10, 1);
        $this->assertCount(10, $result['clients']);
        $this->assertEquals(15, $result['total']);

        $resultPage2 = $this->clientService->getClients(null, 10, 2);
        $this->assertCount(5, $resultPage2['clients']);
    }

    
    public function it_can_sync_legacy_clients()
    {
        $legacyClients = [
            [
                'first_name' => 'Legacy 1',
                'last_name' => 'User',
                'email' => 'legacy1@example.com',
                'legacy_id' => 101,
                'created_at' => now()
            ],
            [
                'first_name' => 'Legacy 2',
                'last_name' => 'User',
                'email' => 'legacy2@example.com',
                'legacy_id' => 102,
                'created_at' => now()
            ]
        ];

        $count = $this->clientService->syncLegacyClients($legacyClients);
        $this->assertEquals(2, $count);

        $dbClients = DB::table('clients')->get();
        $this->assertCount(2, $dbClients);

        // Update one client
        $legacyClients[0]['first_name'] = 'Updated Legacy 1';
        $count = $this->clientService->syncLegacyClients($legacyClients);
        $this->assertEquals(2, $count);

        $client1 = DB::table('clients')->where('legacy_id', 101)->first();
        $this->assertEquals('Updated Legacy 1', $client1->first_name);
    }
}
