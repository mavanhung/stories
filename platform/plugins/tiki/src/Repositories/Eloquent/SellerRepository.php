<?php

namespace Botble\Tiki\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Tiki\Repositories\Interfaces\SellerInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class SellerRepository extends RepositoriesAbstract implements SellerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getSeller($perPage = 12, $active = true, array $with = [])
    {
        $data = $this->model
            ->where([
                'tiki_sellers.status'      => BaseStatusEnum::PUBLISHED,
            ])
            ->with($with)
            ->orderBy('tiki_sellers.created_at', 'desc');

        return $this->applyBeforeExecuteQuery($data)->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function searchSeller($query, $limit = 10, $paginate = 10)
    {
        $data = $this->model->where('tiki_sellers.status', BaseStatusEnum::PUBLISHED);
        foreach (explode(' ', $query) as $term) {
            $data = $data->where('tiki_sellers.seller_name', 'LIKE', '%' . $term . '%');
        }

        $data = $data->select('tiki_sellers.*')
            ->orderBy('tiki_sellers.created_at', 'desc');

        if ($limit) {
            $data = $data->limit($limit);
        }

        if ($paginate) {
            return $this->applyBeforeExecuteQuery($data)->paginate($paginate);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
