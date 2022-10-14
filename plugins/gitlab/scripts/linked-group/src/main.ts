/*
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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

import { openAllTargetModalsOnClick } from "@tuleap/tlp-modal";
import { getDatasetItemOrThrow } from "@tuleap/dom";
import { getPOFileFromLocaleWithoutExtension, initGettext } from "@tuleap/gettext";
import { EditConfigurationModal } from "./EditConfigurationModal";
import { UnlinkModal } from "./UnlinkModal";
import "../themes/main.scss";

const UNLINK_SELECTOR = "#unlink-button";

document.addEventListener("DOMContentLoaded", async () => {
    const locale = getDatasetItemOrThrow(document.body, "userLocale");
    const gettext_provider = await initGettext(
        locale,
        "plugin-gitlab/linked-group",
        (locale) => import(`../po/${getPOFileFromLocaleWithoutExtension(locale)}.po`)
    );

    openAllTargetModalsOnClick(document, UNLINK_SELECTOR);

    EditConfigurationModal(document, gettext_provider).init();
    UnlinkModal(document.location, document, gettext_provider).init();
});