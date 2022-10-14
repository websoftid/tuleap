<?php
/**
 * Copyright (c) Enalean, 2022 - Present. All Rights Reserved.
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

namespace Tuleap\Baseline;

use Tuleap\Baseline\Adapter\Administration\AdminPermissionsPresenter;
use Tuleap\Baseline\Adapter\Administration\UgroupPresenter;
use Tuleap\Test\Stubs\CSRFSynchronizerTokenStub;
use Tuleap\Baseline\Support\NoopSapiEmitter;
use Tuleap\Http\Server\NullServerRequest;
use Tuleap\Layout\BaseLayout;
use Tuleap\Test\Builders\LayoutBuilder;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\Builders\TemplateRendererFactoryBuilder;
use Tuleap\Test\PHPUnit\TestCase;

class ServiceAdministrationControllerTest extends TestCase
{
    use \Tuleap\TemporaryTestDirectory;

    public function testExceptionWhenProjectIsNotAllowed(): void
    {
        $controller = new ServiceAdministrationController(
            \Tuleap\Http\HTTPFactoryBuilder::responseFactory(),
            \Tuleap\Http\HTTPFactoryBuilder::streamFactory(),
            \Tuleap\Baseline\Support\IsProjectAllowedToUsePluginStub::projectIsNotAllowed(),
            TemplateRendererFactoryBuilder::get()->withPath($this->getTmpDir())->build(),
            $this->createMock(\Tuleap\Baseline\Adapter\Administration\IBuildAdminPermissionsPresenter::class),
            $this->createMock(CSRFSynchronizerTokenProvider::class),
            new NoopSapiEmitter(),
        );

        $request = (new NullServerRequest())
            ->withAttribute(\Project::class, ProjectTestBuilder::aProject()->build());

        $this->expectException(\Tuleap\Request\ForbiddenException::class);
        $controller->handle($request);
    }

    public function testExceptionWhenServiceIsNotActivated(): void
    {
        $controller = new ServiceAdministrationController(
            \Tuleap\Http\HTTPFactoryBuilder::responseFactory(),
            \Tuleap\Http\HTTPFactoryBuilder::streamFactory(),
            \Tuleap\Baseline\Support\IsProjectAllowedToUsePluginStub::projectIsAllowed(),
            TemplateRendererFactoryBuilder::get()->withPath($this->getTmpDir())->build(),
            $this->createMock(\Tuleap\Baseline\Adapter\Administration\IBuildAdminPermissionsPresenter::class),
            $this->createMock(CSRFSynchronizerTokenProvider::class),
            new NoopSapiEmitter(),
        );

        $project = ProjectTestBuilder::aProject()
            ->withoutServices()
            ->build();

        $request = (new NullServerRequest())->withAttribute(\Project::class, $project);

        $this->expectException(\Tuleap\Request\ForbiddenException::class);
        $controller->handle($request);
    }

    public function testHappyPath(): void
    {
        $token = CSRFSynchronizerTokenStub::buildSelf();

        $presenter_builder = $this->createMock(
            \Tuleap\Baseline\Adapter\Administration\IBuildAdminPermissionsPresenter::class
        );
        $presenter_builder->method('getPresenter')->willReturn(
            new AdminPermissionsPresenter(
                '/admin/url',
                $token,
                [
                    new UgroupPresenter(104, 'Lorem ipsum', true),
                ],
                []
            )
        );

        $token_provider = $this->createMock(CSRFSynchronizerTokenProvider::class);
        $token_provider->method('getCSRF')
            ->willReturn($token);

        $controller = new ServiceAdministrationController(
            \Tuleap\Http\HTTPFactoryBuilder::responseFactory(),
            \Tuleap\Http\HTTPFactoryBuilder::streamFactory(),
            \Tuleap\Baseline\Support\IsProjectAllowedToUsePluginStub::projectIsAllowed(),
            TemplateRendererFactoryBuilder::get()->withPath($this->getTmpDir())->build(),
            $presenter_builder,
            $token_provider,
            new NoopSapiEmitter(),
        );

        $service = $this->createMock(BaselineTuleapService::class);
        $service->method('displayAdministrationHeader');
        $service->method('displayFooter');

        $project = ProjectTestBuilder::aProject()->build();
        $project->addUsedServices([\baselinePlugin::SERVICE_SHORTNAME, $service]);

        $request = (new NullServerRequest())
            ->withAttribute(\Project::class, $project)
            ->withAttribute(BaseLayout::class, LayoutBuilder::build());

        $response = $controller->handle($request);
        self::assertStringContainsString(
            'Lorem ipsum',
            $response->getBody()->getContents()
        );
    }
}