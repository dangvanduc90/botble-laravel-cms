<?php

namespace Botble\Media\Http\Controllers;

use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Botble\Media\Http\Requests\MediaFileRequest;
use Botble\Media\Repositories\Interfaces\MediaFileInterface;
use Botble\Media\Repositories\Interfaces\MediaFolderInterface;
use Botble\Media\Services\UploadsManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use RvMedia;
use Validator;

/**
 * @since 19/08/2015 07:50 AM
 */
class MediaFileController extends Controller
{
    /**
     * @var UploadsManager
     */
    protected $uploadManager;

    /**
     * @var MediaFileInterface
     */
    protected $fileRepository;

    /**
     * @var MediaFolderInterface
     */
    protected $folderRepository;

    /**
     * @param MediaFileInterface $fileRepository
     * @param MediaFolderInterface $folderRepository
     * @param UploadsManager $uploadManager
     */
    public function __construct(
        MediaFileInterface $fileRepository,
        MediaFolderInterface $folderRepository,
        UploadsManager $uploadManager
    ) {
        $this->fileRepository = $fileRepository;
        $this->folderRepository = $folderRepository;
        $this->uploadManager = $uploadManager;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function postAddExternalService(Request $request)
    {
        $type = $request->input('type');
        if (!in_array($type, config('core.media.media.external_services'))) {
            return RvMedia::responseError(trans('core/media::media.invalid_request'));
        }

        $file = $this->fileRepository->getModel();
        $file->name = $this->fileRepository->createName($request->input('name'), $request->input('folder_id'));
        $file->url = $request->input('url');
        $file->size = 0;
        $file->mime_type = $type;
        $file->folder_id = $request->input('folder_id');
        $file->user_id = Auth::user()->getKey();
        $file->options = $request->input('options', []);
        $this->fileRepository->createOrUpdate($file);

        return RvMedia::responseSuccess(trans('core/media::media.add_success'));
    }

    /**
     * @param MediaFileRequest $request
     * @return JsonResponse
     * @throws FileNotFoundException
     */
    public function postUpload(MediaFileRequest $request)
    {
        $result = RvMedia::handleUpload(Arr::first($request->file('file')), $request->input('folder_id', 0));

        if ($result['error'] == false) {
            return RvMedia::responseSuccess([
                'id'  => $result['data']->id,
                'src' => $result['data']->url,
            ]);
        }

        return RvMedia::responseError($result['message']);
    }

    /**
     * @param Request $request
     * @return ResponseFactory|JsonResponse|Response
     * @throws FileNotFoundException
     */
    public function postUploadFromEditor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload' => 'required|image|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return response('<script>alert("' . trans('core/media::media.can_not_detect_file_type') . '")</script>')
                ->header('Content-Type', 'text/html');
        }

        $result = RvMedia::handleUpload($request->file('upload'), 0, $request->input('upload_type'));

        if ($result['error'] == false) {
            $file = $result['data'];
            if ($request->input('upload_type') == 'tinymce') {
                return response('<script>parent.setImageValue("' . url($file->url) . '"); </script>')->header('Content-Type',
                    'text/html');
            }

            if (!$request->input('CKEditorFuncNum')) {
                return response()->json([
                    'fileName' => File::name(RvMedia::url($file->url)),
                    'uploaded' => 1,
                    'url'      => RvMedia::url($file->url),
                ]);
            }

            return response('<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction("' . $request->input('CKEditorFuncNum') . '", "' . RvMedia::url($file->url) . '", "");</script>')
                ->header('Content-Type', 'text/html');
        }

        return response('<script>alert("' . Arr::get($result, 'message') . '")</script>')
            ->header('Content-Type', 'text/html');
    }
}
