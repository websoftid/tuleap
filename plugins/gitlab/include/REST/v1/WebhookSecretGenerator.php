<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\Gitlab\REST\v1;

use GitPermissionsManager;
use Luracast\Restler\RestException;
use Tuleap\Gitlab\API\GitlabRequestException;
use Tuleap\Gitlab\API\GitlabResponseAPIException;
use Tuleap\Gitlab\Repository\GitlabRepository;
use Tuleap\Gitlab\Repository\GitlabRepositoryFactory;
use Tuleap\Gitlab\Repository\Project\GitlabRepositoryProjectRetriever;
use Tuleap\Gitlab\Repository\Webhook\Bot\CredentialsRetriever;
use Tuleap\Gitlab\Repository\Webhook\WebhookCreator;
use Tuleap\REST\I18NRestException;

class WebhookSecretGenerator
{
    /**
     * @var GitlabRepositoryFactory
     */
    private $repository_factory;
    /**
     * @var GitlabRepositoryProjectRetriever
     */
    private $project_retriever;
    /**
     * @var GitPermissionsManager
     */
    private $permissions_manager;
    /**
     * @var CredentialsRetriever
     */
    private $credentials_retriever;
    /**
     * @var WebhookCreator
     */
    private $webhook_creator;

    public function __construct(
        GitlabRepositoryFactory $repository_factory,
        GitlabRepositoryProjectRetriever $project_retriever,
        GitPermissionsManager $permissions_manager,
        CredentialsRetriever $credentials_retriever,
        WebhookCreator $webhook_creator
    ) {
        $this->repository_factory    = $repository_factory;
        $this->project_retriever     = $project_retriever;
        $this->permissions_manager   = $permissions_manager;
        $this->credentials_retriever = $credentials_retriever;
        $this->webhook_creator       = $webhook_creator;
    }

    public function regenerate(
        GitlabRepositoryWebhookSecretPatchRepresentation $patch_representation,
        \PFUser $current_user
    ): void {
        $repository = $this->repository_factory->getGitlabRepositoryByGitlabRepositoryIdAndPath(
            $patch_representation->gitlab_repository_id,
            $patch_representation->gitlab_repository_url,
        );
        if (! $repository) {
            throw new RestException(404);
        }

        if (! $this->isUserAllowedToUpdateBotApiToken($current_user, $repository)) {
            throw new RestException(404);
        }

        $credentials = $this->credentials_retriever->getCredentials($repository);
        if (! $credentials) {
            throw new I18NRestException(
                400,
                \dgettext('tuleap-gitlab', 'No credentials found to contact the GitLab server.')
            );
        }

        try {
            $this->webhook_creator->generateWebhookInGitlabProject($credentials, $repository);
        } catch (GitlabRequestException $e) {
            throw new I18NRestException(
                400,
                sprintf(
                    dgettext(
                        'tuleap-gitlab',
                        'New secret has been generated, but we could not update the GitLab hooks. GitLab server error: %s'
                    ),
                    $e->getGitlabServerMessage()
                )
            );
        } catch (GitlabResponseAPIException $e) {
            throw new I18NRestException(
                500,
                dgettext(
                    'tuleap-gitlab',
                    "We managed to generate a new secret and to send it to the server, but couldn't parse the response. You should check that everything is ok."
                )
            );
        }
    }

    private function isUserAllowedToUpdateBotApiToken(\PFUser $current_user, GitlabRepository $repository): bool
    {
        foreach ($this->project_retriever->getProjectsGitlabRepositoryIsIntegratedIn($repository) as $project) {
            if ($this->permissions_manager->userIsGitAdmin($current_user, $project)) {
                return true;
            }
        }

        return false;
    }
}
