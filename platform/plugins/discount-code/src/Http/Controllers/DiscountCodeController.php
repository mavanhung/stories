<?php

namespace Botble\DiscountCode\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\DiscountCode\Http\Requests\DiscountCodeRequest;
use Botble\DiscountCode\Repositories\Interfaces\DiscountCodeInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\DiscountCode\Tables\DiscountCodeTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\DiscountCode\Forms\DiscountCodeForm;
use Botble\Base\Forms\FormBuilder;

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
        page_title()->setTitle(trans('plugins/discount-code::discount-code.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/discount-code::discount-code.create'));

        return $formBuilder->create(DiscountCodeForm::class)->renderForm();
    }

    /**
     * @param DiscountCodeRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(DiscountCodeRequest $request, BaseHttpResponse $response)
    {
        $discountCode = $this->discountCodeRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(DISCOUNT_CODE_MODULE_SCREEN_NAME, $request, $discountCode));

        return $response
            ->setPreviousUrl(route('discount-code.index'))
            ->setNextUrl(route('discount-code.edit', $discountCode->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $discountCode = $this->discountCodeRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $discountCode));

        page_title()->setTitle(trans('plugins/discount-code::discount-code.edit') . ' "' . $discountCode->name . '"');

        return $formBuilder->create(DiscountCodeForm::class, ['model' => $discountCode])->renderForm();
    }

    /**
     * @param int $id
     * @param DiscountCodeRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, DiscountCodeRequest $request, BaseHttpResponse $response)
    {
        $discountCode = $this->discountCodeRepository->findOrFail($id);

        $discountCode->fill($request->input());

        $discountCode = $this->discountCodeRepository->createOrUpdate($discountCode);

        event(new UpdatedContentEvent(DISCOUNT_CODE_MODULE_SCREEN_NAME, $request, $discountCode));

        return $response
            ->setPreviousUrl(route('discount-code.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param int $id
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $discountCode = $this->discountCodeRepository->findOrFail($id);

            $this->discountCodeRepository->delete($discountCode);

            event(new DeletedContentEvent(DISCOUNT_CODE_MODULE_SCREEN_NAME, $request, $discountCode));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     * @throws Exception
     */
    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $discountCode = $this->discountCodeRepository->findOrFail($id);
            $this->discountCodeRepository->delete($discountCode);
            event(new DeletedContentEvent(DISCOUNT_CODE_MODULE_SCREEN_NAME, $request, $discountCode));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
