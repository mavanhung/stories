<?php

namespace Botble\Comment\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CommentRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'posts_id'   => 'required|numeric',
            'name'   => 'required',
            'email'   => 'required|email',
            'phone'   => 'required|numeric',
            'star'   => 'required|numeric',
            'comment'   => 'required|max:120',
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
