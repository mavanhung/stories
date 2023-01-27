<?php

namespace Botble\Tiki\Http\Controllers;

use Theme;
use Response;
use SeoHelper;
use SlugHelper;
use Illuminate\Http\Request;
use Botble\Tiki\Models\Seller;
use Illuminate\Routing\Controller;
use Botble\Tiki\Http\Resources\SellerResource;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Tiki\Repositories\Interfaces\SellerInterface;
use Botble\Tiki\Repositories\Interfaces\DiscountCodeInterface;

class PublicController extends Controller
{
    /**
     * @param Request $request
     * @param DiscountCodeInterface $discountCodeRepository
     * @return Response
     */
    // public function getIndex(Request $request, DiscountCodeInterface $discountCodeRepository, SellerInterface $sellerRepository)
    // {
    //     SeoHelper::setTitle('Mã giảm giá Tiki')
    //         ->setDescription('Mã giảm giá Tiki');

    //     $qs = $request->input('qs');
    //     $sellerId = $request->input('seller');
    //     $seller = '';
    //     if(isset($sellerId)) {
    //         $seller = Seller::where('seller_id', $sellerId)->select('seller_id', 'seller_name', 'logo')->first();
    //         if(!blank($seller)) {
    //             $seller = json_encode(new SellerResource($seller));
    //         }
    //     }
    //     if(isset($qs) || isset($sellerId)) {
    //         $discountCodes = $discountCodeRepository->getSearch($qs, $sellerId, 10, 12);
    //     }else {
    //         $discountCodes = $discountCodeRepository->getDiscountCode(12);
    //     }

    //     Theme::breadcrumb()
    //         ->add(__('Home'), url('/'))
    //         ->add(__('Mã giảm giá Tiki'), route('public.index'));

    //     return Theme::layout('discount-code')->scope('tiki-discount-code', compact('discountCodes', 'seller'))
    //         ->render();
    // }

    /**
     * @param Request $request
     * @param SellerInterface $sellerRepository
     * @return Response
     */
    public function searchSeller(Request $request, SellerInterface $sellerRepository)
    {
        $query = $request->input('q');
        $sellers = $sellerRepository->searchSeller($query, 0, 10);

        return response()->json([
            'incomplete_results' => true,
            'items' => SellerResource::collection($sellers),
            'total_count' => $sellers->total()
        ]);
    }
}
