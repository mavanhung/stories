<?php

namespace Botble\Comment\Tables;

use Illuminate\Support\Facades\Auth;
use BaseHelper;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Comment\Repositories\Interfaces\CommentInterface;
use Botble\Table\Abstracts\TableAbstract;
use Illuminate\Contracts\Routing\UrlGenerator;
use Yajra\DataTables\DataTables;
use Html;

class CommentTable extends TableAbstract
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
     * CommentTable constructor.
     * @param DataTables $table
     * @param UrlGenerator $urlGenerator
     * @param CommentInterface $commentRepository
     */
    public function __construct(DataTables $table, UrlGenerator $urlGenerator, CommentInterface $commentRepository)
    {
        parent::__construct($table, $urlGenerator);

        $this->repository = $commentRepository;

        if (!Auth::user()->hasAnyPermission(['comment.edit', 'comment.destroy'])) {
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
            ->editColumn('posts_id', function ($item) {
                return $item->posts_id;
            })
            ->editColumn('name', function ($item) {
                if (!Auth::user()->hasPermission('comment.edit')) {
                    return $item->name;
                }
                return Html::link(route('comment.edit', $item->id), $item->name);
            })
            ->editColumn('email', function ($item) {
                return $item->email;
            })
            ->editColumn('phone', function ($item) {
                return $item->phone;
            })
            // ->editColumn('star', function ($item) {
            //     return $item->star;
            // })
            ->editColumn('comment', function ($item) {
                return $item->comment;
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            });

        return apply_filters(BASE_FILTER_GET_LIST_DATA, $data, $this->repository->getModel())
        ->addColumn('operations', function ($item) {
            return $this->getOperations('comment.edit', 'comment.destroy', $item);
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
               'posts_id',
               'name',
               'email',
               'phone',
            //    'star',
               'comment',
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
            'posts_id' => [
                'title' => trans('plugins/comment::comment.post'),
                'class' => 'text-start',
            ],
            'name' => [
                'title' => trans('core/base::tables.name'),
                'class' => 'text-start',
            ],
            'email' => [
                'title' => trans('plugins/comment::comment.email'),
                'class' => 'text-start',
            ],
            'phone' => [
                'title' => trans('plugins/comment::comment.phone'),
                'class' => 'text-start',
            ],
            // 'star' => [
            //     'title' => trans('plugins/comment::comment.star'),
            //     'class' => 'text-start',
            // ],
            'comment' => [
                'title' => trans('plugins/comment::comment.comment'),
                'class' => 'text-start',
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
    public function buttons()
    {
        return $this->addCreateButton(route('comment.create'), 'comment.create');
    }

    /**
     * {@inheritDoc}
     */
    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('comment.deletes'), 'comment.destroy', parent::bulkActions());
    }

    /**
     * {@inheritDoc}
     */
    public function getBulkChanges(): array
    {
        return [
            'name' => [
                'title'    => trans('core/base::tables.name'),
                'type'     => 'text',
                'validate' => 'required|max:120',
            ],
            'email' => [
                'title'    => trans('plugins/comment::comment.email'),
                'type'     => 'text',
                'validate' => 'required|email|max:120',
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
