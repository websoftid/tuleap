<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload908524c90fb33e49130a31acebce0c7b($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'botmattermostplugin' => '/botmattermostPlugin.class.php',
            'tuleap\\botmattermost\\admincontroller' => '/BotMattermost/AdminController.php',
            'tuleap\\botmattermost\\adminpresenter' => '/BotMattermost/AdminPresenter.php',
            'tuleap\\botmattermost\\bot\\bot' => '/BotMattermost/Bot/Bot.php',
            'tuleap\\botmattermost\\bot\\botdao' => '/BotMattermost/Bot/BotDao.php',
            'tuleap\\botmattermost\\bot\\botfactory' => '/BotMattermost/Bot/BotFactory.php',
            'tuleap\\botmattermost\\exception\\databaseexception' => '/BotMattermost/Exception/DataBaseException.php',
            'tuleap\\botmattermost\\plugin\\plugindescriptor' => '/BotMattermost/Plugin/PluginDescriptor.php',
            'tuleap\\botmattermost\\plugin\\plugininfo' => '/BotMattermost/Plugin/PluginInfo.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload908524c90fb33e49130a31acebce0c7b');
// @codeCoverageIgnoreEnd
