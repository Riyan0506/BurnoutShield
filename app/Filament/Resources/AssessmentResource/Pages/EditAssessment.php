<?php
namespace App\Filament\Resources\AssessmentResource\Pages;
use App\Filament\Resources\AssessmentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditAssessment extends EditRecord {
    protected static string $resource = AssessmentResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
