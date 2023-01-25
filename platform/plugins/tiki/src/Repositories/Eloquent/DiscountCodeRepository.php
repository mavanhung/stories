<?php

namespace Botble\Tiki\Repositories\Eloquent;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;
use Botble\Support\Repositories\Eloquent\RepositoriesAbstract;

class DiscountCodeRepository extends RepositoriesAbstract implements DiscountCodeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDiscountCode($perPage = 12, $active = true, array $with = [])
    {
        $data = $this->model
            ->where([
                'tiki_discount_codes.status'      => BaseStatusEnum::PUBLISHED,
                // 'tiki_discount_codes.is_crawler_home' => 1,
            ])
            ->where('expired_at', '>', now())
            ->with(array_merge(['seller'], $with))
            ->orderBy('tiki_discount_codes.is_crawler_home', 'desc')
            ->orderBy('tiki_discount_codes.created_at', 'desc');

        return $this->applyBeforeExecuteQuery($data)->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function getSearch($query, $sellerId, $limit = 10, $paginate = 10)
    {
        $data = $this->model->where('status', BaseStatusEnum::PUBLISHED)
                            ->where('expired_at', '>', now())
                            ->where(function($q) use ($query) {
                                $q->where('label', 'LIKE', '%' . $query . '%')
                                  ->orWhere('tiki_discount_codes.long_description', 'LIKE', '%' . $query . '%')
                                  ->orWhereHas('seller', function ($q) use ($query) {
                                    $q->where('seller_name', 'LIKE', '%' . $query . '%');
                                  });
                            });

        if(isset($sellerId)) {
            $data = $data->where('tiki_discount_codes.seller_id', $sellerId);
        }

        $data = $data->select('tiki_discount_codes.*')
            ->orderBy('tiki_discount_codes.created_at', 'desc');

        if ($limit) {
            $data = $data->limit($limit);
        }

        if ($paginate) {
            return $this->applyBeforeExecuteQuery($data)->paginate($paginate);
        }

        return $this->applyBeforeExecuteQuery($data)->get();
    }
}
