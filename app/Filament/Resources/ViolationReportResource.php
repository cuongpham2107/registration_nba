<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ViolationReportResource\Pages;
use App\Models\ViolationReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ViolationReportResource extends Resource
{
    protected static ?string $model = ViolationReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Biên bản vi phạm';
    protected static ?string $navigationLabel = 'Biên bản vi phạm';
    protected static ?string $navigationGroup = 'Quản lý danh mục';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        Forms\Components\DateTimePicker::make('recorded_at')
                            ->label('Thời gian lập')
                            ->required(),
                        Forms\Components\TextInput::make('location')
                            ->label('Địa điểm')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Người liên quan')
                    ->schema([
                        Forms\Components\Repeater::make('reporters')
                            ->label('Người lập biên bản')
                            ->schema([
                                Forms\Components\TextInput::make('ho_ten')->label('Họ tên')->required(),
                                Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),
                                Forms\Components\TextInput::make('cong_ty')->label('Công ty'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Thêm người lập biên bản')
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->collapsible(false),

                        Forms\Components\Repeater::make('witnesses')
                            ->label('Người làm chứng')
                            ->schema([
                                Forms\Components\TextInput::make('ho_ten')->label('Họ tên')->required(),
                                Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),
                                Forms\Components\TextInput::make('cong_ty')->label('Công ty'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Thêm người làm chứng')
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->collapsible(false),

                        Forms\Components\Repeater::make('violators')
                            ->label('Người vi phạm')
                            ->schema([
                                Forms\Components\TextInput::make('ho_ten')->label('Họ tên')->required(),
                                Forms\Components\TextInput::make('chuc_vu')->label('Chức vụ'),
                                Forms\Components\TextInput::make('cong_ty')->label('Công ty'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Thêm người vi phạm')
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->collapsible(false),
                    ]),

                Forms\Components\Section::make('Nội dung sự việc')
                    ->schema([
                        Forms\Components\TextInput::make('target')
                            ->label('Mục tiêu')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('violation_content')
                            ->label('Nội dung vi phạm')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('violation_count')
                            ->label('Số lần vi phạm')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('violator_attitude')
                            ->label('Thái độ người vi phạm')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Xử lý')
                    ->schema([
                        Forms\Components\Textarea::make('resolution_direction')
                            ->label('Hướng xử lý')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recorded_at')
                    ->label('Thời gian lập')
                    ->dateTime('H:i, d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Địa điểm')
                    ->searchable(),
                Tables\Columns\TextColumn::make('violation_content')
                    ->label('Nội dung vi phạm')
                    ->limit(50),
                Tables\Columns\TextColumn::make('violation_count')
                    ->label('Số lần'),
                Tables\Columns\TextColumn::make('resolution_direction')
                    ->label('Hướng xử lý')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListViolationReports::route('/'),
            'create' => Pages\CreateViolationReport::route('/create'),
            'edit' => Pages\EditViolationReport::route('/{record}/edit'),
        ];
    }
}
