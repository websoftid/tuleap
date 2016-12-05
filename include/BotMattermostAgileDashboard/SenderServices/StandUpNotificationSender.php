<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
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

namespace Tuleap\BotMattermostAgileDashboard\SenderServices;

use ProjectManager;
use Tuleap\BotMattermost\BotMattermostLogger;
use Tuleap\BotMattermost\Exception\BotNotFoundException;
use Tuleap\BotMattermost\SenderServices\Sender;
use Tuleap\BotMattermostAgileDashboard\BotAgileDashboard\BotAgileDashboardFactory;

class StandUpNotificationSender
{
    private $bot_agiledashboard_factory;
    private $sender;
    private $notification_builder;
    private $project_manager;
    private $logger;

    public function __construct(
        BotAgileDashboardFactory $bot_agiledashboard_factory,
        Sender $sender,
        StandUpNotificationBuilder $notification_builder,
        ProjectManager $project_manager,
        BotMattermostLogger $logger
    ) {
        $this->bot_agiledashboard_factory = $bot_agiledashboard_factory;
        $this->sender                     = $sender;
        $this->notification_builder       = $notification_builder;
        $this->project_manager            = $project_manager;
        $this->logger                     = $logger;
    }

    public function send()
    {
        try {
            $agile_dashboard_bots = $this->bot_agiledashboard_factory->getAgileDashboardBotsForSummary();
            $projects_ids         = $this->getProjectsIdsFromAgileDashboardBots($agile_dashboard_bots);

            foreach ($projects_ids as $project_id) {
                $bots    = $this->getBotsByProjectId($agile_dashboard_bots, $project_id);
                $project = $this->project_manager->getProject($project_id);
                $admins  = $project->getAdmins();
                $text    = $this->notification_builder->buildNotificationText(
                    $admins[0],
                    $project
                );

                $this->logger->info('start stand up notification in project '.$project->getPublicName());
                $this->logger->debug('project: #'.$project_id.' '.$project->getPublicName());
                if (! $text) {
                    $this->logger->warn('No text');
                }

                $this->sender->pushNotifications($bots, $text);
            }
        } catch (BotNotFoundException $e) {
            $this->logger->error('', $e);
        }
    }

    private function getProjectsIdsFromAgileDashboardBots(array $bots)
    {
        $projects_ids = array();
        foreach ($bots as $bot) {
            $project_id = $bot->getProjectId();
            if (! in_array($project_id, $projects_ids)) {
                $projects_ids[] = $project_id;
            }
        }

        if (empty($projects_ids)) {
            $this->logger->warn('No project found');
        }

        return $projects_ids;
    }

    private function getBotsByProjectId(array $agile_dashboard_bots, $project_id)
    {
        $bots = array();
        foreach ($agile_dashboard_bots as $bot_agile_dashboard) {
            if ($bot_agile_dashboard->getProjectId() === $project_id) {
                $bots[] = $bot_agile_dashboard->getBot();
            }
        }

        return $bots;
    }
}