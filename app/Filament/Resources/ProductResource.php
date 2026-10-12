<?php
namespace App\Filament\Resources;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-eye';
    protected static ?string $navigationGroup = 'Katalog Kacamata';
    protected static ?string $modelLabel = 'Produk Kacamata';
    protected static ?string $pluralModelLabel = 'Produk Kacamata';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Informasi Utama')->schema([
                    Forms\Components\Select::make('brand_id')->relationship('brand', 'name')->nullable()->label('Merek (Brand)'),
                Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')->required()->label('Kategori'),
                    Forms\Components\TextInput::make('name')
                        ->required()->maxLength(255)->label('Nama Produk'),
                    Forms\Components\TextInput::make('slug')
                        ->required()->maxLength(255)->unique(ignoreRecord: true),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull()->label('Deskripsi'),
                    Forms\Components\TextInput::make('material')
                        ->maxLength(255)->label('Material (Cth: Titanium)'),
                    Forms\Components\TextInput::make('badge')
                        ->maxLength(255)->label('Badge (Cth: Best Seller)'),
                ])->columns(2),

                Forms\Components\Section::make('Varian Warna')->schema([
                    Forms\Components\Repeater::make('variants')
                        ->relationship('variants')
                        ->schema([
                            Forms\Components\TextInput::make('color_name')
                                ->required()->label('Nama Warna (Cth: Jet Black)'),
                            Forms\Components\ColorPicker::make('color_hex')
                                ->required()->label('Pilih Warna Hex'),
                            Forms\Components\FileUpload::make('image_path')
                                ->image()->directory('products')->label('Foto Varian'),
                            Forms\Components\TextInput::make('sku')
                                ->maxLength(255)->label('Kode SKU'),
                            Forms\Components\TextInput::make('stock')
                                ->required()->numeric()->default(0)->label('Stok'),
                            Forms\Components\Toggle::make('is_active')
                                ->default(true)->label('Aktif'),
                        ])
                        ->columns(2)
                        ->itemLabel(fn (array $state): ?string => $state['color_name'] ?? null)
                        ->addActionLabel('Tambah Varian Warna Baru')
                ]),
            ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Harga & Status')->schema([
                    Forms\Components\TextInput::make('base_price')
                        ->required()->numeric()->prefix('Rp')->label('Harga Normal'),
                    Forms\Components\TextInput::make('promo_price')
                        ->numeric()->prefix('Rp')->label('Harga Promo (Opsional)'),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)->label('Tampilkan di Website'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Nama Kacamata'),
                Tables\Columns\TextColumn::make('brand.name')->sortable()->label('Merek'),
                Tables\Columns\TextColumn::make('category.name')->sortable()->label('Kategori'),
                Tables\Columns\TextColumn::make('base_price')->money('IDR')->sortable()->label('Harga'),
                Tables\Columns\TextColumn::make('variants_count')->counts('variants')->label('Jml Warna'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Aktif'),
            ])
            ->filters([])
            ->actions([ Tables\Actions\EditAction::make() ])
            ->bulkActions([ Tables\Actions\BulkActionGroup::make([ Tables\Actions\DeleteBulkAction::make() ]) ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
