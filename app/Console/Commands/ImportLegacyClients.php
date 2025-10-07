<?php

namespace App\Console\Commands;

use App\Services\ClientService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportLegacyClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:legacy-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import clients from legacy system using SQL native queries';

    /**
     * Summary of service
     * @var ClientService
     */
    protected ClientService $service;

    public function __construct(ClientService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando importación de clientes legacy...');

        try {
            // Call HTTP to legacy endpoint
            $response = Http::get('http://localhost/legacy_clientes.php', [
                'format' => 'json'
            ]);
        } catch (\Exception $e) {
            $this->error('Error to conect with legacy endpoint ' . $e->getMessage());
            return 1;
        }

        if (!$response->ok()) {
            $this->error('Error to conect with legacy endpoint');
            return 1;
        }

        $legacyClients = $response->json();

        // Validate response structure
        if (!is_array($legacyClients) || empty($legacyClients)) {
            $this->error('No legacy clients found to import.');
            return 1;
        }

        $count = $this->service->syncLegacyClients($legacyClients);

        $this->info('Import completed successfully. Total clients processed: ' . $count);
        return 0;
    }
}
