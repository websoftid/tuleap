/*
 * Copyright (c) Enalean, 2017-Present. All Rights Reserved.
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

import angular from "angular";
import ngSanitize from "angular-sanitize";

import "imports-loader?CKEDITOR=>window.CKEDITOR!ng-ckeditor";
import "angular-moment";
import "angular-gettext";
import translations from "../po/fr.po";

import fields from "./tuleap-artifact-modal-fields/fields.js";
import model from "./model/model.js";
import quota_display from "./quota-display/quota-display.js";
import tuleap_highlight from "./tuleap-highlight/highlight.js";
import angular_tlp from "angular-tlp";

import FieldDependenciesService from "./field-dependencies-service.js";
import ValidateService from "./validate-service.js";
import ArtifactModalService from "./tuleap-artifact-modal-service.js";
import ArtifactModalController from "./tuleap-artifact-modal-controller.js";
import NewFollowupComponent from "./followups/new-followup-component.js";

angular
    .module("tuleap.artifact-modal", [
        "angularMoment",
        "ng.ckeditor",
        "gettext",
        angular_tlp,
        fields,
        model,
        ngSanitize,
        quota_display,
        tuleap_highlight
    ])
    .run([
        "gettextCatalog",
        function(gettextCatalog) {
            for (const [language, strings] of Object.entries(translations)) {
                gettextCatalog.setStrings(language, strings);
            }
        }
    ])
    .component("tuleapArtifactModalNewFollowup", NewFollowupComponent)
    .controller("TuleapArtifactModalController", ArtifactModalController)
    .value("TuleapArtifactModalLoading", {
        loading: false
    })
    .service("TuleapArtifactModalFieldDependenciesService", FieldDependenciesService)
    .service("TuleapArtifactModalValidateService", ValidateService)
    .service("NewTuleapArtifactModalService", ArtifactModalService);

export default "tuleap.artifact-modal";
