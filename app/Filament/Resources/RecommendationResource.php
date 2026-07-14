<?php
namespace App\Filament\Resources;
use App\Filament\Resources\RecommendationResource\Pages;
use App\Models\Recommendation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RecommendationResource extends Resource
{
    protected static ?string $model = Recommendation::class;
    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationGroup = 'Assessment Data';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user','name')->required()->searchable(),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->rows(3)->required(),
            Forms\Components\Select::make('category')
                ->options(['sleep'=>'Sleep','exercise'=>'Exercise','work'=>'Work','mental'=>'Mental','social'=>'Social','nutrition'=>'Nutrition','general'=>'General'])
                ->required(),
            Forms\Components\Select::make('priority')
                ->options(['high'=>'High','medium'=>'Medium','low'=>'Low'])->required(),
            Forms\Components\Toggle::make('is_completed')->label('Completed'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn($state) => 'info')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn($state) => match($state) {'high'=>'danger','medium'=>'warning','low'=>'success',default=>'gray'}),
                Tables\Columns\IconColumn::make('is_completed')
                    ->boolean()
                    ->label('Done')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at','desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options(['sleep'=>'Sleep','exercise'=>'Exercise','work'=>'Work','mental'=>'Mental','social'=>'Social','nutrition'=>'Nutrition']),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(['high'=>'High','medium'=>'Medium','low'=>'Low']),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
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
        return [
            'index'  => Pages\ListRecommendations::route('/'),
            'create' => Pages\CreateRecommendation::route('/create'),
            'edit'   => Pages\EditRecommendation::route('/{record}/edit'),
        ];
    }
}
