<?php

/**
 * Copyright (c) 2017-present, DocsPen.
 * All rights reserved.
 *
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace DocsPen\Http\Controllers;

use Activity;
use Illuminate\Http\Request;
use DocsPen\Repos\EntityRepo;
use DocsPen\Repos\CommentRepo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommentController extends Controller
{
    protected $entityRepo;
    protected $commentRepo;

    /**
     * CommentController constructor.
     *
     * @param EntityRepo  $entityRepo
     * @param CommentRepo $commentRepo
     */
    public function __construct(EntityRepo $entityRepo, CommentRepo $commentRepo)
    {
        $this->entityRepo = $entityRepo;
        $this->commentRepo = $commentRepo;
        parent::__construct();
    }

    /**
     * Save a new comment for a Page.
     *
     * @param Request  $request
     * @param int      $pageId
     * @param null|int $commentId
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function savePageComment(Request $request, $pageId, $commentId = null)
    {
        $this->validate(
            $request,
            [
            'text' => 'required|string',
            'html' => 'required|string',
            ]
        );

        try {
            $page = $this->entityRepo->getById('page', $pageId, true);
        } catch (ModelNotFoundException $e) {
            return response('Not found', 404);
        }

        $this->checkOwnablePermission('page-view', $page);

        // Prevent adding comments to draft pages
        if ($page->draft) {
            return $this->jsonError(trans('errors.cannot_add_comment_to_draft'), 400);
        }

        // Create a new comment.
        $this->checkPermission('comment-create-all');
        $comment = $this->commentRepo->create($page, $request->only(['html', 'text', 'parent_id']));
        Activity::add($page, 'commented_on', $page->book->id);

        return view('comments/comment', ['comment' => $comment]);
    }

    /**
     * Update an existing comment.
     *
     * @param Request $request
     * @param int     $commentId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function update(Request $request, $commentId)
    {
        $this->validate(
            $request,
            [
            'text' => 'required|string',
            'html' => 'required|string',
            ]
        );

        $comment = $this->commentRepo->getById($commentId);
        $this->checkOwnablePermission('page-view', $comment->entity);
        $this->checkOwnablePermission('comment-update', $comment);

        $comment = $this->commentRepo->update($comment, $request->only(['html', 'text']));

        return view('comments/comment', ['comment' => $comment]);
    }

    /**
     * Delete a comment from the system.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $comment = $this->commentRepo->getById($id);
        $this->checkOwnablePermission('comment-delete', $comment);
        $this->commentRepo->delete($comment);

        return response()->json(['message' => trans('entities.comment_deleted')]);
    }
}
