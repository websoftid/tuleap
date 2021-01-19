<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Repository;

use GitPermissionsManager;
use GitUserNotAdminException;
use PFUser;
use Project;
use Tuleap\DB\DBTransactionExecutor;
use Tuleap\Gitlab\Repository\Project\GitlabRepositoryProjectDao;
use Tuleap\Gitlab\Repository\Token\GitlabBotApiTokenDao;
use Tuleap\Gitlab\Repository\Webhook\PostPush\Commits\CommitTuleapReferenceDao;
use Tuleap\Gitlab\Repository\Webhook\Secret\SecretDao;

class GitlabRepositoryDeletor
{
    /**
     * @var GitlabRepositoryProjectDao
     */
    private $gitlab_repository_project_dao;

    /**
     * @var GitPermissionsManager
     */
    private $git_permissions_manager;
    /**
     * @var DBTransactionExecutor
     */
    private $db_transaction_executor;
    /**
     * @var SecretDao
     */
    private $secret_dao;
    /**
     * @var GitlabRepositoryDao
     */
    private $gitlab_repository_dao;
    /**
     * @var GitlabBotApiTokenDao
     */
    private $gitlab_bot_api_token_dao;
    /**
     * @var CommitTuleapReferenceDao
     */
    private $commit_tuleap_reference_dao;

    public function __construct(
        GitPermissionsManager $git_permissions_manager,
        DBTransactionExecutor $db_transaction_executor,
        GitlabRepositoryProjectDao $gitlab_repository_project_dao,
        SecretDao $secret_dao,
        GitlabRepositoryDao $gitlab_repository_dao,
        GitlabBotApiTokenDao $gitlab_bot_api_token_dao,
        CommitTuleapReferenceDao $commit_tuleap_reference_dao
    ) {
        $this->git_permissions_manager       = $git_permissions_manager;
        $this->db_transaction_executor       = $db_transaction_executor;
        $this->gitlab_repository_project_dao = $gitlab_repository_project_dao;
        $this->secret_dao                    = $secret_dao;
        $this->gitlab_repository_dao         = $gitlab_repository_dao;
        $this->gitlab_bot_api_token_dao      = $gitlab_bot_api_token_dao;
        $this->commit_tuleap_reference_dao   = $commit_tuleap_reference_dao;
    }

    /**
     * @throws GitUserNotAdminException
     * @throws GitlabRepositoryNotInProjectException
     * @throws GitlabRepositoryNotIntegratedInAnyProjectException
     */
    public function deleteRepositoryInProject(GitlabRepository $gitlab_repository, Project $project, PFUser $user): void
    {
        if (! $this->git_permissions_manager->userIsGitAdmin($user, $project)) {
            throw new GitUserNotAdminException();
        }

        $this->db_transaction_executor->execute(function () use ($gitlab_repository, $project) {
            $repository_id = $gitlab_repository->getId();
            $project_id    = (int) $project->getID();

            $project_ids = $this->gitlab_repository_project_dao->searchProjectsTheGitlabRepositoryIsIntegratedIn(
                $repository_id
            );

            if (count($project_ids) === 0) {
                throw new GitlabRepositoryNotIntegratedInAnyProjectException($repository_id);
            }

            if (! in_array($project_id, $project_ids, true)) {
                throw new GitlabRepositoryNotInProjectException($repository_id, $project_id);
            }

            if (count($project_ids) > 1) {
                $this->gitlab_repository_project_dao->removeGitlabRepositoryIntegrationInProject(
                    $repository_id,
                    $project_id
                );
            } else {
                $this->secret_dao->deleteGitlabRepositoryWebhookSecret($repository_id);
                $this->gitlab_repository_project_dao->removeGitlabRepositoryIntegrationInProject(
                    $repository_id,
                    $project_id
                );
                $this->gitlab_bot_api_token_dao->deleteGitlabBotToken($repository_id);
                $this->commit_tuleap_reference_dao->deleteCommitsInGitlabRepository($repository_id);
                $this->gitlab_repository_dao->deleteGitlabRepository($repository_id);
            }
        });
    }
}
