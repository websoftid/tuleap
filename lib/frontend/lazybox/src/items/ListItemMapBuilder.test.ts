/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

import { describe, it, expect } from "vitest";
import { ListItemMapBuilder } from "./ListItemMapBuilder";
import { GroupCollectionBuilder } from "../../tests/builders/GroupCollectionBuilder";
import { TemplatingCallbackStub } from "../../tests/stubs/TemplatingCallbackStub";
import type { RenderedItemMap } from "../type";
import type { GroupCollection } from "./GroupCollection";

describe("ListItemBuilder", () => {
    const buildMap = (groups: GroupCollection): RenderedItemMap => {
        const builder = ListItemMapBuilder(TemplatingCallbackStub.build());
        return builder.buildLazyboxItemsMap(groups);
    };

    it(`flattens a single group and builds a RenderedItem for each item
        and returns a map containing all items`, () => {
        const map = buildMap(
            GroupCollectionBuilder.withSingleGroup({
                items: [
                    { id: "value-0", value: { id: 0 }, is_disabled: false },
                    { id: "value-1", value: { id: 1 }, is_disabled: false },
                    { id: "value-2", value: { id: 2 }, is_disabled: false },
                    { id: "value-3", value: { id: 3 }, is_disabled: false },
                ],
            })
        );

        expect(map.size).toBe(4);

        const [first_entry, second_entry, third_entry, fourth_entry] = Array.from(map.entries());
        expect(first_entry[0]).toBe("lazybox-item-value-0");
        expect(first_entry[1]).toStrictEqual({
            id: "lazybox-item-value-0",
            template: expect.anything(),
            value: { id: 0 },
            is_disabled: false,
            group_id: "",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(second_entry[0]).toBe("lazybox-item-value-1");
        expect(second_entry[1]).toStrictEqual({
            id: "lazybox-item-value-1",
            template: expect.anything(),
            value: { id: 1 },
            is_disabled: false,
            group_id: "",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(third_entry[0]).toBe("lazybox-item-value-2");
        expect(third_entry[1]).toStrictEqual({
            id: "lazybox-item-value-2",
            template: expect.anything(),
            value: { id: 2 },
            is_disabled: false,
            group_id: "",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(fourth_entry[0]).toBe("lazybox-item-value-3");
        expect(fourth_entry[1]).toStrictEqual({
            id: "lazybox-item-value-3",
            template: expect.anything(),
            value: { id: 3 },
            is_disabled: false,
            group_id: "",
            is_selected: false,
            element: expect.any(Element),
        });
    });

    it(`flattens the given groups and builds a RenderedItem for each item of each group
        and returns a map containing all items`, () => {
        const map = buildMap(GroupCollectionBuilder.withTwoGroups());

        expect(map.size).toBe(6);

        const [first_entry, second_entry, third_entry, fourth_entry, fifth_entry, sixth_entry] =
            Array.from(map.entries());

        expect(first_entry[0]).toBe("lazybox-item-group1-value-0");
        expect(first_entry[1]).toStrictEqual({
            id: "lazybox-item-group1-value-0",
            template: expect.anything(),
            value: { id: 0 },
            is_disabled: false,
            group_id: "group1",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(second_entry[0]).toBe("lazybox-item-group1-value-1");
        expect(second_entry[1]).toStrictEqual({
            id: "lazybox-item-group1-value-1",
            template: expect.anything(),
            value: { id: 1 },
            is_disabled: false,
            group_id: "group1",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(third_entry[0]).toBe("lazybox-item-group1-value-2");
        expect(third_entry[1]).toStrictEqual({
            id: "lazybox-item-group1-value-2",
            template: expect.anything(),
            value: { id: 2 },
            is_disabled: false,
            group_id: "group1",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(fourth_entry[0]).toBe("lazybox-item-group2-value-3");
        expect(fourth_entry[1]).toStrictEqual({
            id: "lazybox-item-group2-value-3",
            template: expect.anything(),
            value: { id: 3 },
            is_disabled: false,
            group_id: "group2",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(fifth_entry[0]).toBe("lazybox-item-group2-value-4");
        expect(fifth_entry[1]).toStrictEqual({
            id: "lazybox-item-group2-value-4",
            template: expect.anything(),
            value: { id: 4 },
            is_disabled: false,
            group_id: "group2",
            is_selected: false,
            element: expect.any(Element),
        });
        expect(sixth_entry[0]).toBe("lazybox-item-group2-value-5");
        expect(sixth_entry[1]).toStrictEqual({
            id: "lazybox-item-group2-value-5",
            template: expect.anything(),
            value: { id: 5 },
            is_disabled: true,
            group_id: "group2",
            is_selected: false,
            element: expect.any(Element),
        });
    });
});