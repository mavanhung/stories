<?php

namespace Botble\Tiki\Http\Controllers;

use Illuminate\Http\Request;
use Botble\Tiki\Tables\SellerTable;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Tiki\Repositories\Interfaces\SellerInterface;

class SellerController extends BaseController
{
    /**
     * @var SellerInterface
     */
    protected $sellerRepository;

    /**
     * @param SellerInterface $sellerRepository
     */
    public function __construct(SellerInterface $sellerRepository)
    {
        $this->sellerRepository = $sellerRepository;
    }

    /**
     * @param SellerTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(SellerTable $table)
    {
        page_title()->setTitle(trans('plugins/tiki::seller.menu_name'));

        return $table->renderTable();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return BaseHttpResponse
     */
    public function destroy($id, Request $request, BaseHttpResponse $response)
    {
        try {
            $seller = $this->sellerRepository->findOrFail($id);
            $this->sellerRepository->delete($seller);

            event(new DeletedContentEvent(POST_MODULE_SCREEN_NAME, $request, $seller));

            return $response
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
