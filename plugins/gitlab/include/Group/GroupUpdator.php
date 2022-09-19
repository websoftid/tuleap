<?php
/**
 * Copyright (c) Enalean, 2022 - present. All Rights Reserved.
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
 *  along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Group;

use Luracast\Restler\RestException;
use Tuleap\Git\Branch\BranchName;
use Tuleap\Git\Branch\InvalidBranchNameException;
use Tuleap\Gitlab\REST\v1\Group\GitlabGroupPATCHRepresentation;

final class GroupUpdator
{
    private const FAKE_BRANCH_NAME = 'branch_name';

    public function __construct(private UpdateBranchPrefixOfGroup $update_group_link)
    {
    }

    /**
     * @throws RestException
     */
    public function updateBranchPrefixOfGroupLinkFromPATCHRequest(
        GitlabGroup $gitlab_group_link,
        GitlabGroupPATCHRepresentation $gitlab_group_link_representation,
    ): void {
        $prefix_branch_name = $gitlab_group_link_representation->create_branch_prefix;

        if ($prefix_branch_name === null) {
            return;
        }

        try {
            BranchName::fromBranchNameShortHand($prefix_branch_name . self::FAKE_BRANCH_NAME);

            $this->update_group_link->updateBranchPrefixOfGroupLink(
                $gitlab_group_link->id,
                $prefix_branch_name,
            );
        } catch (InvalidBranchNameException $exception) {
            throw new RestException(
                400,
                sprintf(
                    "The branch name prefix '%s' produces invalid git branch names",
                    $prefix_branch_name
                )
            );
        }
    }
}
