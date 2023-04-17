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

export class FieldFocusManager {
    private steal_focus_event_from_source_select_box_handler!: (event: Event) => void;

    constructor(
        private readonly doc: Document,
        private readonly source_select_box: HTMLSelectElement,
        private readonly selection_element: HTMLElement
    ) {}

    public init(): void {
        this.steal_focus_event_from_source_select_box_handler = (event: Event): void => {
            event.preventDefault();
            this.applyFocusOnLazybox();
        };

        this.source_select_box.addEventListener(
            "focus",
            this.steal_focus_event_from_source_select_box_handler
        );
    }

    public destroy(): void {
        this.source_select_box.removeEventListener(
            "focus",
            this.steal_focus_event_from_source_select_box_handler
        );
    }

    public doesSelectionElementHaveTheFocus(): boolean {
        return this.doc.activeElement === this.selection_element;
    }

    public applyFocusOnLazybox(): void {
        this.selection_element.focus();
    }
}
