<?php

namespace Botble\Tiki\Repositories\Caches;

use Botble\Support\Repositories\Caches\CacheAbstractDecorator;
use Botble\Tiki\Repositories\Interfaces\SellerInterface;

class SellerCacheDecorator extends CacheAbstractDecorator implements SellerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getSeller(int $limit = 5, array $with = [])
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritDoc}
     */
    public function searchSeller($query, $limit = 10, $paginate = 10)
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }
}
