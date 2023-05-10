/*
 * Copyright (c) Enalean, 2023-Present. All Rights Reserved.
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

import { define, dispatch, html } from "hybrids";
import type { SearchInput } from "../SearchInput";
import type { LazyboxNewItemCallback, LazyboxTemplatingCallback } from "../type";
import type { SelectionElement } from "../selection/SelectionElement";
import { getAllGroupsTemplate } from "./GroupTemplate";
import type { GroupCollection } from "../items/GroupCollection";

export const TAG = "tuleap-lazybox-dropdown";

export type DropdownElement = {
    open: boolean;
    multiple_selection: boolean;
    groups: GroupCollection;
    new_item_callback: LazyboxNewItemCallback | undefined;
    new_item_button_label: string;
    templating_callback: LazyboxTemplatingCallback;
    search_input: SearchInput & HTMLElement;
    selection: SelectionElement & HTMLElement;
};
type InternalDropdownElement = Readonly<DropdownElement> & {
    content(): HTMLElement;
};
export type HostElement = InternalDropdownElement & HTMLElement;

export const observeOpen = (
    host: HostElement,
    new_value: boolean,
    old_value: boolean | undefined
): void => {
    if (old_value === undefined) {
        // Do not focus at initial render
        return;
    }
    if (new_value) {
        dispatch(host, "open");
        host.content();
        host.search_input.setFocus();
        return;
    }
    host.selection.setFocus();
    dispatch(host, "close");
};

export const selectionSetter = (
    host: DropdownElement,
    selection: SelectionElement & HTMLElement
): SelectionElement & HTMLElement => {
    selection.addEventListener("open-dropdown", () => {
        host.open = true;
    });
    return selection;
};

export const DropdownElement = define<InternalDropdownElement>({
    tag: TAG,
    open: { observe: observeOpen, value: false },
    multiple_selection: false,
    groups: { set: (host, new_value) => new_value ?? [] },
    new_item_callback: undefined,
    new_item_button_label: "",
    templating_callback: undefined,
    search_input: undefined,
    selection: { set: selectionSetter },
    content: (host) => {
        if (!host.open) {
            return html``;
        }
        const search_section = !host.multiple_selection
            ? html`<span
                  class="lazybox-single-dropdown-search-section"
                  data-test="single-search-section"
                  >${host.search_input}</span
              >`
            : html``;

        const new_item_button =
            host.new_item_callback !== undefined
                ? html`<button
                      type="button"
                      class="lazybox-new-item-button"
                      onclick="${host.new_item_callback}"
                      data-test="new-item-button"
                  >
                      ${host.new_item_button_label}
                  </button>`
                : html``;

        return html`${search_section}${new_item_button}
            <ul
                class="lazybox-dropdown-values-list"
                role="listbox"
                aria-expanded="true"
                aria-hidden="false"
            >
                ${getAllGroupsTemplate(host)}
            </ul>`;
    },
});