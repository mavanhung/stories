<?php

namespace Botble\Tiki\Repositories\Interfaces;

use Botble\Support\Repositories\Interfaces\RepositoryInterface;

interface DiscountCodeInterface extends RepositoryInterface
{
    /**
     * @param int $limit
     * @param array $with
     * @return mixed
     */
    public function getDiscountCode($paginate = 10, array $with = []);

    /**
     * @param string $query
     * @param int $sellerId
     * @param int $limit
     * @param int $paginate
     * @return mixed
     */
    public function getSearch($query, $sellerId, $limit = 10, $paginate = 10);
}
