<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Filament\Resources\SiteSettingResource\RelationManagers;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Pengaturan Web';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Pengaturan Web')->tabs([
                    Forms\Components\Tabs\Tab::make('Pita Pengumuman')->schema([
                        Forms\Components\Toggle::make('announcement_active')->label('Aktifkan Pita Pengumuman di Atas Web?')->default(false)->columnSpanFull(),
                        Forms\Components\TextInput::make('announcement_text')->label('Teks Pengumuman')->placeholder('Cth: Gratis Ongkir Seluruh Indonesia!'),
                        Forms\Components\TextInput::make('announcement_link')->label('Tautan Pengumuman (Opsional)')->url(),
                    ])->columns(2)->icon('heroicon-o-megaphone'),
                    
                    Forms\Components\Tabs\Tab::make('Keunggulan Kami')->schema([
                        Forms\Components\Repeater::make('benefits')->label('Daftar Keunggulan (Why Choose Us)')->schema([
                            Forms\Components\TextInput::make('title')->label('Judul (Cth: Garansi 6 Bulan)')->required(),
                            Forms\Components\TextInput::make('description')->label('Deskripsi Singkat')->required(),
                            Forms\Components\Select::make('icon')->label('Ikon')->options([
                                'shield-check' => 'Perisai (Keamanan/Garansi)',
                                'truck' => 'Truk (Pengiriman)',
                                'eye' => 'Mata (Optik/Medis)',
                                'star' => 'Bintang (Kualitas)',
                                'currency-dollar' => 'Uang (Harga Terbaik)',
                            ])->default('star')->required(),
                        ])->columns(3)->defaultItems(3)->columnSpanFull(),
                    ])->icon('heroicon-o-star'),

                    Forms\Components\Tabs\Tab::make('Umum')->schema([
                        Forms\Components\TextInput::make('shop_name')->label('Nama Toko')->required()->maxLength(255)->default('Optik Dwi Putra'),
                        Forms\Components\TextInput::make('whatsapp_number')->label('Nomor WhatsApp (Cth: 62812...)')->maxLength(255),
                        Forms\Components\ColorPicker::make('theme_color')->label('Warna Tema Utama')->required()->default('#84CC16'),
                        Forms\Components\TextInput::make('hero_title')->label('Judul Besar (Hero)')->maxLength(255),
                        Forms\Components\TextInput::make('hero_subtitle')->label('Sub-Judul')->maxLength(255),
                        Forms\Components\Textarea::make('usp_text')->label('Teks Promo (USP)')->columnSpanFull(),
                    ])->columns(2)->icon('heroicon-o-cog'),
                    
                    Forms\Components\Tabs\Tab::make('Sosial Media')->schema([
                        Forms\Components\TextInput::make('instagram_url')->label('Link Instagram')->url()->placeholder('https://instagram.com/...'),
                        Forms\Components\TextInput::make('facebook_url')->label('Link Facebook')->url(),
                        Forms\Components\TextInput::make('tiktok_url')->label('Link TikTok')->url(),
                    ])->columns(2)->icon('heroicon-o-globe-alt'),
                    
                    Forms\Components\Tabs\Tab::make('Footer & Alamat')->schema([
                        Forms\Components\Textarea::make('address')->label('Alamat Fisik Toko')->columnSpanFull(),
                        Forms\Components\Textarea::make('operating_hours')->label('Jam Operasional')->placeholder('Cth: Senin - Sabtu: 09:00 - 21:00')->columnSpanFull(),
                        Forms\Components\TextInput::make('warranty_url')->label('Tautan Kebijakan Garansi')->url()->placeholder('https://...'),
                        Forms\Components\TextInput::make('size_guide_url')->label('Tautan Panduan Ukuran Frame')->url()->placeholder('https://...'),
                    ])->columns(2)->icon('heroicon-o-map-pin'),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('shop_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('whatsapp_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('theme_color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hero_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hero_subtitle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
