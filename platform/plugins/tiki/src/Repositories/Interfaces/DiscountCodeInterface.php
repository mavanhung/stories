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
    public function getDiscountCode(int $limit = 5, array $with = []);
}
