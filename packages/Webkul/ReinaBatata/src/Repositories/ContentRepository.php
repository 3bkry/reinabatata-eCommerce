<?php

namespace Webkul\ReinaBatata\Repositories;

use Illuminate\Container\Container as App;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Event;
use Webkul\Product\Repositories\ProductRepository;

class ContentRepository extends Repository
{
   /**
    * Product Repository object
    *
    * @var \Webkul\Product\Repositories\ProductRepository
    */
    protected $productRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Product\Repositories\ProductRepository $productRepository
     * @param  \Illuminate\Container\Container  $app
     * @return void
     */
    public function __construct(
        ProductRepository $productRepository,
        App $app
    )
    {
        $this->productRepository = $productRepository;

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return 'Webkul\ReinaBatata\Contracts\Content';
    }

    /**
     * @param  array  $data
     * @return \Webkul\ReinaBatata\Models\Content
     */
    public function create(array $data)
    {
        // Event::fire('reinabatata.content.create.before');

        if (isset($data['locale']) && $data['locale'] == 'all') {
            $model = app()->make($this->model());

            foreach (core()->getAllLocales() as $locale) {
                foreach ($model->translatedAttributes as $attribute) {
                    if (isset($data[$attribute])) {
                        $data[$locale->code][$attribute] = $data[$attribute];
                    }
                }
            }
        }

        $content = $this->model->create($data);

        // Event::fire('reinabatata.content.create.after', $content);

        return $content;
    }

    /**
     * @param  array  $data
     * @param  int  $id
     * @return \Webkul\ReinaBatata\Models\Content
     */
    public function update(array $data, $id)
    {
        $content = $this->find($id);

        // Event::fire('reinabatata.content.update.before', $id);

        $content->update($data);

        // Event::fire('reinabatata.content.update.after', $id);

        return $content;
    }

    /**
     * @param  int  $id
     * @return array
     */
    public function getProducts($id)
    {
        $results = [];

        $locale = request()->get('locale') ?: app()->getLocale();

        $content = $this->model->find($id);

        if ($content->content_type == 'product') {
            $contentLocale = $content->translate($locale);

            $products = json_decode($contentLocale->products, true);

            if (! empty($products)) {
                foreach ($products as $product_id) {
                    $product = $this->productRepository->find($product_id);

                    if (isset($product->id)) {
                        $results[] = [
                            'id'   => $product->id,
                            'name' => $product->name,
                        ];
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @return array
     */
    public function getAllContents()
    {
        $query = $this->model::orderBy('position', 'ASC');

        $contentCollection = $query
            ->select(
                'reinabatata_contents.content_type',
                'reinabatata_contents_translations.title as title',
                'reinabatata_contents_translations.page_link as page_link',
                'reinabatata_contents_translations.link_target as link_target'
            )
            ->where('reinabatata_contents.status', 1)
            ->leftJoin('reinabatata_contents_translations', 'reinabatata_contents.id', 'reinabatata_contents_translations.content_id')
            ->distinct('reinabatata_contents_translations.id')
            ->where('reinabatata_contents_translations.locale', app()->getLocale())
            ->limit(5)
            ->get();

        $formattedContent = [];

        foreach ($contentCollection as $content) {
            array_push($formattedContent, [
                'title'        => $content->title,
                'page_link'    => $content->page_link,
                'link_target'  => $content->link_target,
                'content_type' => $content->content_type,
            ]);
        }

        return $formattedContent;
    }
}