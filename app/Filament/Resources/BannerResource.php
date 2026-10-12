<?php
namespace App\Filament\Resources;
use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource {
    protected static ?string $model = Banner::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Promosi';
    protected static ?string $modelLabel = 'Banner Slide';
    protected static ?string $pluralModelLabel = 'Banner Slide';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make('Detail Banner')->schema([
                Forms\Components\FileUpload::make('image_path')->image()->directory('banners')->label('Gambar Banner (Rasio ideal 21:9 atau 1200x400px)'),
                Forms\Components\TextInput::make('title')->label('Judul Promo (tampil sebagai teks di banner)'),
                Forms\Components\TextInput::make('link_url')->url()->label('Link Tujuan (Opsional)'),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('sort_order')->numeric()->default(0)->label('Urutan Tampil'),
                    Forms\Components\Toggle::make('is_active')->default(true)->label('Tampilkan Banner'),
                ]),
            ])
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image_path')->width(120)->height(40)->label('Gambar'),
            Tables\Columns\TextColumn::make('title')->searchable()->label('Judul'),
            Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            Tables\Columns\TextColumn::make('sort_order')->sortable()->label('Urutan'),
        ])->defaultSort('sort_order', 'asc')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
