<?php

namespace Botble\Tiki\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;

class Seller extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tiki_sellers';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'seller_name',
        'store_name',
        'seller_id',
        'store_id',
        'store_level',
        'seller_type',
        'storefront_label',
        'logo',
        'seller_url',
        'url_slug',
        'live_at',
        'status'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];
}
