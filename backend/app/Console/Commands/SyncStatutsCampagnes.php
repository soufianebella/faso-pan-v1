<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CampagneService;
use Illuminate\Console\Command;

class SyncStatutsCampagnes extends Command
{
    protected $signature   = 'campagnes:sync-statuts';
    protected $description = 'Met à jour les statuts des campagnes selon leurs dates (preparation/active/expiree)';

    public function __construct(
        protected readonly CampagneService $campagneService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Synchronisation des statuts campagnes...');

        $result = $this->campagneService->syncStatuts();

        $this->line('');

        if ($result['expires'] === 0 && $result['actives'] === 0) {
            $this->line('  <fg=gray>Aucune campagne à mettre à jour.</>');
        } else {
            if ($result['expires'] > 0) {
                $this->line(
                    "  <fg=red>✗ {$result['expires']} campagne(s) expirée(s)</> <fg=gray>(faces libérées)</>",
                );
            }
            if ($result['actives'] > 0) {
                $this->line(
                    "  <fg=green>✓ {$result['actives']} campagne(s) activée(s)</>",
                );
            }
        }

        $this->line('');
        $this->info('Terminé.');

        return self::SUCCESS;
    }
}
