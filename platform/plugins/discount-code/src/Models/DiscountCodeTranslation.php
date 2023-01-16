<?php

namespace Botble\DiscountCode\Models;

use Botble\Base\Models\BaseModel;

class DiscountCodeTranslation extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'discount_codes_translations';

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'discount_codes_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;
}
