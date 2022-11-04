/*
 * Copyright (c) Enalean, 2018 - Present. All Rights Reserved.
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

import CodeMirror from "codemirror";
import "codemirror/addon/scroll/simplescrollbars.js";
import { getComments } from "../comments-state.js";
import {
    initDataAndCodeMirrors,
    isFirstLineOfGroup,
    getCommentLine,
    getLineHandles,
    getRightLine,
    getLeftLine,
} from "./side-by-side-lines-state.js";
import { buildCodePlaceholderWidget } from "./side-by-side-code-placeholder-builder.js";
import { buildCommentsPlaceholderWidget } from "./side-by-side-comment-placeholder-builder.js";
import { synchronize } from "./side-by-side-scroll-synchronizer.js";
import { getCollapsibleSectionsSideBySide } from "../../code-collapse/code-collapse-service.js";
import { equalizeSides } from "./side-by-side-line-height-equalizer.js";

import { POSITION_LEFT, POSITION_RIGHT } from "../inline-comment-positions.js";

import "./modes.js";
import { getCodeMirrorConfigurationToMakePotentiallyDangerousBidirectionalCharactersVisible } from "../diff-bidirectional-unicode-text";

export default {
    template: `
        <div class="pull-request-side-by-side-diff" resize></div>
        <div class="pull-request-side-by-side-diff" resize></div>
    `,
    controller,
    bindings: {
        diff: "<",
        filePath: "@",
        pullRequestId: "@",
    },
};

controller.$inject = ["$element", "$scope", "$q", "CodeMirrorHelperService", "TooltipService"];

function controller($element, $scope, $q, CodeMirrorHelperService, TooltipService) {
    const self = this;
    self.$onInit = init;

    function init() {
        const [left_element, right_element] = $element[0].querySelectorAll(
            ".pull-request-side-by-side-diff"
        );
        const options =
            getCodeMirrorConfigurationToMakePotentiallyDangerousBidirectionalCharactersVisible({
                readOnly: true,
                lineWrapping: true,
                gutters: ["gutter-lines"],
                mode: self.diff.mime_type,
                scrollbarStyle: "overlay",
                viewportMargin: 20,
            });

        const left_code_mirror = CodeMirror(left_element, options);
        const right_code_mirror = CodeMirror(right_element, options);
        $scope.$broadcast("code_mirror_initialized");

        const file_lines = self.diff.lines;
        displaySideBySideDiff(file_lines, left_code_mirror, right_code_mirror);

        synchronize(left_code_mirror, right_code_mirror);

        const collapsible_sections = getCollapsibleSectionsSideBySide(file_lines, getComments());
        CodeMirrorHelperService.collapseCommonSectionsSideBySide(
            left_code_mirror,
            right_code_mirror,
            collapsible_sections
        );
    }

    function displaySideBySideDiff(file_lines, left_code_mirror, right_code_mirror) {
        initDataAndCodeMirrors(file_lines, left_code_mirror, right_code_mirror);

        const code_placeholders = file_lines.map((line) => {
            displayLine(line, left_code_mirror, right_code_mirror);
            return addCodePlaceholder(line, left_code_mirror, right_code_mirror);
        });

        code_placeholders.forEach((widget_params) => {
            if (!widget_params) {
                return;
            }
            CodeMirrorHelperService.displayPlaceholderWidget(widget_params);
        });

        file_lines.forEach((line) => {
            addCommentsPlaceholder(line, left_code_mirror, right_code_mirror);
        });

        getComments().forEach((comment) => {
            displayInlineComment(comment, left_code_mirror, right_code_mirror);
        });

        TooltipService.setupTooltips();

        handleCodeMirrorEvents(left_code_mirror, right_code_mirror);
    }

    function displayInlineComment(comment, left_code_mirror, right_code_mirror) {
        const comment_line = getCommentLine(comment);

        if (comment_line.new_offset === null) {
            CodeMirrorHelperService.displayInlineComment(
                left_code_mirror,
                comment,
                comment_line.old_offset - 1
            ).node.post_rendering_callback = () => {
                recomputeCommentPlaceholderHeight(
                    left_code_mirror,
                    right_code_mirror,
                    getLeftLine(comment_line.old_offset - 1)
                );
            };

            return addCommentsPlaceholder(comment_line, left_code_mirror, right_code_mirror);
        }

        if (comment_line.old_offset === null) {
            CodeMirrorHelperService.displayInlineComment(
                right_code_mirror,
                comment,
                comment_line.new_offset - 1
            ).node.post_rendering_callback = () => {
                recomputeCommentPlaceholderHeight(
                    left_code_mirror,
                    right_code_mirror,
                    getRightLine(comment_line.new_offset - 1)
                );
            };

            return addCommentsPlaceholder(comment_line, left_code_mirror, right_code_mirror);
        }

        const target_code_mirror =
            comment.position === POSITION_LEFT ? left_code_mirror : right_code_mirror;
        const line_number =
            comment.position === POSITION_LEFT
                ? comment_line.old_offset - 1
                : comment_line.new_offset - 1;

        CodeMirrorHelperService.displayInlineComment(
            target_code_mirror,
            comment,
            line_number
        ).node.post_rendering_callback = () => {
            recomputeCommentPlaceholderHeight(
                left_code_mirror,
                right_code_mirror,
                comment.position === POSITION_LEFT
                    ? getLeftLine(line_number)
                    : getRightLine(line_number)
            );
        };

        return addCommentsPlaceholder(comment_line, left_code_mirror, right_code_mirror);
    }

    function recomputeCommentPlaceholderHeight(left_code_mirror, right_code_mirror, line) {
        const placeholder_to_create = equalizeSides(
            left_code_mirror,
            right_code_mirror,
            getLineHandles(line)
        );

        if (placeholder_to_create) {
            CodeMirrorHelperService.displayPlaceholderWidget(placeholder_to_create);
        }
    }

    function handleCodeMirrorEvents(left_code_mirror, right_code_mirror) {
        left_code_mirror.on("lineWidgetAdded", (code_mirror, line_widget, line_number) => {
            recomputeCommentPlaceholderHeight(
                left_code_mirror,
                right_code_mirror,
                getLeftLine(line_number)
            );
        });
        right_code_mirror.on("lineWidgetAdded", (code_mirror, line_widget, line_number) => {
            recomputeCommentPlaceholderHeight(
                left_code_mirror,
                right_code_mirror,
                getRightLine(line_number)
            );
        });
        left_code_mirror.on("lineWidgetCleared", (code_mirror, line_widget, line_number) => {
            recomputeCommentPlaceholderHeight(
                left_code_mirror,
                right_code_mirror,
                getLeftLine(line_number)
            );
        });
        right_code_mirror.on("lineWidgetCleared", (code_mirror, line_widget, line_number) => {
            recomputeCommentPlaceholderHeight(
                left_code_mirror,
                right_code_mirror,
                getRightLine(line_number)
            );
        });

        left_code_mirror.on("gutterClick", (left_code_mirror, line_number) =>
            addCommentOnLeftCodeMirror(left_code_mirror, right_code_mirror, line_number)
        );
        right_code_mirror.on("gutterClick", (right_code_mirror, line_number) =>
            addCommentOnRightCodeMirror(left_code_mirror, right_code_mirror, line_number)
        );
    }

    function addCommentOnLeftCodeMirror(left_code_mirror, right_code_mirror, line_number) {
        const line = getLeftLine(line_number);
        if (!line) {
            return;
        }

        CodeMirrorHelperService.showCommentForm(
            left_code_mirror,
            line.unidiff_offset,
            line_number,
            self.filePath,
            self.pullRequestId,
            POSITION_LEFT,
            () => recomputeCommentPlaceholderHeight(left_code_mirror, right_code_mirror, line)
        );
    }

    function addCommentOnRightCodeMirror(left_code_mirror, right_code_mirror, line_number) {
        const line = getRightLine(line_number);
        if (!line) {
            return;
        }

        CodeMirrorHelperService.showCommentForm(
            right_code_mirror,
            line.unidiff_offset,
            line_number,
            self.filePath,
            self.pullRequestId,
            POSITION_RIGHT,
            () => recomputeCommentPlaceholderHeight(left_code_mirror, right_code_mirror, line)
        );
    }

    function addCodePlaceholder(line, left_code_mirror, right_code_mirror) {
        if (lineIsUnmoved(line) || !isFirstLineOfGroup(line)) {
            return null;
        }
        return buildCodePlaceholderWidget(line, left_code_mirror, right_code_mirror);
    }

    function addCommentsPlaceholder(line, left_code_mirror, right_code_mirror) {
        if (!lineIsUnmoved(line) && !isFirstLineOfGroup(line)) {
            return;
        }
        const widget_params = buildCommentsPlaceholderWidget(
            line,
            left_code_mirror,
            right_code_mirror
        );
        if (!widget_params) {
            return;
        }

        CodeMirrorHelperService.displayPlaceholderWidget(widget_params);
    }

    function lineIsUnmoved(line) {
        return line.new_offset !== null && line.old_offset !== null;
    }

    function displayLine(line, left_code_mirror, right_code_mirror) {
        if (line.old_offset !== null) {
            left_code_mirror.setGutterMarker(
                line.old_offset - 1,
                "gutter-lines",
                document.createTextNode(line.old_offset)
            );

            if (line.new_offset === null) {
                left_code_mirror.addLineClass(
                    line.old_offset - 1,
                    "background",
                    "pull-request-file-diff-deleted-lines"
                );
            }
        }

        if (line.new_offset !== null) {
            right_code_mirror.setGutterMarker(
                line.new_offset - 1,
                "gutter-lines",
                document.createTextNode(line.new_offset)
            );

            if (line.old_offset === null) {
                right_code_mirror.addLineClass(
                    line.new_offset - 1,
                    "background",
                    "pull-request-file-diff-added-lines"
                );
            }
        }
    }
}