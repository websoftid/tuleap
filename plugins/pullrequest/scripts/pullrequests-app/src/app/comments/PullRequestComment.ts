/*
 * Copyright (c) Enalean, 2022 - present. All Rights Reserved.
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

import { define, html } from "hybrids";
import type { UpdateFunction } from "hybrids";
import { getCommentBody } from "./PullRequestCommentBodyTemplate";
import { getCommentFooter } from "./PullRequestCommentFooterTemplate";
import type { IRelativeDateHelper } from "../helpers/date-helpers";
import { PullRequestCommentController } from "./PullRequestCommentController";
import type { ControlPullRequestComment } from "./PullRequestCommentController";
import { getReplyFormTemplate } from "./PullRequestCommentReplyFormTemplate";
import { PullRequestCommentReplyFormFocusHelper } from "./PullRequestCommentReplyFormFocusHelper";
import type { PullRequestCommentPresenter } from "./PullRequestCommentPresenter";
import type { CurrentPullRequestUserPresenter } from "./PullRequestCurrentUserPresenter";

export const TAG_NAME = "tuleap-pullrequest-comment";
export type HostElement = PullRequestComment & HTMLElement;

type MapOfClasses = Record<string, boolean>;

export interface PullRequestComment {
    readonly comment: PullRequestCommentPresenter;
    readonly content: () => HTMLElement;
    readonly post_rendering_callback: () => void;
    readonly relativeDateHelper: IRelativeDateHelper;
    readonly currentUser: CurrentPullRequestUserPresenter;
    readonly controller: ControlPullRequestComment;
    is_reply_form_displayed: boolean;
}

const getCommentClasses = (host: PullRequestComment): MapOfClasses => {
    const classes: MapOfClasses = {
        "pull-request-comment": true,
        "is-outdated": host.comment.is_outdated,
        "is-inline-comment": host.comment.is_inline_comment,
    };

    classes[host.comment.type] = true;

    return classes;
};

function renderFactory(fn: (host: HostElement) => UpdateFunction<PullRequestComment>) {
    return (host: HostElement): UpdateFunction<PullRequestComment> => {
        const component = fn(host);
        if (host.post_rendering_callback) {
            // Wait for component to be returned to trigger the callback
            setTimeout(() => host.post_rendering_callback());
        }

        return component;
    };
}

export const PullRequestComment = define<PullRequestComment>({
    tag: TAG_NAME,
    comment: undefined,
    post_rendering_callback: undefined,
    relativeDateHelper: undefined,
    currentUser: undefined,
    controller: {
        get: (host, value) =>
            value ?? PullRequestCommentController(PullRequestCommentReplyFormFocusHelper()),
        set: (host, value) => value,
    },
    is_reply_form_displayed: false,
    content: renderFactory(
        (host) => html`
            <div class="pull-request-comment-component">
                <div class="${getCommentClasses(host)}" data-test="pullrequest-comment">
                    <div class="tlp-avatar">
                        <img
                            src="${host.comment.user.avatar_url}"
                            class="media-object"
                            aria-hidden="true"
                        />
                    </div>

                    <div class="pull-request-comment-content">
                        ${getCommentBody(host)} ${getCommentFooter(host)}
                    </div>
                </div>

                <div class="pull-request-comment-follow-ups">${getReplyFormTemplate(host)}</div>
            </div>
        `
    ),
});