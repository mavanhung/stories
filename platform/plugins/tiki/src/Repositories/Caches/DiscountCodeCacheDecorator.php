<?php

namespace Botble\Tiki\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;

class DiscountCodeCacheDecorator extends CacheAbstractDecorator implements DiscountCodeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDiscountCode(int $limit = 5, array $with = [])
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }
}