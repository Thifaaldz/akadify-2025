<?php

namespace App\Filament\Admin\Resources\VerificationResource\Pages;

use App\Filament\Admin\Resources\VerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVerification extends EditRecord
{
    protected static string $resource = VerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
