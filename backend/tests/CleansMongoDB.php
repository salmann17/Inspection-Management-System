<?php

namespace Tests;

use App\Models\Inspection;
use App\Models\InspectionCharge;
use App\Models\InspectionItem;
use App\Models\InspectionLot;

trait CleansMongoDB
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanCollections();
    }

    private function cleanCollections(): void
    {
        Inspection::truncate();
        InspectionItem::truncate();
        InspectionLot::truncate();
        InspectionCharge::truncate();
    }
}
