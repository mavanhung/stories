<?php

namespace Botble\Tiki\Tables;

use Illuminate\Support\Facades\Auth;
use BaseHelper;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Contracts\Routing\UrlGenerator;
use Yajra\DataTables\DataTables;
use Html;

class DiscountCodeTable extends TableAbstract
{

    /**
     * @var bool
     */
    protected $hasActions = true;

    /**
     * @var bool
     */
    protected $hasFilter = true;

    /**
     * TikiTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param DiscountCodeInterface $discountCodeRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, DiscountCodeInterface $discountCodeRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $discountCodeRepository;

        if (!Auth::user()->hasAnyPermission(['tiki_discount_code.edit', 'tiki_discount_code.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function ajax()
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('seller_id', function ($item) {
                return $item->seller_id;
            })
            ->editColumn('seller_id', function ($item) {
                return $item->seller ? $item->seller->seller_name : 'Tiki';
            })
            ->editColumn('coupon_code', function ($item) {
                return $item->coupon_code;
            })
            ->editColumn('expired_at', function ($item) {
                return BaseHelper::formatDate($item->expired_at, 'd/m/Y H:i:s');
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at, 'd/m/Y H:i:s');
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                return $this->getOperations(null, 'tiki_discount_code.destroy', $item);
            });

        // return $this->toJson($data);
        return apply_filters(BASE_FILTER_GET_LIST_DATA, $data, $this->repository->getModel())
        ->addColumn('operations', function ($item) {
            return $this->getOperations(null, 'tiki_discount_code.destroy', $item);
        })
        ->escapeColumns([])
        ->make(true);
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        $query = $this->repository->getModel()
            ->select([
               'id',
               'seller_id',
               'coupon_code',
               'expired_at',
               'created_at',
               'status',
           ]);

        return $this->applyScopes($query);
    }

    /**
     * {@inheritDoc}
     */
    public function columns()
    {
        return [
            'id' => [
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'seller_id' => [
                'title' => trans('plugins/tiki::discountcode.seller_name'),
                'class' => 'text-start',
                'width' => '150px',
            ],
            'coupon_code' => [
                'title' => trans('plugins/tiki::discountcode.coupon_code'),
                'class' => 'text-start',
            ],
            'expired_at' => [
                'title' => trans('plugins/tiki::discountcode.expired_at'),
                'class' => 'text-start',
                'width' => '100px',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    // public function buttons()
    // {
    //     return $this->addCreateButton(route('tiki_discount_code.create'), 'tiki_discount_code.create');
    // }

    /**
     * {@inheritDoc}
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('tiki_discount_code.deletes'), 'tiki_discount_code.destroy', parent::bulkActions());
    }

    /**
     * {@inheritDoc}
     */
    public function getBulkChanges(): array
    {
        return [
            // 'name' => [
            //     'title'    => trans('core/base::tables.name'),
            //     'type'     => 'text',
            //     'validate' => 'required|max:120',
            // ],
            'seller_id' => [
                'title'    => trans('plugins/tiki::discountcode.seller_id'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title'    => trans('core/base::tables.status'),
                'type'     => 'select',
                'choices'  => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
            'expired_at' => [
                'title' => trans('plugins/tiki::discountcode.expired_at'),
                'type'  => 'date',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type'  => 'date',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->getBulkChanges();
    }
}
