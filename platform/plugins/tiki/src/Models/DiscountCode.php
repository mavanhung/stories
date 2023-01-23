<?php

namespace Botble\Tiki\Models;

use Botble\Tiki\Models\Seller;
use Botble\Base\Models\BaseModel;
use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCode extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tiki_discount_codes';

    /**
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'categories_id',
        'coupon_id',
        'coupon_code',
        'label',
        'tags',
        'short_title',
        'period',
        'simple_action',
        'coupon_type',
        'discount_amount',
        'min_amount',
        'rule_id',
        'short_description',
        'long_description',
        'expired_at',
        'icon_url',
        'is_crawler_home',
        'status'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    /**
     * @deprecated
     * @return BelongsTo
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'seller_id');
    }
}
