<?php
/**
 * Copyright (c) Enalean, 2024-Present. All Rights Reserved.
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

namespace Tuleap\CrossTracker\Report\Query\Advanced\SelectBuilder\Metadata;

use LogicException;
use Tuleap\CrossTracker\Report\Query\Advanced\SelectBuilder\IProvideParametrizedSelectAndFromSQLFragments;
use Tuleap\CrossTracker\Report\Query\Advanced\SelectBuilder\Metadata\Semantic\Description\DescriptionSelectFromBuilder;
use Tuleap\CrossTracker\Report\Query\Advanced\SelectBuilder\Metadata\Semantic\Status\StatusSelectFromBuilder;
use Tuleap\CrossTracker\Report\Query\Advanced\SelectBuilder\Metadata\Semantic\Title\TitleSelectFromBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Report\Query\Advanced\Grammar\Metadata;

final class MetadataSelectFromBuilderTest extends TestCase
{
    private function getSelectFrom(Metadata $metadata): IProvideParametrizedSelectAndFromSQLFragments
    {
        $builder = new MetadataSelectFromBuilder(
            new TitleSelectFromBuilder(),
            new DescriptionSelectFromBuilder(),
            new StatusSelectFromBuilder(),
        );

        return $builder->getSelectFrom($metadata);
    }

    public function testItReturnsEmptyAsNothingHasBeenImplemented(): void
    {
        $result = $this->getSelectFrom(new Metadata('id'));
        self::assertEmpty($result->getSelect());
        self::assertEmpty($result->getFrom());
        self::assertEmpty($result->getFromParameters());
    }

    public function testItThrowsIfMetadataNotRecognized(): void
    {
        self::expectException(LogicException::class);
        $this->getSelectFrom(new Metadata('not-existing'));
    }

    public function testItReturnsSQLForTitleSemantic(): void
    {
        $result = $this->getSelectFrom(new Metadata('title'));
        self::assertNotEmpty($result->getSelect());
        self::assertNotEmpty($result->getFrom());
    }

    public function testItReturnsSQLForDescriptionSemantic(): void
    {
        $result = $this->getSelectFrom(new Metadata('description'));
        self::assertNotEmpty($result->getSelect());
        self::assertNotEmpty($result->getFrom());
    }

    public function testItReturnsSQLForStatusSemantic(): void
    {
        $result = $this->getSelectFrom(new Metadata('status'));
        self::assertNotEmpty($result->getSelect());
        self::assertNotEmpty($result->getFrom());
    }
}
