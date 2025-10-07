<?php

namespace Tests\Feature;

use App\Http\Controllers\ClientController;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{

    protected $service;
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock del servicio
        $this->service = Mockery::mock(ClientService::class);
        $this->controller = new ClientController($this->service);
    }

    public function test_it_returns_paginated_clients()
    {
        $clients = [
            ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com']
        ];

        $this->service
            ->shouldReceive('getClients')
            ->once()
            ->andReturn(['clients' => $clients, 'total' => 1]);

        $request = Request::create('/clients', 'GET', ['per_page' => 10, 'page' => 1]);

        $response = $this->controller->index($request);
        $data = $response->getData(true);

        $this->assertEquals(1, $data['total']);
        $this->assertEquals('John', $data['data'][0]['first_name']);
    }

    public function test_it_creates_a_client()
    {
        $requestData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ];

        $request = Mockery::mock(\App\Http\Requests\ClientRequest::class);
        $request->shouldReceive('validated')->andReturn($requestData);

        $this->service
            ->shouldReceive('createClient')
            ->once()
            ->with($requestData);

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($data['success']);
    }

    public function test_it_returns_client_by_id()
    {
        $client = ['id' => 1, 'first_name' => 'John'];

        $this->service
            ->shouldReceive('getClient')
            ->once()
            ->with(1)
            ->andReturn($client);

        $response = $this->controller->show(1);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('John', $data['data']['first_name']);
    }

    public function test_it_updates_a_client()
    {
        $requestData = ['first_name' => 'Johnny'];

        $request = Mockery::mock(\App\Http\Requests\ClientRequest::class);
        $request->shouldReceive('validated')->andReturn($requestData);


        $this->service
            ->shouldReceive('updateClient')
            ->once()
            ->with(1, $requestData)
            ->andReturn(true);

        $response = $this->controller->update($request, 1);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
    }

    public function test_it_deletes_a_client()
    {
        $this->service
            ->shouldReceive('deleteClient')
            ->once()
            ->with(1)
            ->andReturn(true);

        $response = $this->controller->destroy(1);
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
