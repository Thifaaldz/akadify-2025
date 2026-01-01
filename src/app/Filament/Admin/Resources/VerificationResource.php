<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VerificationResource\Pages;
use App\Models\Verification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class VerificationResource extends Resource
{
    protected static ?string $model = Verification::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document'; // ikon yang pasti ada

    // =========================
    // FORM (untuk create/edit)
    // =========================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')
                    ->label('Student')
                    ->relationship('student', 'nama')
                    ->required(),
                Forms\Components\TextInput::make('ijazah_path')
                    ->label('Ijazah Path')
                    ->required()
                    ->disabled(), // biasanya auto dari OCR
                Forms\Components\Select::make('status')
                    ->options([
                        'PENDING_OCR' => 'Pending OCR',
                        'PROCESSING' => 'Processing',
                        'VERIFIED' => 'Verified',
                        'REJECTED' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('reason')
                    ->label('Reason / Notes')
                    ->json(), // menyesuaikan cast array
            ]);
    }

    // =========================
    // TABLE (untuk list/index)
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('student.nama')
                    ->label('Student Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.nisn')
                    ->label('NISN')
                    ->sortable(),
                TextColumn::make('ijazah_path')
                    ->label('Ijazah Path')
                    ->wrap()
                    ->limit(50),
                BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'PENDING_OCR',
                        'warning' => 'PROCESSING',
                        'success' => 'VERIFIED',
                        'danger' => 'REJECTED',
                    ]),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->wrap()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'PENDING_OCR' => 'Pending OCR',
                        'PROCESSING' => 'Processing',
                        'VERIFIED' => 'Verified',
                        'REJECTED' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    // =========================
    // RELATIONS
    // =========================
    public static function getRelations(): array
    {
        return [
            // nanti bisa ditambahkan relasi ke OCR result jika ada
        ];
    }

    // =========================
    // PAGES
    // =========================
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerifications::route('/'),
            'create' => Pages\CreateVerification::route('/create'),
            'edit' => Pages\EditVerification::route('/{record}/edit'),
        ];
    }
}
