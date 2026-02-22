<?php

namespace App\Console\Commands;

use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\InspectionLot;
use App\Models\InventoryLot;
use App\Models\MasterData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureMongoIndexes extends Command
{
    protected $signature = 'mongo:ensure-indexes';

    protected $description = 'Create MongoDB indexes for all collections';

    protected array $models = [
        MasterData::class,
        InventoryLot::class,
        Inspection::class,
        InspectionItem::class,
        InspectionLot::class,
    ];

    public function handle(): int
    {
        $this->info('Ensuring MongoDB indexes…');

        foreach ($this->models as $modelClass) {
            $model      = new $modelClass;
            $collection = DB::connection($model->getConnectionName())
                            ->getCollection($model->getTable());

            $definitions = $modelClass::indexDefinitions();

            $this->line('');
            $this->comment("Collection: {$model->getTable()} (" . count($definitions) . ' indexes)');

            foreach ($definitions as $def) {
                $name = $def['options']['name'] ?? json_encode($def['key']);

                try {
                    $collection->createIndex($def['key'], $def['options']);
                    $this->line("  ✔ {$name}");
                } catch (\Exception $e) {
                    $this->error("  ✘ {$name}: {$e->getMessage()}");
                }
            }
        }

        $this->line('');
        $this->info('Done.');

        return self::SUCCESS;
    }
}
