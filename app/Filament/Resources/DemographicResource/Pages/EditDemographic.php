<?php
namespace App\Filament\Resources\DemographicResource\Pages;
use App\Filament\Resources\DemographicResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditDemographic extends EditRecord {
    protected static string $resource = DemographicResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
