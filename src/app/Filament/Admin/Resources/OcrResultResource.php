<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OcrResultResource\Pages;
use App\Filament\Admin\Resources\OcrResultResource\RelationManagers;
use App\Models\OcrResult;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OcrResultResource extends Resource
{
    protected static ?string $model = OcrResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nisn')->label('NISN')->maxLength(255),
                TextInput::make('nama')->label('Nama')->maxLength(255),
                TextInput::make('tahun_lulus')->label('Tahun Lulus')->maxLength(4),
                TextInput::make('sekolah')->label('Sekolah')->maxLength(255),
                Textarea::make('raw_text')->label('Raw Text')->rows(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nisn')->label('NISN')->sortable()->searchable(),
                TextColumn::make('nama')->label('Nama')->sortable()->searchable(),
                TextColumn::make('tahun_lulus')->label('Tahun Lulus')->sortable(),
                TextColumn::make('sekolah')->label('Sekolah')->sortable()->searchable(),
                TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOcrResults::route('/'),
            'create' => Pages\CreateOcrResult::route('/create'),
            'edit' => Pages\EditOcrResult::route('/{record}/edit'),
        ];
    }
}
