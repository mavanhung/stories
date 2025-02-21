<?php

namespace Theme\Stories\Http\Controllers;

use App\Helpers\Functions;
use Theme;
use RvMedia;
use Illuminate\Http\Request;
use Botble\Comment\Models\Comment;
use Botble\Base\Enums\BaseStatusEnum;
use Illuminate\Support\Facades\Storage;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Comment\Http\Requests\CommentRequest;
use Botble\Comment\Http\Resources\PaginateResource;
use Botble\Theme\Http\Controllers\PublicController;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Botble\Comment\Repositories\Interfaces\CommentInterface;
use Log;

class StoriesController extends PublicController
{
    use Functions;
    /**
     * @var CommentInterface
     */
    protected $commentRepository;

    protected $postRepository;

    /**
     * @param CommentInterface $commentRepository
     */
    public function __construct(CommentInterface $commentRepository, PostInterface $postRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function ajaxGetPanelInner(Request $request, BaseHttpResponse $response)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        return $response->setData(Theme::partial('components.panel-inner'));
    }

    /**
     * @param CommentRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function ajaxGetComment(Request $request, BaseHttpResponse $response, $id) {
        $comments = Comment::Where('posts_id', $id)
                            ->whereStatus(BaseStatusEnum::PUBLISHED)
                            ->orderBy('created_at', 'DESC')
                            ->paginate(5)
                            ->appends($request->query());
        return $response->setData(Theme::partial('components.comment-item', compact('comments')));
    }

    /**
     * @param CommentRequest $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function ajaxPostComment(CommentRequest $request, BaseHttpResponse $response) {
        try {
            $exists = $this->postRepository->count([
                'id'  => $request->input('posts_id'),
            ]);
            if ($exists <= 0) {
                return $response
                    ->setError()
                    ->setMessage('Bài viết không tồn tại.');
            }

            //Lưu hình ảnh ở storage
            $results = [];
            if ($request->hasFile('images')) {
                $images = (array)$request->file('images', []);
                foreach ($images as $image) {
                    $result = RvMedia::handleUpload($image, 0, 'comments');
                    if ($result['error'] != false) {
                        return $response->setError()->setMessage($result['message']);
                    }
                    $results[] = $result;
                }
            }

            $request->merge([
                'images' => $results ? json_encode(array_filter(collect($results)->pluck('data.url')->values()->toArray())) : null,
            ]);

            //Lưu hình ảnh ở s3
            // $results = [];
            // if ($request->hasFile('images')) {
            //     $images = (array)$request->file('images', []);
            //     foreach ($images as $image) {
            //         $path = Storage::disk('s3')->put('comments', $image);
            //         $path = Storage::disk('s3')->url($path);
            //         $results[] = $path;
            //     }
            // }

            // $request->merge([
            //     'images' => $results ? json_encode($results) : null,
            // ]);

            $comment = $this->commentRepository->createOrUpdate($request->input());

            return $response->setData(Theme::partial('components.comment-single', compact('comment')));
        } catch (\Throwable $th) {
            $message = "Có lỗi xảy ra comment: " . $th->getMessage() . ", dòng: " . $th->getLine() . ", file: " . $th->getFile();
            $this->sendNotificationTelegram($message);
            Log::error("Có lỗi xảy ra: {$th->getMessage()}, dòng: {$th->getLine()}, file: {$th->getFile()}");
            return $response
                    ->setError()
                    ->setMessage('Đã có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
