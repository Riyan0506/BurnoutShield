<?php
namespace App\Filament\Resources\DemographicResource\Pages;
use App\Filament\Resources\DemographicResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
class ListDemographics extends ListRecords {
    protected static string $resource = DemographicResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
