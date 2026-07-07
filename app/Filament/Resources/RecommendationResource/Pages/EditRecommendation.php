<?php
namespace App\Filament\Resources\RecommendationResource\Pages;
use App\Filament\Resources\RecommendationResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditRecommendation extends EditRecord {
    protected static string $resource = RecommendationResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
