<?php
namespace App\Filament\Resources;
use App\Filament\Resources\AssessmentResource\Pages;
use App\Models\Assessment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssessmentResource extends Resource
{
    protected static ?string $model = Assessment::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Assessment Data';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Work Data')->schema([
                Forms\Components\Select::make('user_id')->relationship('user','name')->required()->searchable(),
                Forms\Components\TextInput::make('work_hours_per_week')->numeric()->required(),
                Forms\Components\TextInput::make('overtime_hours')->numeric()->required(),
                Forms\Components\TextInput::make('meetings_per_day')->numeric()->required(),
                Forms\Components\TextInput::make('deadlines_missed')->numeric()->required(),
            ])->columns(2),
            Forms\Components\Section::make('Wellness')->schema([
                Forms\Components\TextInput::make('job_satisfaction')->numeric()->minValue(1)->maxValue(10)->required(),
                Forms\Components\TextInput::make('manager_support')->numeric()->minValue(1)->maxValue(10)->required(),
                Forms\Components\TextInput::make('work_life_balance')->numeric()->minValue(1)->maxValue(10)->required(),
                Forms\Components\TextInput::make('sleep_hours')->numeric()->required(),
                Forms\Components\TextInput::make('physical_activity_days')->numeric()->required(),
                Forms\Components\TextInput::make('screen_time_hours')->numeric()->required(),
                Forms\Components\TextInput::make('caffeine_intake')->numeric()->required(),
                Forms\Components\TextInput::make('social_support_score')->numeric()->minValue(1)->maxValue(10)->required(),
                Forms\Components\Toggle::make('has_therapy'),
                Forms\Components\Toggle::make('seeks_professional_help'),
            ])->columns(2),
            Forms\Components\Section::make('Psychological')->schema([
                Forms\Components\TextInput::make('stress_level')->numeric()->minValue(1)->maxValue(10)->required(),
                Forms\Components\TextInput::make('anxiety_score')->numeric()->minValue(1)->maxValue(10)->required(),
                Forms\Components\TextInput::make('depression_score')->numeric()->minValue(1)->maxValue(10)->required(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Employee')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('work_hours_per_week')->label('Work Hrs')->sortable(),
                Tables\Columns\TextColumn::make('stress_level')->label('Stress')->sortable(),
                Tables\Columns\TextColumn::make('anxiety_score')->label('Anxiety')->sortable(),
                Tables\Columns\TextColumn::make('depression_score')->label('Depression')->sortable(),
                Tables\Columns\TextColumn::make('prediction.risk_level')->label('Risk')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'High'=>'danger','Moderate'=>'warning','Low'=>'success',default=>'gray'
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at','desc')
            ->filters([
                Tables\Filters\SelectFilter::make('risk_level')
                    ->label('Risk Level')
                    ->options(['Low'=>'Low','Moderate'=>'Moderate','High'=>'High'])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        return $query
                            ->when($value, fn ($q) => $q->whereHas('prediction', function ($predictionsQuery) use ($value) {
                                $predictionsQuery->where('risk_level', $value);
                            }));
                    }),
            ])
            ->actions([Tables\Actions\ViewAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array {
        return [
            'index'  => Pages\ListAssessments::route('/'),
            'create' => Pages\CreateAssessment::route('/create'),
            'edit'   => Pages\EditAssessment::route('/{record}/edit'),
        ];
    }
}
