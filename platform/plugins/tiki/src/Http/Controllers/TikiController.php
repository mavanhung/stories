<?php

namespace Botble\Tiki\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Tiki\Http\Requests\TikiRequest;
use Botble\Tiki\Repositories\Interfaces\TikiInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Tiki\Tables\TikiTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Tiki\Forms\TikiForm;
use Botble\Base\Forms\FormBuilder;

class TikiController extends BaseController
{
    /**
     * @var TikiInterface
     */
    protected $tikiRepository;

    /**
     * @param TikiInterface $tikiRepository
     */
    public function __construct(TikiInterface $tikiRepository)
    {
        $this->tikiRepository = $tikiRepository;
    }

    /**
     * @param TikiTable $table
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(TikiTable $table)
    {
        page_title()->setTitle(trans('plugins/tiki::tiki.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/tiki::tiki.create'));

        return $formBuilder->create(TikiForm::class)->renderForm();
    }

    /**
     * @param TikiRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function store(TikiRequest $request, BaseHttpResponse $response)
    {
        $tiki = $this->tikiRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(TIKI_MODULE_SCREEN_NAME, $request, $tiki));

        return $response
            ->setPreviousUrl(route('tiki.index'))
            ->setNextUrl(route('tiki.edit', $tiki->id))
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
        $tiki = $this->tikiRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $tiki));

        page_title()->setTitle(trans('plugins/tiki::tiki.edit') . ' "' . $tiki->name . '"');

        return $formBuilder->create(TikiForm::class, ['model' => $tiki])->renderForm();
    }

    /**
     * @param int $id
     * @param TikiRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function update($id, TikiRequest $request, BaseHttpResponse $response)
    {
        $tiki = $this->tikiRepository->findOrFail($id);

        $tiki->fill($request->input());

        $tiki = $this->tikiRepository->createOrUpdate($tiki);

        event(new UpdatedContentEvent(TIKI_MODULE_SCREEN_NAME, $request, $tiki));

        return $response
            ->setPreviousUrl(route('tiki.index'))
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
            $tiki = $this->tikiRepository->findOrFail($id);

            $this->tikiRepository->delete($tiki);

            event(new DeletedContentEvent(TIKI_MODULE_SCREEN_NAME, $request, $tiki));

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
            $tiki = $this->tikiRepository->findOrFail($id);
            $this->tikiRepository->delete($tiki);
            event(new DeletedContentEvent(TIKI_MODULE_SCREEN_NAME, $request, $tiki));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
