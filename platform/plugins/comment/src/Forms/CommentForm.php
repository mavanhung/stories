<?php

namespace Botble\Comment\Forms;

use Botble\Blog\Models\Post;
use Botble\Comment\Models\Comment;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Comment\Http\Requests\CommentRequest;

class CommentForm extends FormAbstract
{

    /**
     * {@inheritDoc}
     */
    public function buildForm()
    {
        $this
            ->setupModel(new Comment)
            ->setValidatorClass(CommentRequest::class)
            ->withCustomFields()
            ->add('posts_id', 'text', [
                'label'      => trans('plugins/comment::comment.post'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/comment::comment.post_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('name', 'text', [
                'label'      => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('email', 'text', [
                'label'      => trans('plugins/comment::comment.email'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/comment::comment.email_placeholder'),
                    'data-counter' => 60,
                ],
            ])
            ->add('phone', 'text', [
                'label'      => trans('plugins/comment::comment.phone'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/comment::comment.phone_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('star', 'select', [
                'label'      => trans('plugins/comment::comment.star'),
                'label_attr' => ['class' => 'control-label required'],
                'choices'    => [
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ],
            ])
            ->add('comment', 'text', [
                'label'      => trans('plugins/comment::comment.comment'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('plugins/comment::comment.comment_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('images[]', 'mediaImages', [
                'label'      => trans('plugins/comment::comment.image'),
                'label_attr' => ['class' => 'control-label'],
                'values'     => $this->model ? json_decode($this->model->images, true) : [],
            ])
            ->add('status', 'customSelect', [
                'label'      => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'class' => 'form-control select-full',
                ],
                'choices'    => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
