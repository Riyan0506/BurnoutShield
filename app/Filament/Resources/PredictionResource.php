<?php
namespace App\Filament\Resources;
use App\Filament\Resources\PredictionResource\Pages;
use App\Models\PredictionResult;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PredictionResource extends Resource
{
    protected static ?string $model = PredictionResult::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Prediction Results';
    protected static ?string $navigationGroup = 'Assessment Data';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form { return $form->schema([]); }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('risk_level')
                    ->label('Risk')
                    ->badge()
                    ->color(fn ($state) => match($state) {'High'=>'danger','Moderate'=>'warning','Low'=>'success',default=>'gray'}),
                Tables\Columns\TextColumn::make('burnout_probability')
                    ->label('Burnout %')
                    ->formatStateUsing(fn ($state) => number_format($state, 2).'%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('model_used')
                    ->label('Model')
                    ->badge()
                    ->color(fn($state) => 'info')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('recommendations_count')
                    ->label('Recs')
                    ->counts('recommendations')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at','desc')
            ->filters([
                Tables\Filters\SelectFilter::make('risk_level')
                    ->label('Risk Level')
                    ->options(['High'=>'High','Moderate'=>'Moderate','Low'=>'Low']),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->label('•••'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ]),
            ])
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array {
        return ['index' => Pages\ListPredictions::route('/')];
    }
}
