<!--
  - Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->
<template>
    <div class="timetracking-overview-writing-mode-selected-dates">
        <div class="tlp-form-element timetracking-overview-writing-mode-selected-date">
            <label for="timetracking-start-date" class="tlp-label">
                <translate>From</translate>
                <i class="fa fa-asterisk"></i>
            </label>
            <div class="tlp-form-element tlp-form-element-prepend">
                <span class="tlp-prepend">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <input
                    type="text"
                    class="tlp-input tlp-input-date"
                    id="timetracking-start-date"
                    ref="start_date"
                    v-bind:value="start_date"
                    size="11"
                />
            </div>
        </div>
        <div class="tlp-form-element timetracking-overview-writing-mode-selected-date">
            <label for="timetracking-end-date" class="tlp-label">
                <translate>To</translate>
                <i class="fa fa-asterisk"></i>
            </label>
            <div class="tlp-form-element tlp-form-element-prepend">
                <span class="tlp-prepend">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <input
                    type="text"
                    class="tlp-input tlp-input-date"
                    id="timetracking-end-date"
                    ref="end_date"
                    v-bind:value="end_date"
                    size="11"
                />
            </div>
        </div>
    </div>
</template>

<script>
import { datePicker } from "tlp";
import { mapState } from "vuex";
export default {
    name: "TimeTrackingOverviewWritingDates",
    computed: {
        ...mapState(["start_date", "end_date"]),
    },
    mounted() {
        const store = this.$store;
        datePicker(this.$refs.start_date, {
            onClose(long_date, short_date) {
                store.commit("setStartDate", short_date);
            },
        });

        datePicker(this.$refs.end_date, {
            onClose(long_date, short_date) {
                store.commit("setEndDate", short_date);
            },
        });
    },
};
</script>
