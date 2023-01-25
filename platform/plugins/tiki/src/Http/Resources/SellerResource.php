<?php

namespace Botble\Tiki\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RvMedia;

class SellerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->seller_id,
            'seller_id'   => $this->seller_id,
            'seller_name' => $this->seller_name,
            'logo'       => $this->logo ? RvMedia::url($this->logo) : null,
        ];
    }
}
