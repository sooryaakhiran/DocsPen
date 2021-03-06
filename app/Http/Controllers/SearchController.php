<?php

/**
 * Copyright (c) 2017-present, DocsPen.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace DocsPen\Http\Controllers;

use Illuminate\Http\Request;
use DocsPen\Repos\EntityRepo;
use DocsPen\Services\ViewService;
use DocsPen\Services\SearchService;

class SearchController extends Controller
{
    protected $entityRepo;
    protected $viewService;
    protected $searchService;

    /**
     * SearchController constructor.
     *
     * @param EntityRepo    $entityRepo
     * @param ViewService   $viewService
     * @param SearchService $searchService
     */
    public function __construct(EntityRepo $entityRepo, ViewService $viewService, SearchService $searchService)
    {
        $this->entityRepo = $entityRepo;
        $this->viewService = $viewService;
        $this->searchService = $searchService;
        parent::__construct();
    }

    /**
     * Searches all entities.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     *
     * @internal param string $searchTerm
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('term');
        $this->setPageTitle(trans('entities.search_for_term', ['term' => $searchTerm]));

        $page = intval($request->get('page', '0')) ?: 1;
        $nextPageLink = baseUrl('/search?term='.urlencode($searchTerm).'&page='.($page + 1));

        $results = $this->searchService->searchEntities($searchTerm, 'all', $page, 20);
        $hasNextPage = $this->searchService->searchEntities($searchTerm, 'all', $page + 1, 20)['count'] > 0;

        if ($searchTerm === '') {
            abort(404);
        } else {
            return view(
                'search/all',
                [
                'entities' => $results['results'],
                'totalResults' => $results['total'],
                'searchTerm' => $searchTerm,
                'hasNextPage' => $hasNextPage,
                'nextPageLink' => $nextPageLink,
                ]
            );
        }
    }

    /**
     * Searches all entities within a book.
     *
     * @param Request $request
     * @param int     $bookId
     *
     * @return \Illuminate\View\View
     *
     * @internal param string $searchTerm
     */
    public function searchBook(Request $request, $bookId)
    {
        $term = $request->get('term', '');
        $results = $this->searchService->searchBook($bookId, $term);

        return view('partials/entity-list', ['entities' => $results]);
    }

    /**
     * Searches all entities within a chapter.
     *
     * @param Request $request
     * @param int     $chapterId
     *
     * @return \Illuminate\View\View
     *
     * @internal param string $searchTerm
     */
    public function searchChapter(Request $request, $chapterId)
    {
        $term = $request->get('term', '');
        $results = $this->searchService->searchChapter($chapterId, $term);

        return view('partials/entity-list', ['entities' => $results]);
    }

    /**
     * Search for a list of entities and return a partial HTML response of matching entities.
     * Returns the most popular entities if no search is provided.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function searchEntitiesAjax(Request $request)
    {
        $entityTypes = $request->filled('types') ? collect(explode(',', $request->get('types'))) : collect(['page', 'chapter', 'book']);
        $searchTerm = $request->get('term', false);

        // Search for entities otherwise show most popular
        if ($searchTerm !== false) {
            $searchTerm .= ' {type:'.implode('|', $entityTypes->toArray()).'}';
            $entities = $this->searchService->searchEntities($searchTerm)['results'];
        } else {
            $entityNames = $entityTypes->map(
                function ($type) {
                    return 'DocsPen\\'.ucfirst($type);
                }
            )->toArray();
            $entities = $this->viewService->getPopular(20, 0, $entityNames);
        }

        return view('search/entity-ajax-list', ['entities' => $entities]);
    }
}
