<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\ProgramIncrement;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tuleap\ProgramManagement\Program\Backlog\ProgramIncrement\Source\Changeset\Values\ChangesetValueNotFoundException;
use Tuleap\ProgramManagement\Program\Backlog\ProgramIncrement\Source\Changeset\Values\DescriptionValue;
use Tuleap\ProgramManagement\Program\Backlog\ProgramIncrement\Source\Fields\Field;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class DescriptionValueAdapterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Artifact
     */
    private $artifact_data;

    /**
     * @var \PFUser
     */
    private $user;

    /**
     * @var \Tracker_FormElement_Field_Text
     */
    private $description_field;

    /**
     * @var \Tracker_FormElement_Field_String
     */
    private $field_description_data;

    protected function setUp(): void
    {
        $this->description_field      = new \Tracker_FormElement_Field_Text(
            1,
            10,
            null,
            'description',
            'description',
            '',
            true,
            null,
            true,
            true,
            1
        );
        $this->field_description_data = new Field($this->description_field);

        $this->user          = UserTestBuilder::aUser()->withId(101)->build();
        $submitted_on        = 123456789;
        $project             = new \Project(
            ['group_id' => '101', 'unix_group_name' => "project", 'group_name' => 'My project']
        );
        $tracker             = TrackerTestBuilder::aTracker()->withId(1)->withProject($project)->build();
        $this->artifact_data = new Artifact(1, $tracker->getId(), $this->user->getId(), $submitted_on, true);
        $this->artifact_data->setTracker($tracker);
    }

    public function testItThrowsWhenDescriptionValueIsNotFound(): void
    {
        $source_changeset = \Mockery::mock(\Tracker_Artifact_Changeset::class);

        $source_changeset->shouldReceive('getValue')->with($this->description_field)->andReturnNull();
        $source_changeset->shouldReceive('getId')->andReturn(1);

        $adapter = new DescriptionValueAdapter();

        $this->expectException(ChangesetValueNotFoundException::class);

        $replication_data = ReplicationDataAdapter::build($this->artifact_data, $this->user, $source_changeset);
        $adapter->build($this->field_description_data, $replication_data);
    }

    public function testItBuildDescriptionValue(): void
    {
        $source_changeset = \Mockery::mock(\Tracker_Artifact_Changeset::class);

        $changset_value = \Mockery::mock(\Tracker_Artifact_ChangesetValue_String::class);
        $changset_value->shouldReceive('getValue')->once()->andReturn("My description");
        $changset_value->shouldReceive('getFormat')->once()->andReturn("text");
        $source_changeset->shouldReceive('getValue')->with($this->description_field)->andReturn($changset_value);

        $adapter = new DescriptionValueAdapter();

        $expected_data = new DescriptionValue("My description", "text");

        $replication_data = ReplicationDataAdapter::build($this->artifact_data, $this->user, $source_changeset);
        $data             = $adapter->build($this->field_description_data, $replication_data);

        $this->assertEquals($expected_data, $data);
    }
}
