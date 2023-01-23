<?php

namespace Botble\Tiki\Http\Controllers;

use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Response;
use SeoHelper;
use SlugHelper;
use Theme;

class PublicController extends Controller
{
    /**
     * @param Request $request
     * @param DiscountCodeInterface $discountCodeRepository
     * @return Response
     */
    public function getIndex(Request $request, DiscountCodeInterface $discountCodeRepository)
    {
        SeoHelper::setTitle('Mã giảm giá Tiki')
            ->setDescription('Mã giảm giá Tiki');

        $discountCodes = $discountCodeRepository->getDiscountCode(30);

        Theme::breadcrumb()
            ->add(__('Home'), url('/'))
            ->add(__('Mã giảm giá Tiki'), route('public.index'));

        return Theme::scope('tiki-discount-code', compact('discountCodes'))
            ->render();
    }
}
