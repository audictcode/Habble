<?php

namespace App\Models;

use App\Models\Furni\FurniCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FurniValue extends Model
{
    use HasFactory;

    protected $table = 'furni_values';

    protected $fillable = [
        'name', 'category_id', 'admin_id', 'price',
        'price_type', 'state', 'icon_path', 'image_path',
        'habboassets_furni_id', 'habboassets_hotel',
        'habboassets_source_url', 'imported_from_habboassets_at',
        'source_provider', 'habbofurni_item_id',
        'habbofurni_imported_at', 'external_metadata'
    ];

    protected $hidden = [
        'admin_id'
    ];

    protected const API_PAGINATION_LIMIT = 9;

    protected $casts = [
        'imported_from_habboassets_at' => 'datetime',
        'habbofurni_imported_at' => 'datetime',
        'external_metadata' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class);
    }

    public static function defaultQuery()
    {
        return FurniValue::orderByDesc('id');
    }

    public function category()
    {
        return $this->belongsTo(FurniCategory::class);
    }

    public static function resultsForApi(?string $search)
    {
        if(!$search) {
            return FurniValue::defaultQuery()
                ->paginate(self::API_PAGINATION_LIMIT);
        }

        return FurniValue::defaultQuery()
            ->where('name', 'LIKE', "%{$search}%")
            ->paginate(self::API_PAGINATION_LIMIT);
    }
}
