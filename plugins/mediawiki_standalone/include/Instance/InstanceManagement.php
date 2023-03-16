<?php
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
 *
 */

declare(strict_types=1);

namespace Tuleap\MediawikiStandalone\Instance;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Tuleap\MediawikiStandalone\Configuration\MediaWikiCentralDatabaseParameterGenerator;
use Tuleap\MediawikiStandalone\Configuration\MediaWikiManagementCommandFactory;
use Tuleap\MediawikiStandalone\Instance\Migration\MigrateInstance;
use Tuleap\MediawikiStandalone\Instance\Migration\SwitchMediawikiService;
use Tuleap\NeverThrow\Err;
use Tuleap\NeverThrow\Fault;
use Tuleap\NeverThrow\Result;
use Tuleap\Option\Option;
use Tuleap\Project\ProjectByIDFactory;
use Tuleap\Queue\WorkerEvent;

final class InstanceManagement
{
    public function __construct(
        private LoggerInterface $logger,
        private MediawikiClientFactory $client_factory,
        private RequestFactoryInterface $http_request_factory,
        private StreamFactoryInterface $http_stream_factory,
        private ProjectByIDFactory $project_factory,
        private MediaWikiCentralDatabaseParameterGenerator $central_database_parameter_generator,
        private readonly MediaWikiManagementCommandFactory $command_factory,
        private readonly OngoingInitializationsState $initializations_state,
        private readonly SwitchMediawikiService $switch_mediawiki_service,
    ) {
    }

    public function process(WorkerEvent $worker_event): void
    {
        try {
            $this->processInitializationEvent(CreateInstance::fromEvent($worker_event, $this->project_factory, $this->central_database_parameter_generator));
            $this->processInitializationEvent(MigrateInstance::fromEvent($worker_event, $this->project_factory, $this->central_database_parameter_generator, $this->command_factory, $this->initializations_state, $this->switch_mediawiki_service));

            if (($suspension_event = SuspendInstance::fromEvent($worker_event, $this->project_factory)) !== null) {
                $this->sendRequest($suspension_event);
                return;
            }
            if (($resume = ResumeInstance::fromEvent($worker_event, $this->project_factory)) !== null) {
                $this->sendRequest($resume);
                return;
            }
            if (($delete = DeleteInstance::fromEvent($worker_event, $this->project_factory)) !== null) {
                $this->sendRequest($delete);
                return;
            }
            if (($rename = RenameInstance::fromEvent($worker_event, $this->project_factory)) !== null) {
                $this->sendRequest($rename);
                return;
            }
            if (($log_users_out = LogUsersOutInstance::fromEvent($worker_event, $this->project_factory)) !== null) {
                $this->sendRequest($log_users_out);
                return;
            }
        } catch (\Project_NotFoundException $exception) {
            $this->logger->error(
                sprintf("Payload %s does not reference an existing project", var_export($worker_event->getPayload(), true)),
                ['exception' => $exception]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @psalm-param Option<CreateInstance>|Option<MigrateInstance> $possible_operation_event
     */
    private function processInitializationEvent(Option $possible_operation_event): void
    {
        $possible_operation_event
            ->apply(function (CreateInstance|MigrateInstance $operation): void {
                $operation->process(
                    $this->client_factory->getHTTPClient(),
                    $this->http_request_factory,
                    $this->http_stream_factory,
                    $this->logger,
                )->mapErr(
                /** @psalm-return Err<null> */
                    function (Fault $fault): Err {
                        Fault::writeToLogger($fault, $this->logger);
                        return Result::err(null);
                    }
                );
            });
    }

    private function sendRequest(InstanceOperation $event): void
    {
        try {
            $this->logger->info(sprintf("Processing %s: ", $event->getTopic()));
            $request = $event->getRequest($this->http_request_factory, $this->http_stream_factory);
            $this->logger->debug(sprintf('%s %s', $request->getMethod(), (string) $request->getUri()));
            $response = $this->client_factory->getHTTPClient()->sendRequest($request);
            $this->logger->debug((string) $response->getBody());
            if ($response->getStatusCode() === 200) {
                $this->logger->info(sprintf('Mediawiki %s success', $event::class));
                return;
            }
            $this->logger->error(sprintf('Mediawiki %s error: %s (code: %d)', $event::class, $response->getReasonPhrase(), $response->getStatusCode()));
        } catch (ClientExceptionInterface | ConfigurationErrorException $e) {
            $this->logger->error(sprintf('Cannot connect to mediawiki REST API: %s (%s)', $e->getMessage(), $e::class), ['exception' => $e]);
        }
    }
}
