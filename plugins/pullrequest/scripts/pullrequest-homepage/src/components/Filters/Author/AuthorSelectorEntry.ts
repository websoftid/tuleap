/*
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

import type { Fault } from "@tuleap/fault";
import type { SelectorEntry } from "@tuleap/plugin-pullrequest-selectors-dropdown";
import type { User } from "@tuleap/plugin-pullrequest-rest-api-types";
import { AuthorTemplatingCallback } from "./AuthorTemplatingCallback";
import { AuthorsLoader } from "./AuthorsLoader";
import { AuthorFilteringCallback } from "./AuthorFilteringCallback";
import { AuthorFilterBuilder, TYPE_FILTER_AUTHOR } from "./AuthorFilter";
import type { StoreListFilters } from "../ListFiltersStore";

export const isUser = (item_value: unknown): item_value is User =>
    typeof item_value === "object" && item_value !== null && "id" in item_value;

export const AuthorSelectorEntry = (
    $gettext: (string: string) => string,
    on_error_callback: (fault: Fault) => void,
    filters_store: StoreListFilters,
    repository_id: number,
): SelectorEntry => ({
    entry_name: $gettext("Author"),
    isDisabled: (): boolean => filters_store.hasAFilterWithType(TYPE_FILTER_AUTHOR),
    config: {
        placeholder: $gettext("Name"),
        label: $gettext("Matching users"),
        empty_message: $gettext("No matching user"),
        disabled_message: $gettext("You can only filter on one author"),
        templating_callback: AuthorTemplatingCallback,
        loadItems: AuthorsLoader(on_error_callback, repository_id),
        filterItems: AuthorFilteringCallback,
        onItemSelection: (item: unknown): void => {
            if (!isUser(item)) {
                return;
            }

            filters_store.storeFilter(AuthorFilterBuilder($gettext).fromAuthor(item));
        },
    },
});
