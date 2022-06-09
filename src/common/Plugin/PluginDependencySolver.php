<?php
/**
 * Copyright (c) Enalean, 2013 - Present. All Rights Reserved.
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

/**
 * This class is responsible of detecting if there are unmet dependencies in plugin
 */
class PluginDependencySolver // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
{
    /** @var PluginManager */
    private $plugin_manager;

    public function __construct(PluginManager $plugin_manager)
    {
        $this->plugin_manager = $plugin_manager;
    }

    /**
     * Get plugin names that are still installed and which depends on the given plugin
     *
     * @return array of strings
     */
    public function getInstalledDependencies(Plugin $plugin)
    {
        return $this->getMissingDependencies($plugin, $this->plugin_manager->getAllPlugins());
    }

    /**
     * @return array of strings
     */
    public function getEnabledDependencies(Plugin $plugin)
    {
        return $this->getMissingDependencies($plugin, $this->plugin_manager->getEnabledPlugins());
    }

    /**
     * Get plugin names that should already be installed for the given plugin name
     *
     * @return array of strings
     *
     * @throws \Tuleap\Plugin\InvalidPluginNameException
     */
    public function getUninstalledDependencies($plugin_name)
    {
        $plugin = $this->plugin_manager->getPluginDuringInstall($plugin_name);
        return $this->getUnmetMissingDependencies($plugin, 'getPluginByName');
    }

    /**
     * Get plugin names that should already be enabled for the given plugin name
     *
     * @return array of strings
     */
    public function getDisabledDependencies(Plugin $plugin)
    {
        return $this->getUnmetMissingDependencies($plugin, 'getEnabledPluginByName');
    }

    private function getUnmetMissingDependencies(Plugin $plugin, $method)
    {
        $unmet_dependencies = [];
        foreach ($plugin->getDependencies() as $dependency_name) {
            $dependency_plugin = $this->plugin_manager->$method($dependency_name);
            if (! $dependency_plugin) {
                $unmet_dependencies[] = $dependency_name;
            }
        }
        return $unmet_dependencies;
    }

    private function getMissingDependencies(Plugin $plugin, array $plugins_collection)
    {
        $missing_dependencies = [];
        foreach ($plugins_collection as $candidate_plugin) {
            if (in_array($plugin->getName(), $candidate_plugin->getDependencies())) {
                $missing_dependencies[] = $candidate_plugin->getName();
            }
        }
        return $missing_dependencies;
    }
}
