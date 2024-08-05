/*
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

import { describe, expect, it } from "vitest";
import {
    DATE_SELECTABLE_TYPE,
    NUMERIC_SELECTABLE_TYPE,
    PROJECT_SELECTABLE_TYPE,
    TEXT_SELECTABLE_TYPE,
    TRACKER_SELECTABLE_TYPE,
} from "./cross-tracker-rest-api-types";
import { ArtifactsTableBuilder } from "./ArtifactsTableBuilder";
import {
    DATE_CELL,
    NUMERIC_CELL,
    PROJECT_CELL,
    TEXT_CELL,
    TRACKER_CELL,
} from "../domain/ArtifactsTable";
import {
    ARTIFACT_COLUMN_NAME,
    PROJECT_COLUMN_NAME,
    TRACKER_COLUMN_NAME,
} from "../domain/ColumnName";
import { SelectableReportContentRepresentationStub } from "../../tests/builders/SelectableReportContentRepresentationStub";
import { ArtifactRepresentationStub } from "../../tests/builders/ArtifactRepresentationStub";

describe(`ArtifactsTableBuilder`, () => {
    describe(`mapReportToArtifactsTable()`, () => {
        const artifact_column = ARTIFACT_COLUMN_NAME;

        it(`will transform each selected name into a column name
            and for each artifact, it will create a Map from column name to its value
            so that it is easy to render the table of results for each column`, () => {
            const first_artifact_uri = "/plugins/tracker/?aid=540";
            const second_artifact_uri = "/plugins/tracker/?aid=435";

            const first_date = "2022-09-15T00:00:00+06:00";
            const second_date = "2018-09-23T23:26:36+09:00";
            const date_column = "start_date";

            const float_value = 15.2;
            const int_value = 10;
            const numeric_column = "remaining_effort";

            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [
                        { type: DATE_SELECTABLE_TYPE, name: date_column },
                        { type: NUMERIC_SELECTABLE_TYPE, name: numeric_column },
                    ],
                    [
                        ArtifactRepresentationStub.build({
                            [artifact_column]: { uri: first_artifact_uri },
                            [date_column]: { value: first_date, with_time: false },
                            [numeric_column]: { value: float_value },
                        }),
                        ArtifactRepresentationStub.build({
                            [artifact_column]: { uri: second_artifact_uri },
                            [date_column]: { value: second_date, with_time: true },
                            [numeric_column]: { value: int_value },
                        }),
                    ],
                ),
            );

            expect(table.columns).toHaveLength(3);
            expect(table.columns.has(artifact_column)).toBe(true);
            expect(table.columns.has(date_column)).toBe(true);
            expect(table.columns.has(numeric_column)).toBe(true);

            expect(table.rows).toHaveLength(2);
            const [first_row, second_row] = table.rows;

            expect(first_row.uri).toBe(first_artifact_uri);

            const date_value_first_row = first_row.cells.get(date_column);
            if (date_value_first_row?.type !== DATE_CELL) {
                throw Error("Expected to find first date value");
            }
            expect(date_value_first_row.value.unwrapOr(null)).toBe(first_date);

            const numeric_value_first_row = first_row.cells.get(numeric_column);
            if (numeric_value_first_row?.type !== NUMERIC_CELL) {
                throw Error("Expected to find first numeric value");
            }
            expect(numeric_value_first_row.value.unwrapOr(null)).toBe(float_value);

            expect(second_row.uri).toBe(second_artifact_uri);

            const date_value_second_row = second_row.cells.get(date_column);
            if (date_value_second_row?.type !== DATE_CELL) {
                throw Error("Expected to find second date value");
            }
            expect(date_value_second_row.value.unwrapOr(null)).toBe(second_date);

            const numeric_value_second_row = second_row.cells.get(numeric_column);
            if (numeric_value_second_row?.type !== NUMERIC_CELL) {
                throw Error("Expected to find second numeric value");
            }
            expect(numeric_value_second_row.value.unwrapOr(null)).toBe(int_value);
        });

        it(`builds a table with "date" selectables`, () => {
            const first_date = "2022-09-15T00:00:00+06:00";
            const second_date = "2018-09-23T23:26:36+09:00";
            const date_column = "start_date";

            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [{ type: DATE_SELECTABLE_TYPE, name: date_column }],
                    [
                        ArtifactRepresentationStub.build({
                            [date_column]: { value: first_date, with_time: false },
                        }),
                        ArtifactRepresentationStub.build({
                            [date_column]: { value: second_date, with_time: true },
                        }),
                        ArtifactRepresentationStub.build({
                            [date_column]: { value: null, with_time: false },
                        }),
                    ],
                ),
            );

            expect(table.columns.has(date_column)).toBe(true);
            expect(table.rows).toHaveLength(3);
            const [first_row, second_row, third_row] = table.rows;
            const date_value_first_row = first_row.cells.get(date_column);
            if (date_value_first_row?.type !== DATE_CELL) {
                throw Error("Expected to find first date value");
            }
            expect(date_value_first_row.value.unwrapOr(null)).toBe(first_date);
            expect(date_value_first_row.with_time).toBe(false);

            const date_value_second_row = second_row.cells.get(date_column);
            if (date_value_second_row?.type !== DATE_CELL) {
                throw Error("Expected to find second date value");
            }
            expect(date_value_second_row.value.unwrapOr(null)).toBe(second_date);
            expect(date_value_second_row.with_time).toBe(true);

            const date_value_third_row = third_row.cells.get(date_column);
            if (date_value_third_row?.type !== DATE_CELL) {
                throw Error("Expected to find third date value");
            }
            expect(date_value_third_row.value.isNothing()).toBe(true);
            expect(date_value_third_row.with_time).toBe(false);
        });

        it(`builds a table with "numeric" selectables`, () => {
            const float_value = 15.2;
            const int_value = 10;
            const numeric_column = "remaining_effort";

            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [{ type: NUMERIC_SELECTABLE_TYPE, name: numeric_column }],
                    [
                        ArtifactRepresentationStub.build({
                            [numeric_column]: { value: float_value },
                        }),
                        ArtifactRepresentationStub.build({
                            [numeric_column]: { value: int_value },
                        }),
                        ArtifactRepresentationStub.build({ [numeric_column]: { value: null } }),
                    ],
                ),
            );

            expect(table.columns.has(numeric_column)).toBe(true);
            expect(table.rows).toHaveLength(3);
            const [first_row, second_row, third_row] = table.rows;
            const numeric_value_first_row = first_row.cells.get(numeric_column);
            if (numeric_value_first_row?.type !== NUMERIC_CELL) {
                throw Error("Expected to find first numeric value");
            }
            expect(numeric_value_first_row.value.unwrapOr(null)).toBe(float_value);

            const numeric_value_second_row = second_row.cells.get(numeric_column);
            if (numeric_value_second_row?.type !== NUMERIC_CELL) {
                throw Error("Expected to find second numeric value");
            }
            expect(numeric_value_second_row.value.unwrapOr(null)).toBe(int_value);

            const numeric_value_third_row = third_row.cells.get(numeric_column);
            if (numeric_value_third_row?.type !== NUMERIC_CELL) {
                throw Error("Expected to find third numeric value");
            }
            expect(numeric_value_third_row.value.isNothing()).toBe(true);
        });

        it(`builds a table with "text" selectables`, () => {
            const text_value = "<p>Griffith II</p>";
            const text_column = "details";

            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [{ type: TEXT_SELECTABLE_TYPE, name: text_column }],
                    [
                        ArtifactRepresentationStub.build({ [text_column]: { value: text_value } }),
                        ArtifactRepresentationStub.build({ [text_column]: { value: "" } }),
                    ],
                ),
            );

            expect(table.columns.has(text_column)).toBe(true);
            expect(table.rows).toHaveLength(2);
            const [first_row, second_row] = table.rows;
            const text_value_first_row = first_row.cells.get(text_column);
            if (text_value_first_row?.type !== TEXT_CELL) {
                throw Error("Expected to find first text value");
            }
            expect(text_value_first_row.value).toBe(text_value);

            const text_value_second_row = second_row.cells.get(text_column);
            if (text_value_second_row?.type !== TEXT_CELL) {
                throw Error("Expected to find second text value");
            }
            expect(text_value_second_row.value).toBe("");
        });

        it(`builds a table with "project" selectables`, () => {
            const first_project = { icon: "", name: "Minimum Butter" };
            const second_project = { icon: "🖍️", name: "Teal Creek" };
            const project_column = PROJECT_COLUMN_NAME;

            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [{ type: PROJECT_SELECTABLE_TYPE, name: project_column }],
                    [
                        ArtifactRepresentationStub.build({ [project_column]: first_project }),
                        ArtifactRepresentationStub.build({ [project_column]: second_project }),
                    ],
                ),
            );

            expect(table.columns.has(project_column)).toBe(true);
            expect(table.rows).toHaveLength(2);
            const [first_row, second_row] = table.rows;
            const project_first_row = first_row.cells.get(project_column);
            if (project_first_row?.type !== PROJECT_CELL) {
                throw Error("Expected to find first project name");
            }
            expect(project_first_row.icon).toBe(first_project.icon);
            expect(project_first_row.name).toBe(first_project.name);

            const project_second_row = second_row.cells.get(project_column);
            if (project_second_row?.type !== PROJECT_CELL) {
                throw Error("Expected to find second project name");
            }
            expect(project_second_row.icon).toBe(second_project.icon);
            expect(project_second_row.name).toBe(second_project.name);
        });

        it(`builds a table with "tracker" selectables`, () => {
            const first_tracker = { color: "army-green", name: "Releases" };
            const second_tracker = { color: "inca-silver", name: "Activities" };
            const tracker_column = TRACKER_COLUMN_NAME;

            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [{ type: TRACKER_SELECTABLE_TYPE, name: tracker_column }],
                    [
                        ArtifactRepresentationStub.build({ [tracker_column]: first_tracker }),
                        ArtifactRepresentationStub.build({ [tracker_column]: second_tracker }),
                    ],
                ),
            );

            expect(table.columns.has(tracker_column)).toBe(true);
            expect(table.rows).toHaveLength(2);
            const [first_row, second_row] = table.rows;
            const tracker_first_row = first_row.cells.get(tracker_column);
            if (tracker_first_row?.type !== TRACKER_CELL) {
                throw Error("Expected to find first tracker name");
            }
            expect(tracker_first_row.name).toBe(first_tracker.name);
            expect(tracker_first_row.color).toBe(first_tracker.color);

            const tracker_second_row = second_row.cells.get(tracker_column);
            if (tracker_second_row?.type !== TRACKER_CELL) {
                throw Error("Expected to find second tracker name");
            }
            expect(tracker_second_row.name).toBe(second_tracker.name);
            expect(tracker_second_row.color).toBe(second_tracker.color);
        });

        it(`given a report content representation with an unsupported selectable type,
            it will NOT include it in the columns of the table
            and will NOT include it in the rows`, () => {
            const table = ArtifactsTableBuilder().mapReportToArtifactsTable(
                SelectableReportContentRepresentationStub.build(
                    [{ type: "unsupported", name: "wacken" }],
                    [ArtifactRepresentationStub.build({ wacken: { value: "frightfulness" } })],
                ),
            );
            expect(table.columns).toHaveLength(1);
            expect(table.columns.has(ARTIFACT_COLUMN_NAME)).toBe(true);
            expect(table.columns.has("wacken")).toBe(false);
            expect(table.rows).toHaveLength(1);
            expect(table.rows[0].uri).toBeDefined();
            expect(table.rows[0].cells).toHaveLength(0);
        });

        function* generateBrokenSelectedValues(): Generator<[string, Record<string, unknown>]> {
            yield [DATE_SELECTABLE_TYPE, { value: "ritualist" }];
            yield [NUMERIC_SELECTABLE_TYPE, { value: "ritualist" }];
            yield [TEXT_SELECTABLE_TYPE, { value: 12 }];
            yield [PROJECT_SELECTABLE_TYPE, { value: 12 }];
            yield [TRACKER_SELECTABLE_TYPE, { value: 12 }];
        }

        it.each([...generateBrokenSelectedValues()])(
            `when the artifact value does not match the %s representation, it will throw an error`,
            (selected_type, representation) => {
                expect(() =>
                    ArtifactsTableBuilder().mapReportToArtifactsTable(
                        SelectableReportContentRepresentationStub.build(
                            [{ type: selected_type, name: "makeress" }],
                            [ArtifactRepresentationStub.build({ makeress: representation })],
                        ),
                    ),
                ).toThrow();
            },
        );
    });
});
