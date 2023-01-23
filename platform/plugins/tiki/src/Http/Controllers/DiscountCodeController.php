<?php

namespace Botble\Tiki\Http\Controllers;

use Illuminate\Http\Request;
use Botble\Tiki\Tables\DiscountCodeTable;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;

class DiscountCodeController extends BaseController
{
    /**
     * @var DiscountCodeInterface
     */
    protected $discountCodeRepository;

    /**
     * @param DiscountCodeInterface $discountCodeRepository
     */
    public function __construct(DiscountCodeInterface $discountCodeRepository)
    {
        $this->discountCodeRepository = $discountCodeRepository;
    }

    /**
     * @param DiscountCodeTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(DiscountCodeTable $table)
    {
        page_title()->setTitle(trans('plugins/tiki::discountcode.menu_name'));

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
            $discountCode = $this->discountCodeRepository->findOrFail($id);
            $this->discountCodeRepository->delete($discountCode);

            event(new DeletedContentEvent(POST_MODULE_SCREEN_NAME, $request, $discountCode));

            return $response
                ->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
