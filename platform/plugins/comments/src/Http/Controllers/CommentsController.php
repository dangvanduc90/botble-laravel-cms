<?php

namespace Botble\Comments\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Comments\Http\Requests\CommentsRequest;
use Botble\Comments\Repositories\Interfaces\CommentsInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Comments\Tables\CommentsTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Comments\Forms\CommentsForm;
use Botble\Base\Forms\FormBuilder;

class CommentsController extends BaseController
{
    /**
     * @var CommentsInterface
     */
    protected $commentsRepository;

    /**
     * CommentsController constructor.
     * @param CommentsInterface $commentsRepository
     */
    public function __construct(CommentsInterface $commentsRepository)
    {
        $this->commentsRepository = $commentsRepository;
    }

    /**
     * @param CommentsTable $dataTable
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index(CommentsTable $table)
    {
        page_title()->setTitle(trans('plugins/comments::comments.name'));

        return $table->renderTable();
    }

    /**
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/comments::comments.create'));

        return $formBuilder->create(CommentsForm::class)->renderForm();
    }

    /**
     * Create new item
     *
     * @param CommentsRequest $request
     * @return BaseHttpResponse
     */
    public function store(CommentsRequest $request, BaseHttpResponse $response)
    {
        $comments = $this->commentsRepository->createOrUpdate($request->input());

        event(new CreatedContentEvent(COMMENTS_MODULE_SCREEN_NAME, $request, $comments));

        return $response
            ->setPreviousUrl(route('comments.index'))
            ->setNextUrl(route('comments.edit', $comments->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    /**
     * Show edit form
     *
     * @param $id
     * @param Request $request
     * @param FormBuilder $formBuilder
     * @return string
     */
    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $comments = $this->commentsRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $comments));

        page_title()->setTitle(trans('plugins/comments::comments.edit') . ' "' . $comments->name . '"');

        return $formBuilder->create(CommentsForm::class, ['model' => $comments])->renderForm();
    }

    /**
     * @param $id
     * @param CommentsRequest $request
     * @return BaseHttpResponse
     */
    public function update($id, CommentsRequest $request, BaseHttpResponse $response)
    {
        $comments = $this->commentsRepository->findOrFail($id);

        $comments->fill($request->input());

        $this->commentsRepository->createOrUpdate($comments);

        event(new UpdatedContentEvent(COMMENTS_MODULE_SCREEN_NAME, $request, $comments));

        return $response
            ->setPreviousUrl(route('comments.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return BaseHttpResponse
     */
    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $comments = $this->commentsRepository->findOrFail($id);

            $this->commentsRepository->delete($comments);

            event(new DeletedContentEvent(COMMENTS_MODULE_SCREEN_NAME, $request, $comments));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.cannot_delete'));
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
            $comments = $this->commentsRepository->findOrFail($id);
            $this->commentsRepository->delete($comments);
            event(new DeletedContentEvent(COMMENTS_MODULE_SCREEN_NAME, $request, $comments));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
