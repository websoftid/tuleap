<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\AgileDashboard;

use EventManager;
use PFUser;
use Tuleap\AgileDashboard\Milestone\AgileDashboardPromotedMilestonesRetriever;
use Tuleap\AgileDashboard\Milestone\Sidebar\MilestonesInSidebarDao;
use Tuleap\Kanban\CheckSplitKanbanConfiguration;

class AgileDashboardService extends \Service
{
    public function getIconName(): string
    {
        if ($this->isLegacyAgileDashboard()) {
            return 'fa-solid fa-tlp-taskboard';
        }

        return 'fa-solid fa-tlp-backlog';
    }

    public function getInternationalizedName(): string
    {
        if ($this->isLegacyAgileDashboard()) {
            return parent::getInternationalizedName();
        }

        return dgettext('tuleap-agiledashboard', 'Backlog');
    }

    public function getProjectAdministrationName(): string
    {
        if ($this->isLegacyAgileDashboard()) {
            return parent::getProjectAdministrationName();
        }

        return dgettext('tuleap-agiledashboard', 'Backlog');
    }

    public function getInternationalizedDescription(): string
    {
        if ($this->isLegacyAgileDashboard()) {
            return parent::getInternationalizedDescription();
        }

        return dgettext('tuleap-agiledashboard', 'Backlog');
    }

    private function isLegacyAgileDashboard(): bool
    {
        return ! (new CheckSplitKanbanConfiguration(EventManager::instance()))->isProjectAllowedToUseSplitKanban($this->project);
    }

    public function urlCanChange(): bool
    {
        return false;
    }

    public function getUrl(?string $url = null): string
    {
        return AgileDashboardServiceHomepageUrlBuilder::buildSelf()->getUrl($this->project);
    }

    public function getPromotedItemPresenters(PFUser $user, ?string $active_promoted_item_id): array
    {
        return (new AgileDashboardPromotedMilestonesRetriever(
            \Planning_MilestoneFactory::build(),
            $this->project,
            new MilestonesInSidebarDao()
        ))->getSidebarPromotedMilestones($user);
    }
}
