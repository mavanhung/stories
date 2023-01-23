<?php

namespace Botble\Tiki\Tables;

use Html;
use BaseHelper;
use RvMedia;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Contracts\Routing\UrlGenerator;
use Botble\Tiki\Repositories\Interfaces\SellerInterface;
use Botble\Tiki\Models\Seller;

class SellerTable extends TableAbstract
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
     * @param SellerInterface $sellerRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, SellerInterface $sellerRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $sellerRepository;

        if (!Auth::user()->hasAnyPermission(['tiki_seller.edit', 'tiki_seller.destroy'])) {
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
            ->editColumn('seller_name', function ($item) {
                return $item->seller_name;
            })
            ->editColumn('logo', function ($item) {
                return Html::image(RvMedia::getImageUrl($item->logo, null, false, RvMedia::getDefaultImage()),
                    $item->seller_name, ['width' => 50]);
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
                return $this->getOperations(null, 'tiki_seller.destroy', $item);
            });

        // return $this->toJson($data);
        return apply_filters(BASE_FILTER_GET_LIST_DATA, $data, $this->repository->getModel())
        ->addColumn('operations', function ($item) {
            return $this->getOperations(null, 'tiki_seller.destroy', $item);
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
               'seller_name',
               'logo',
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
                'title' => trans('plugins/tiki::seller.seller_id'),
                'class' => 'text-start',
                'width' => '100px',
            ],
            'seller_name' => [
                'title' => trans('plugins/tiki::seller.seller_name'),
                'class' => 'text-start',
                'width' => '150px',
            ],
            'logo'      => [
                'title' => trans('plugins/tiki::seller.logo'),
                'width' => '70px',
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
    //     return $this->addCreateButton(route('tiki_seller.create'), 'tiki_seller.create');
    // }

    /**
     * {@inheritDoc}
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('tiki_seller.deletes'), 'tiki_seller.destroy', parent::bulkActions());
    }

    /**
     * {@inheritDoc}
     */
    public function getBulkChanges(): array
    {
        return [
            'seller_id' => [
                'title'    => trans('plugins/tiki::seller.seller_id'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'seller_name' => [
                'title'    => trans('plugins/tiki::seller.seller_name'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'status' => [
                'title'    => trans('core/base::tables.status'),
                'type'     => 'select',
                'choices'  => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
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
