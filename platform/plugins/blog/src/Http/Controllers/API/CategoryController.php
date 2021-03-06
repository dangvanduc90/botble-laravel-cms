<?php

namespace Botble\Blog\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Blog\Http\Resources\CategoryResource;
use Botble\Blog\Http\Resources\ListCategoryResource;
use Botble\Blog\Repositories\Interfaces\CategoryInterface;
use Botble\Blog\Supports\FilterCategory;
use Botble\Slug\Repositories\Interfaces\SlugInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Botble\Blog\Models\Category;

class CategoryController extends Controller
{
    /**
     * @var CategoryInterface
     */
    protected $categoryRepository;

    /**
     * @var SlugInterface
     */
    protected $slugRepository;

    /**
     * AuthenticationController constructor.
     *
     * @param CategoryInterface $categoryRepository
     */
    public function __construct(CategoryInterface $categoryRepository, SlugInterface $slugRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->slugRepository = $slugRepository;
    }

    /**
     * List categories
     *
     * @group Blog
     *
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function index(Request $request, BaseHttpResponse $response)
    {
        $data = $this->categoryRepository
            ->getModel()
            ->where(['status' => BaseStatusEnum::PUBLISHED])
            ->select(['id', 'name', 'description'])
            ->paginate($request->input('per_page', 10));

        return $response
            ->setData(ListCategoryResource::collection($data))
            ->toApiResponse();
    }

    /**
     * Filters categories
     *
     * @group Blog
     *
     * @param Request $request
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse
     */
    public function getFilters(Request $request, BaseHttpResponse $response)
    {
        $filters = FilterCategory::setFilters($request->input());
        $data = $this->categoryRepository->getFilters($filters);
        return $response
            ->setData(CategoryResource::collection($data))
            ->toApiResponse();
    }

    /**
     * Get category by slug
     *
     * @group Blog
     * @queryParam slug Find by slug of category.
     * @param string $slug
     * @param BaseHttpResponse $response
     * @return BaseHttpResponse|JsonResponse
     */
    public function findBySlug(string $slug, BaseHttpResponse $response)
    {
        $slug = $this->slugRepository->getFirstBy(['key' => $slug, 'reference_type' => Category::class]);
        if (!$slug) {
            return $response->setError()->setCode(404)->setMessage('Not found');
        }

        $category = $this->categoryRepository->getCategoryById($slug->reference_id);

        if (!$category) {
            return $response->setError()->setCode(404)->setMessage('Not found');
        }

        return $response
            ->setData(new ListCategoryResource($category))
            ->toApiResponse();
    }
}
