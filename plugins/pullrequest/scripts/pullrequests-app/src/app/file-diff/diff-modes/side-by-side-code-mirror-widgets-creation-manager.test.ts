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

import type { Editor } from "codemirror";
import type { ManageCodeMirrorWidgetsCreation } from "./side-by-side-code-mirror-widgets-creation-manager";
import type { StubCreateInlineCommentWidget } from "../../../../tests/stubs/CreateInlineCommentWidgetStub";
import type { StubCreatePlaceholderWidget } from "../../../../tests/stubs/CreatePlaceholderWidgetStub";
import type { SynchronizedLineHandles } from "./side-by-side-line-mapper";
import type { PullRequestInlineCommentPresenter } from "../../comments/PullRequestCommentPresenter";
import type { InlineCommentPosition } from "../../comments/types";
import type {
    AddedFileLine,
    FileLine,
    GroupOfLines,
    RemovedFileLine,
    UnMovedFileLine,
} from "./types";

import { PullRequestCommentPresenterStub } from "../../../../tests/stubs/PullRequestCommentPresenterStub";
import { GroupOfLinesStub } from "../../../../tests/stubs/GroupOfLinesStub";
import { FileLineStub } from "../../../../tests/stubs/FileLineStub";
import { FileLineHandleStub } from "../../../../tests/stubs/FileLineHandleStub";
import { FileDiffWidgetStub } from "../../../../tests/stubs/FileDiffWidgetStub";
import { CreatePlaceholderWidgetStub } from "../../../../tests/stubs/CreatePlaceholderWidgetStub";
import { FileLinesStateStub } from "../../../../tests/stubs/FileLinesStateStub";
import { CreateInlineCommentWidgetStub } from "../../../../tests/stubs/CreateInlineCommentWidgetStub";

import { SideBySideCodeMirrorWidgetsCreationManager } from "./side-by-side-code-mirror-widgets-creation-manager";
import { SideBySideCodeMirrorsContentManager } from "./side-by-side-code-mirrors-content-manager";
import { SideBySideLinesHeightEqualizer } from "./side-by-side-line-height-equalizer";
import { SideBySidePlaceholderPositioner } from "./side-by-side-placeholder-positioner";
import { INLINE_COMMENT_POSITION_LEFT, INLINE_COMMENT_POSITION_RIGHT } from "../../comments/types";

describe("side-by-side-code-mirror-widgets-creation-manager", () => {
    let left_code_mirror: Editor,
        right_code_mirror: Editor,
        create_inline_comment_stub: StubCreateInlineCommentWidget,
        create_placeholder_stub: StubCreatePlaceholderWidget;

    const buildCreationManager = (
        lines: FileLine[],
        group_of_lines: GroupOfLines[],
        lines_to_handles_map: Map<FileLine, SynchronizedLineHandles>
    ): ManageCodeMirrorWidgetsCreation => {
        const file_lines_state = FileLinesStateStub(lines, group_of_lines, lines_to_handles_map);
        return SideBySideCodeMirrorWidgetsCreationManager(
            file_lines_state.getState(),
            SideBySideLinesHeightEqualizer(
                left_code_mirror,
                right_code_mirror,
                SideBySidePlaceholderPositioner(file_lines_state.getState())
            ),
            SideBySideCodeMirrorsContentManager(
                file_lines_state.getFileLines(),
                left_code_mirror,
                right_code_mirror
            ),
            create_inline_comment_stub.build(),
            create_placeholder_stub.build()
        );
    };

    const triggerPostRenderCallback = (): void => {
        const comment_widget_params =
            create_inline_comment_stub.getLastCreationParametersReceived();
        if (comment_widget_params === null) {
            throw new Error("An inline comment widget should have been created");
        }

        comment_widget_params.post_rendering_callback();
    };

    beforeEach(() => {
        left_code_mirror = {
            name: "left-code-mirror",
            setValue: jest.fn(),
        } as unknown as Editor;

        right_code_mirror = {
            name: "right-code-mirror",
            setValue: jest.fn(),
        } as unknown as Editor;

        create_inline_comment_stub = CreateInlineCommentWidgetStub();
        create_placeholder_stub = CreatePlaceholderWidgetStub();
    });

    describe("Management of inline comments widgets", () => {
        it("When the line of the comment is not found in the state, Then it does nothing", () => {
            const comment = PullRequestCommentPresenterStub.buildFileDiffCommentPresenter({
                unidiff_offset: 12,
            });

            const widget_creation_manager = buildCreationManager([], [], new Map([]));

            widget_creation_manager.displayInlineComment(comment);

            expect(create_inline_comment_stub.getNbCalls()).toBe(0);
        });

        describe("on removed lines (left side)", () => {
            let comment_line: RemovedFileLine, comment: PullRequestInlineCommentPresenter;

            beforeEach(() => {
                comment_line = FileLineStub.buildRemovedLine(12, 12);
                comment = PullRequestCommentPresenterStub.buildFileDiffCommentPresenter({
                    unidiff_offset: 12,
                });
            });

            it("should create an inline comment widget on the left codemirror", () => {
                buildCreationManager(
                    [comment_line],
                    [GroupOfLinesStub.buildGroupOfRemovedLines([comment_line])],
                    new Map([])
                ).displayInlineComment(comment);

                expect(create_inline_comment_stub.getNbCalls()).toBe(1);
                expect(
                    create_inline_comment_stub.getLastCreationParametersReceived()
                ).toStrictEqual({
                    code_mirror: left_code_mirror,
                    comment,
                    line_number: 11,
                    post_rendering_callback: expect.any(Function),
                });
            });

            it(`Given that there is no placeholder on the opposite side (right side)
                When the widget has rendered
                Then it should create one on the right codemirror with the same height`, () => {
                const left_side_handle_height = 160;
                const right_side_handle = FileLineHandleStub.buildLineHandleWithNoWidgets();
                buildCreationManager(
                    [comment_line],
                    [GroupOfLinesStub.buildGroupOfRemovedLines([comment_line])],
                    new Map([
                        [
                            comment_line,
                            {
                                left_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                    FileDiffWidgetStub.buildInlineCommentWidget(
                                        left_side_handle_height
                                    ),
                                ]),
                                right_handle: right_side_handle,
                            },
                        ],
                    ])
                ).displayInlineComment(comment);

                triggerPostRenderCallback();

                expect(create_placeholder_stub.getNbCalls()).toBe(1);
                expect(create_placeholder_stub.getLastCreationParametersReceived()).toStrictEqual({
                    code_mirror: right_code_mirror,
                    handle: right_side_handle,
                    widget_height: left_side_handle_height,
                    display_above_line: true,
                    is_comment_placeholder: true,
                });
            });

            it(`Given that there is already a comment placeholder on the opposite side (right side)
                When the widget has rendered
                Then it should update the comment placeholder height so the two lines have the same height`, () => {
                const left_side_handle_height = 160;
                const right_hand_side_placeholder =
                    FileDiffWidgetStub.buildCodeCommentPlaceholder(60);
                buildCreationManager(
                    [comment_line],
                    [GroupOfLinesStub.buildGroupOfRemovedLines([comment_line])],
                    new Map([
                        [
                            comment_line,
                            {
                                left_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                    FileDiffWidgetStub.buildInlineCommentWidget(
                                        left_side_handle_height
                                    ),
                                ]),
                                right_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                    right_hand_side_placeholder,
                                ]),
                            },
                        ],
                    ])
                ).displayInlineComment(comment);

                triggerPostRenderCallback();

                expect(create_placeholder_stub.getNbCalls()).toBe(0);
                expect(right_hand_side_placeholder.height).toStrictEqual(left_side_handle_height);
            });
        });

        describe("on added lines (right side)", () => {
            let comment_line: AddedFileLine, comment: PullRequestInlineCommentPresenter;

            beforeEach(() => {
                comment_line = FileLineStub.buildAddedLine(12, 12);
                comment = PullRequestCommentPresenterStub.buildFileDiffCommentPresenter({
                    unidiff_offset: 12,
                });
            });

            it(`should create an inline comment widget on the right codemirror`, () => {
                const widget_creation_manager = buildCreationManager(
                    [comment_line],
                    [GroupOfLinesStub.buildGroupOfAddedLines([comment_line])],
                    new Map([])
                );

                widget_creation_manager.displayInlineComment(comment);

                expect(create_inline_comment_stub.getNbCalls()).toBe(1);
                expect(
                    create_inline_comment_stub.getLastCreationParametersReceived()
                ).toStrictEqual({
                    code_mirror: right_code_mirror,
                    comment,
                    line_number: 11,
                    post_rendering_callback: expect.any(Function),
                });
            });

            it(`Given that there is no placeholder on the opposite side (left side)
                When the widget has rendered
                Then it should create one on the left codemirror with the same height`, () => {
                const right_side_handle_height = 160;
                const left_side_handle = FileLineHandleStub.buildLineHandleWithNoWidgets();
                buildCreationManager(
                    [comment_line],
                    [GroupOfLinesStub.buildGroupOfAddedLines([comment_line])],
                    new Map([
                        [
                            comment_line,
                            {
                                left_handle: left_side_handle,
                                right_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                    FileDiffWidgetStub.buildInlineCommentWidget(
                                        right_side_handle_height
                                    ),
                                ]),
                            },
                        ],
                    ])
                ).displayInlineComment(comment);

                triggerPostRenderCallback();

                expect(create_placeholder_stub.getNbCalls()).toBe(1);
                expect(create_placeholder_stub.getLastCreationParametersReceived()).toStrictEqual({
                    code_mirror: left_code_mirror,
                    handle: left_side_handle,
                    widget_height: right_side_handle_height,
                    display_above_line: false,
                    is_comment_placeholder: true,
                });
            });

            it(`Given that there is already a comment placeholder on the opposite side (left side)
                When the widget has rendered
                Then it should update the comment placeholder height so the two lines have the same height`, () => {
                const right_side_handle_height = 160;
                const left_hand_side_placeholder =
                    FileDiffWidgetStub.buildCodeCommentPlaceholder(60);

                buildCreationManager(
                    [comment_line],
                    [GroupOfLinesStub.buildGroupOfAddedLines([comment_line])],
                    new Map([
                        [
                            comment_line,
                            {
                                left_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                    left_hand_side_placeholder,
                                ]),
                                right_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                    FileDiffWidgetStub.buildInlineCommentWidget(
                                        right_side_handle_height
                                    ),
                                ]),
                            },
                        ],
                    ])
                ).displayInlineComment(comment);

                triggerPostRenderCallback();

                expect(create_placeholder_stub.getNbCalls()).toBe(0);
                expect(left_hand_side_placeholder.height).toStrictEqual(right_side_handle_height);
            });
        });

        describe("on unmoved lines (left and right)", () => {
            let comment_line: UnMovedFileLine;

            const buildComment = (
                position: InlineCommentPosition
            ): PullRequestInlineCommentPresenter => {
                return PullRequestCommentPresenterStub.buildFileDiffCommentPresenter({
                    unidiff_offset: 12,
                    position,
                });
            };

            beforeEach(() => {
                comment_line = FileLineStub.buildUnMovedFileLine(12, 12);
            });

            describe("with comments on the LEFT side", () => {
                it(`should create an inline comment widget on the LEFT codemirror`, () => {
                    const comment = buildComment(INLINE_COMMENT_POSITION_LEFT);
                    const widget_creation_manager = buildCreationManager(
                        [comment_line],
                        [GroupOfLinesStub.buildGroupOfUnMovedLines([comment_line])],
                        new Map([])
                    );
                    widget_creation_manager.displayInlineComment(comment);

                    expect(create_inline_comment_stub.getNbCalls()).toBe(1);
                    expect(
                        create_inline_comment_stub.getLastCreationParametersReceived()
                    ).toStrictEqual({
                        code_mirror: left_code_mirror,
                        comment,
                        line_number: 11,
                        post_rendering_callback: expect.any(Function),
                    });
                });

                it(`Given that there is no placeholder on the opposite side (right side)
                    When the widget has rendered
                    Then it should create one on the right codemirror`, () => {
                    const comment = buildComment(INLINE_COMMENT_POSITION_LEFT);
                    const left_side_handle_height = 160;
                    const right_side_handle = FileLineHandleStub.buildLineHandleWithNoWidgets();

                    buildCreationManager(
                        [comment_line],
                        [GroupOfLinesStub.buildGroupOfUnMovedLines([comment_line])],
                        new Map([
                            [
                                comment_line,
                                {
                                    left_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                        FileDiffWidgetStub.buildInlineCommentWidget(
                                            left_side_handle_height
                                        ),
                                    ]),
                                    right_handle: right_side_handle,
                                },
                            ],
                        ])
                    ).displayInlineComment(comment);

                    triggerPostRenderCallback();

                    expect(create_placeholder_stub.getNbCalls()).toBe(1);
                    expect(
                        create_placeholder_stub.getLastCreationParametersReceived()
                    ).toStrictEqual({
                        code_mirror: right_code_mirror,
                        handle: right_side_handle,
                        widget_height: left_side_handle_height,
                        display_above_line: false,
                        is_comment_placeholder: true,
                    });
                });

                it(`Given that there is already a comment placeholder on the opposite side (right side)
                    When the widget has rendered
                    Then it should update the comment placeholder height so the two lines have the same height`, () => {
                    const comment = buildComment(INLINE_COMMENT_POSITION_LEFT);
                    const left_side_handle_height = 160;
                    const right_hand_side_placeholder =
                        FileDiffWidgetStub.buildCodeCommentPlaceholder(60);

                    buildCreationManager(
                        [comment_line],
                        [GroupOfLinesStub.buildGroupOfUnMovedLines([comment_line])],
                        new Map([
                            [
                                comment_line,
                                {
                                    left_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                        FileDiffWidgetStub.buildInlineCommentWidget(
                                            left_side_handle_height
                                        ),
                                    ]),
                                    right_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                        right_hand_side_placeholder,
                                    ]),
                                },
                            ],
                        ])
                    ).displayInlineComment(comment);

                    triggerPostRenderCallback();

                    expect(create_placeholder_stub.getNbCalls()).toBe(0);
                    expect(right_hand_side_placeholder.height).toStrictEqual(
                        left_side_handle_height
                    );
                });
            });

            describe("with comments on the RIGHT side", () => {
                it(`should create an inline comment widget on the right codemirror`, () => {
                    const comment = buildComment(INLINE_COMMENT_POSITION_RIGHT);
                    const widget_creation_manager = buildCreationManager(
                        [comment_line],
                        [GroupOfLinesStub.buildGroupOfUnMovedLines([comment_line])],
                        new Map([])
                    );

                    widget_creation_manager.displayInlineComment(comment);

                    expect(create_inline_comment_stub.getNbCalls()).toBe(1);
                    expect(
                        create_inline_comment_stub.getLastCreationParametersReceived()
                    ).toStrictEqual({
                        code_mirror: right_code_mirror,
                        comment,
                        line_number: 11,
                        post_rendering_callback: expect.any(Function),
                    });
                });

                it(`Given that there is no placeholder on the opposite side (left side)
                    When the widget has rendered
                    Then it should create one on the left codemirror`, () => {
                    const comment = buildComment(INLINE_COMMENT_POSITION_RIGHT);
                    const right_side_handle_height = 160;
                    const left_side_handle = FileLineHandleStub.buildLineHandleWithNoWidgets();

                    buildCreationManager(
                        [comment_line],
                        [GroupOfLinesStub.buildGroupOfUnMovedLines([comment_line])],
                        new Map([
                            [
                                comment_line,
                                {
                                    left_handle: left_side_handle,
                                    right_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                        FileDiffWidgetStub.buildInlineCommentWidget(
                                            right_side_handle_height
                                        ),
                                    ]),
                                },
                            ],
                        ])
                    ).displayInlineComment(comment);

                    triggerPostRenderCallback();

                    expect(create_placeholder_stub.getNbCalls()).toBe(1);
                    expect(
                        create_placeholder_stub.getLastCreationParametersReceived()
                    ).toStrictEqual({
                        code_mirror: left_code_mirror,
                        handle: left_side_handle,
                        widget_height: right_side_handle_height,
                        display_above_line: false,
                        is_comment_placeholder: true,
                    });
                });

                it(`Given that there is already a comment placeholder on the opposite side (left side)
                    When the widget has rendered
                    Then it should update the comment placeholder height so the two lines have the same height`, () => {
                    const comment = buildComment(INLINE_COMMENT_POSITION_RIGHT);
                    const right_side_handle_height = 160;
                    const left_hand_side_placeholder =
                        FileDiffWidgetStub.buildCodeCommentPlaceholder(60);

                    buildCreationManager(
                        [comment_line],
                        [GroupOfLinesStub.buildGroupOfUnMovedLines([comment_line])],
                        new Map([
                            [
                                comment_line,
                                {
                                    left_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                        left_hand_side_placeholder,
                                    ]),
                                    right_handle: FileLineHandleStub.buildLineHandleWithWidgets([
                                        FileDiffWidgetStub.buildInlineCommentWidget(
                                            right_side_handle_height
                                        ),
                                    ]),
                                },
                            ],
                        ])
                    ).displayInlineComment(comment);

                    triggerPostRenderCallback();

                    expect(create_placeholder_stub.getNbCalls()).toBe(0);
                    expect(left_hand_side_placeholder.height).toStrictEqual(
                        right_side_handle_height
                    );
                });
            });
        });
    });
});