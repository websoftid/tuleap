/*
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import "../themes/style.scss";

export * from "./types";

export {
    PullRequestCommentComponent,
    PULL_REQUEST_COMMENT_ELEMENT_TAG_NAME,
} from "./comment/PullRequestComment";
export { PullRequestCommentReplyFormFocusHelper } from "./comment/PullRequestCommentReplyFormFocusHelper";
export { PullRequestCommentNewReplySaver } from "./comment/PullRequestCommentReplySaver";
export { PullRequestCommentController } from "./comment/PullRequestCommentController";
export { PullRequestCommentRepliesStore } from "./comment/PullRequestCommentRepliesStore";

export type {
    PullRequestInlineCommentPresenter,
    PullRequestCommentPresenter,
    PullRequestGlobalCommentPresenter,
    PullRequestCommentFile,
} from "./comment/PullRequestCommentPresenter";

export type { ControlPullRequestComment } from "./comment/PullRequestCommentController";
export type { StorePullRequestCommentReplies } from "./comment/PullRequestCommentRepliesStore";
export type { CurrentPullRequestUserPresenter } from "./comment/PullRequestCurrentUserPresenter";
export type { PullRequestPresenter } from "./comment/PullRequestPresenter";

export {
    PullRequestCommentSkeletonComponent,
    PULL_REQUEST_COMMENT_SKELETON_ELEMENT_TAG_NAME,
} from "./skeleton/PullRequestCommentSkeleton";
