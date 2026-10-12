<?php
namespace App\Filament\Resources;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Penjualan';
    protected static ?string $modelLabel = 'Pesanan Pelanggan';
    protected static ?string $pluralModelLabel = 'Pesanan Pelanggan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pelanggan')->schema([
                Forms\Components\TextInput::make('order_number')->required()->label('Nomor Order'),
                Forms\Components\Select::make('status')
                    ->options(['pending' => 'Pending', 'paid' => 'Lunas', 'shipped' => 'Dikirim', 'cancelled' => 'Dibatalkan'])
                    ->required()->label('Status Pesanan'),
                Forms\Components\TextInput::make('customer_name')->required()->label('Nama Pelanggan'),
                Forms\Components\TextInput::make('customer_whatsapp')->required()->label('WhatsApp'),
                Forms\Components\Textarea::make('shipping_address')->columnSpanFull()->label('Alamat Pengiriman'),
                Forms\Components\TextInput::make('total_amount')->required()->numeric()->prefix('Rp')->label('Total Harga'),
            ])->columns(2),

            Forms\Components\Section::make('Rincian Kacamata & Resep Mata')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\Select::make('product_variant_id')
                            ->relationship('variant', 'color_name')
                            ->label('Varian Yang Dipilih')
                            ->disabled(),
                        Forms\Components\TextInput::make('quantity')->numeric()->label('Qty')->disabled(),
                        Forms\Components\TextInput::make('price')->numeric()->prefix('Rp')->label('Harga Satuan')->disabled(),
                        
                        Forms\Components\Repeater::make('prescription')
                            ->relationship('prescription')
                            ->schema([
                                Forms\Components\TextInput::make('lens_type')->label('Tipe Lensa (Single Vision / Frame Only)'),
                                Forms\Components\Grid::make(4)->schema([
                                    Forms\Components\TextInput::make('right_sph')->label('R SPH'),
                                    Forms\Components\TextInput::make('right_cyl')->label('R CYL'),
                                    Forms\Components\TextInput::make('right_axis')->label('R AXIS'),
                                    Forms\Components\Placeholder::make('space1')->label('')->content('Kanan (OD)'),
                                ]),
                                Forms\Components\Grid::make(4)->schema([
                                    Forms\Components\TextInput::make('left_sph')->label('L SPH'),
                                    Forms\Components\TextInput::make('left_cyl')->label('L CYL'),
                                    Forms\Components\TextInput::make('left_axis')->label('L AXIS'),
                                    Forms\Components\Placeholder::make('space2')->label('')->content('Kiri (OS)'),
                                ]),
                                Forms\Components\TextInput::make('pd')->label('PD (Jarak Pupil)'),
                            ])
                            ->columnSpanFull()
                            ->addable(false)
                            ->deletable(false)
                    ])
                    ->columnSpanFull()
                    ->addable(false)
                    ->deletable(false)
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->label('Nomor Order'),
                Tables\Columns\TextColumn::make('customer_name')->searchable()->label('Pelanggan'),
                Tables\Columns\TextColumn::make('customer_whatsapp')->label('WhatsApp'),
                Tables\Columns\TextColumn::make('total_amount')->money('IDR')->sortable()->label('Total'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid', 'shipped' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })->label('Status'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('Tanggal'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([ Tables\Actions\EditAction::make() ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
