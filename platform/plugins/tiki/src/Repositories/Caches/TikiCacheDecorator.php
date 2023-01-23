<?php

namespace Botble\Tiki\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use Botble\Tiki\Repositories\Interfaces\TikiInterface;

class TikiCacheDecorator extends CacheAbstractDecorator implements TikiInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDiscountCode($query, $limit = 10, $paginate = 10)
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function getAllDiscountCode($perPage = 12, $active = true, array $with = ['slugable'])
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }
}
