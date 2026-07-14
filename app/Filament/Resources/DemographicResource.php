<?php
namespace App\Filament\Resources;
use App\Filament\Resources\DemographicResource\Pages;
use App\Models\DemographicData;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DemographicResource extends Resource
{
    protected static ?string $model = DemographicData::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Demographic Data';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')->relationship('user','name')->required()->searchable(),
            Forms\Components\TextInput::make('age')->numeric()->minValue(16)->maxValue(80),
            Forms\Components\Select::make('gender')->options(['Male'=>'Male','Female'=>'Female','Non-binary'=>'Non-binary','Prefer not to say'=>'Prefer not to say']),
            Forms\Components\Select::make('job_role')->options([
                'Software Engineer'=>'Software Engineer','Data Scientist'=>'Data Scientist',
                'DevOps'=>'DevOps','Frontend Developer'=>'Frontend Developer',
                'Backend Developer'=>'Backend Developer','Full Stack Developer'=>'Full Stack Developer',
                'Product Manager'=>'Product Manager','Project Manager'=>'Project Manager',
                'UX Designer'=>'UX Designer','QA Engineer'=>'QA Engineer','CTO'=>'CTO','Other'=>'Other',
            ]),
            Forms\Components\TextInput::make('experience_years')->numeric()->step(0.5),
            Forms\Components\Select::make('company_size')->options([
                'Startup (<50)'=>'Startup (<50)','Mid-size (50-500)'=>'Mid-size (50-500)',
                'Large (500-5000)'=>'Large (500-5000)','MNC (>5000)'=>'MNC (>5000)',
            ]),
            Forms\Components\Select::make('work_mode')->options(['Remote'=>'Remote','Onsite'=>'Onsite','Hybrid'=>'Hybrid']),
        ])->columns([
            'default' => 1,
            'sm' => 1,
            'md' => 2,
            'lg' => 2,
            'xl' => 2,
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
                Tables\Columns\TextColumn::make('age')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('gender')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('job_role')
                    ->label('Role')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('experience_years')
                    ->label('Exp (yrs)')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('company_size')
                    ->label('Company')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('work_mode')
                    ->badge()
                    ->color(fn($state) => 'info')
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options(['Male' => 'Male', 'Female' => 'Female', 'Non-binary' => 'Non-binary', 'Prefer not to say' => 'Prefer not to say']),
                Tables\Filters\SelectFilter::make('work_mode')
                    ->options(['Remote' => 'Remote', 'Onsite' => 'Onsite', 'Hybrid' => 'Hybrid']),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListDemographics::route('/'),
            'create' => Pages\CreateDemographic::route('/create'),
            'edit'   => Pages\EditDemographic::route('/{record}/edit'),
        ];
    }
}
