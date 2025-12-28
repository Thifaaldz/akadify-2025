<?php

namespace App\Filament\Admin\Resources\OcrResultResource\Pages;

use App\Filament\Admin\Resources\OcrResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOcrResults extends ListRecords
{
    protected static string $resource = OcrResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
