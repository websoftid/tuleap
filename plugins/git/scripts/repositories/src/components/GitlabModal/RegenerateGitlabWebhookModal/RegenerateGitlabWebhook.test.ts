/**
 * Copyright (c) Enalean, 2021 - present. All Rights Reserved.
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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 */

import { createStoreMock } from "@tuleap/core/scripts/vue-components/store-wrapper-jest";
import type { Wrapper } from "@vue/test-utils";
import { createLocalVue, shallowMount } from "@vue/test-utils";
import RegenerateGitlabWebhook from "./RegenerateGitlabWebhook.vue";
import VueDOMPurifyHTML from "vue-dompurify-html";
import GetTextPlugin from "vue-gettext";
import type { Store } from "vuex-mock-store";

describe("RegenerateGitlabWebhook", () => {
    let store_options = {},
        localVue,
        store: Store;

    beforeEach(() => {
        store_options = {
            state: {},
            getters: {},
        };
    });

    function instantiateComponent(): Wrapper<RegenerateGitlabWebhook> {
        localVue = createLocalVue();
        localVue.use(VueDOMPurifyHTML);
        localVue.use(GetTextPlugin, {
            translations: {},
            silent: true,
        });

        store = createStoreMock(store_options);

        return shallowMount(RegenerateGitlabWebhook, {
            mocks: { $store: store },
            localVue,
        });
    }

    it("When the user confirms new token, Then api is called", async () => {
        const wrapper = instantiateComponent();
        expect(wrapper.find("[data-test=icon-spin]").exists()).toBeFalsy();

        wrapper.setData({
            repository: {
                gitlab_data: {
                    gitlab_repository_url: "https://example.com/my/repo",
                    gitlab_repository_id: 12,
                },
                normalized_path: "my/repo",
            },
        });
        await wrapper.vm.$nextTick();

        wrapper.find("[data-test=regenerate-gitlab-webhook-submit]").trigger("click");
        await wrapper.vm.$nextTick();

        expect(
            wrapper.find("[data-test=regenerate-gitlab-webhook-submit]").attributes().disabled
        ).toBeTruthy();
        expect(wrapper.find("[data-test=icon-spin]").exists()).toBeTruthy();

        expect(store.dispatch).toHaveBeenCalledWith("regenerateGitlabWebhook", {
            gitlab_repository_id: 12,
            gitlab_repository_url: "https://example.com/my/repo",
        });
    });

    it("When user submit but there are errors, Then nothing happens", async () => {
        const wrapper = instantiateComponent();

        wrapper.setData({
            message_error_rest: "Error message",
            repository: {
                gitlab_data: {
                    gitlab_repository_url: "https://example.com/my/repo",
                    gitlab_repository_id: 12,
                },
                normalized_path: "my/repo",
            },
        });
        await wrapper.vm.$nextTick();

        wrapper.find("[data-test=regenerate-gitlab-webhook-submit]").trigger("click");
        await wrapper.vm.$nextTick();

        expect(store.dispatch).not.toHaveBeenCalled();
    });

    it("When api throws an error, Then error message is displayed", async () => {
        const wrapper = instantiateComponent();

        wrapper.setData({
            repository: {
                gitlab_data: {
                    gitlab_repository_url: "https://example.com/my/repo",
                    gitlab_repository_id: 12,
                },
                normalized_path: "my/repo",
            },
        });
        await wrapper.vm.$nextTick();

        jest.spyOn(store, "dispatch").mockReturnValue(
            Promise.reject({
                response: {
                    status: 404,
                    json: (): Promise<{ error: { code: number; message: string } }> =>
                        Promise.resolve({ error: { code: 404, message: "Error on server" } }),
                },
            })
        );

        wrapper.find("[data-test=regenerate-gitlab-webhook-submit]").trigger("click");
        await wrapper.vm.$nextTick();
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.$data.message_error_rest).toEqual("404 Error on server");
        expect(
            wrapper.find("[data-test=regenerate-gitlab-webhook-submit]").attributes().disabled
        ).toBeTruthy();
    });

    it("When user cancel, Then data are reset", async () => {
        const wrapper = instantiateComponent();
        wrapper.setData({
            repository: {
                gitlab_data: {
                    gitlab_repository_url: "https://example.com/my/repo",
                    gitlab_repository_id: 12,
                },
                normalized_path: "my/repo",
            },
            message_error_rest: "Error server",
            is_updating_webhook: true,
        });
        await wrapper.vm.$nextTick();

        wrapper.find("[data-test=regenerate-gitlab-webhook-cancel]").trigger("click");
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.$data.message_error_rest).toEqual("");
        expect(wrapper.vm.$data.repository).toEqual(null);
        expect(wrapper.vm.$data.is_updating_webhook).toBeFalsy();
    });
});
