<?php
/**
 * Copyright (c) Enalean, 2016 - Present. All Rights Reserved.
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

namespace Tuleap\Theme\BurningParrot;

use Event;
use EventManager;
use PFUser;
use ThemeVariant;
use ThemeVariantColor;
use Tuleap\HelpDropdown\HelpDropdownPresenter;
use Tuleap\Layout\CssAsset;
use Tuleap\Layout\CssAssetCollection;
use Tuleap\Layout\IncludeAssets;
use Tuleap\layout\NewDropdown\NewDropdownPresenter;
use Tuleap\Layout\SidebarPresenter;
use Tuleap\Layout\ThemeVariation;
use Tuleap\OpenGraph\OpenGraphPresenter;
use Tuleap\Project\ProjectContextPresenter;
use Tuleap\Theme\BurningParrot\Navbar\PresenterBuilder as NavbarPresenterBuilder;
use Tuleap\User\SwitchToPresenter;
use URLRedirect;

class HeaderPresenterBuilder
{
    /** @var NavbarPresenterBuilder */
    private $navbar_presenter_builder;

    /** @var PFUser */
    private $current_user;

    /** @var string */
    private $imgroot;

    /** @var string */
    private $title;

    /** @var array */
    private $body_classes;

    /** @var array */
    private $main_classes;

    /** @var SidebarPresenter */
    private $sidebar;

    /** @var CssAssetCollection */
    private $css_assets;

    public function build(
        NavbarPresenterBuilder $navbar_presenter_builder,
        PFUser $current_user,
        $imgroot,
        $title,
        $feedback_logs,
        $body_classes,
        $main_classes,
        $sidebar,
        URLRedirect $url_redirect,
        array $toolbar,
        array $breadcrumbs,
        $motd,
        CssAssetCollection $css_assets,
        OpenGraphPresenter $open_graph,
        HelpDropdownPresenter $help_dropdown_presenter,
        NewDropdownPresenter $new_dropdown_presenter,
        ?ProjectContextPresenter $project_context,
        ?SwitchToPresenter $switch_to
    ) {
        $this->navbar_presenter_builder              = $navbar_presenter_builder;
        $this->current_user                          = $current_user;
        $this->imgroot                               = $imgroot;
        $this->title                                 = $title;
        $this->body_classes                          = $body_classes;
        $this->main_classes                          = $main_classes;
        $this->sidebar                               = $sidebar;
        $this->css_assets                            = $css_assets;

        $color = $this->getMainColor();
        $theme_variation = new ThemeVariation($color, $current_user);

        return new HeaderPresenter(
            $this->current_user,
            $this->getPageTitle(),
            $this->imgroot,
            $this->navbar_presenter_builder->build(
                $this->current_user,
                $url_redirect,
                $new_dropdown_presenter
            ),
            $color,
            $this->getStylesheets($theme_variation),
            $feedback_logs,
            $this->getBodyClassesAsString(),
            $this->getMainClassesAsString(),
            $this->sidebar,
            $toolbar,
            $breadcrumbs,
            $motd,
            $open_graph,
            $help_dropdown_presenter,
            $project_context,
            $switch_to
        );
    }

    private function getPageTitle()
    {
        $page_title = \ForgeConfig::get('sys_name');

        if (! empty($this->title)) {
            $page_title = $this->title . ' - ' . $page_title;
        }

        return $page_title;
    }

    private function getStylesheets(ThemeVariation $theme_variation): array
    {
        $core_assets = new IncludeAssets(
            __DIR__ . '/../../../www/assets/core',
            '/assets/core'
        );
        $css_assets = new CssAssetCollection([
            new CssAsset($core_assets, 'tlp'),
            new CssAsset($core_assets, 'BurningParrot/burning-parrot')
        ]);
        $this->css_assets = $css_assets->merge($this->css_assets);

        $stylesheets = [];
        foreach ($this->css_assets->getDeduplicatedAssets() as $css_asset) {
            $stylesheets[] = $css_asset->getFileURL($theme_variation);
        }

        EventManager::instance()->processEvent(
            Event::BURNING_PARROT_GET_STYLESHEETS,
            [
                'variant'     => $this->getMainColor(),
                'stylesheets' => &$stylesheets
            ]
        );

        return $stylesheets;
    }

    private function getMainColor()
    {
        $theme_variant = new ThemeVariant();
        return ThemeVariantColor::buildFromVariant($theme_variant->getVariantForUser($this->current_user));
    }

    private function getMainClassesAsString()
    {
        return implode(' ', $this->main_classes);
    }

    private function getBodyClassesAsString()
    {
        return implode(' ', $this->body_classes);
    }
}
