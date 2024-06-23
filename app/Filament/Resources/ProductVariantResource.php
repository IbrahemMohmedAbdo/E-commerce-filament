<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Size;
use Filament\Tables;
use App\Models\Color;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductVariant;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Filament\Resources\ProductVariantResource\RelationManagers;


class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static $formFields = [
        'product_id',
        'color_id',
        'size_id',
        'stock',
        'sku',
        'images',
        'weight',
        'height',
        'width',
        'depth',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('product_id')
                ->options(Product::all()->pluck('name', 'id'))
                ->required()
                ->label('Product'),

            Forms\Components\Select::make('color_id')
                ->options(Color::all()->pluck('name', 'id'))
                ->required()
                ->label('Color'),

            Forms\Components\Select::make('size_id')
                ->options(Size::all()->pluck('name', 'id'))
                ->required()
                ->label('Size'),

            Forms\Components\TextInput::make('stock')
                ->numeric()
                ->required()
                ->default(0),

            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->maxLength(255)
                ->required(),

            Forms\Components\FileUpload::make('images')
                ->multiple(true)
                ->label('Images'),

            Forms\Components\TextInput::make('weight')
                ->numeric(),

            Forms\Components\TextInput::make('height')
                ->numeric(),

            Forms\Components\TextInput::make('width')
                ->numeric(),

            Forms\Components\TextInput::make('depth')
                ->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('product.name') // Use dot notation to access the related model's attribute
            ->label('Product')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('color.name') // Use dot notation to access the related model's attribute
            ->label('Color')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('size.name') // Use dot notation to access the related model's attribute
            ->label('Size')
            ->searchable()
            ->sortable(),
            Tables\Columns\TextColumn::make('stock')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable(),
            Tables\Columns\TextColumn::make('weight')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('height')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('width')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('depth')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->filters([])->actions([
            Tables\Actions\EditAction::make(),
        ])->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
    }

    public static function handleCreateRequest($request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'color_id' => 'required|exists:colors,id',
            'size_id' => 'required|exists:sizes,id',
            'stock' => 'required|numeric',
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ]);

        if ($request->hasFile('images')) {
            $images = [];

            foreach ($request->file('images') as $image) {
                $path = $image->store('public/images');
                $images[] = Storage::url($path);
            }

            $validatedData['images'] = $images;
        }

        ProductVariant::create($validatedData);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }

}

