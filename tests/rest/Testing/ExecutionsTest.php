<?php
/**
 * Copyright (c) Enalean, 2014. All rights reserved
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/
 */
namespace Testing;

require_once dirname(__FILE__).'/../bootstrap.php';

/**
 * @group ArtifactsTest
 */
class ExecutionsTest extends BaseTest {

    public function testPutExecutions() {
        $initial_value = 'failed';
        $new_value     = 'blocked';

        $execution = $this->getFirstExecutionForValid73Campaign();
        $this->assertEquals($initial_value, $execution['status']);

        $response = $this->getResponse($this->client->put('testing_executions/'. $execution['id'], null, json_encode(array(
            'status' => $new_value
        ))));

        $this->assertEquals($response->getStatusCode(), 200);

        $updated_execution = $this->getFirstExecutionForValid73Campaign();
        $this->assertEquals($new_value, $updated_execution['status']);

        $this->getResponse($this->client->put('testing_executions/'. $execution['id'], null, json_encode(array(
            'status' => $initial_value
        ))));
    }

    private function getFirstExecutionForValid73Campaign() {
        $campaign = $this->getValid73Campaign();

        $all_executions_request  = $this->client->get('testing_campaigns/'. $campaign['id'] .'/testing_executions');
        $all_executions_response = $this->getResponse($all_executions_request);

        $executions      = $all_executions_response->json();
        $first_execution = $executions[0];
        $this->assertEquals('Import default template', $first_execution['definition']['summary']);

        return $first_execution;
    }
}