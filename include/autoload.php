<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoloade54301205094db6fae6528cab2ab3896($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'timesheetingplugin' => '/timesheetingPlugin.class.php',
            'tuleap\\timesheeting\\admin\\admincontroller' => '/Timesheeting/Admin/AdminController.php',
            'tuleap\\timesheeting\\admin\\admindao' => '/Timesheeting/Admin/AdminDao.php',
            'tuleap\\timesheeting\\admin\\adminpresenter' => '/Timesheeting/Admin/AdminPresenter.php',
            'tuleap\\timesheeting\\admin\\timesheetingenabler' => '/Timesheeting/Admin/TimesheetingEnabler.php',
            'tuleap\\timesheeting\\admin\\timesheetingugroupdao' => '/Timesheeting/Admin/TimesheetingUgroupDao.php',
            'tuleap\\timesheeting\\admin\\timesheetingugroupretriever' => '/Timesheeting/Admin/TimesheetingUgroupRetriever.php',
            'tuleap\\timesheeting\\admin\\timesheetingugroupsaver' => '/Timesheeting/Admin/TimesheetingUgroupSaver.php',
            'tuleap\\timesheeting\\artifactview\\artifactview' => '/Timesheeting/ArtifactView/ArtifactView.php',
            'tuleap\\timesheeting\\artifactview\\artifactviewbuilder' => '/Timesheeting/ArtifactView/ArtifactViewBuilder.php',
            'tuleap\\timesheeting\\artifactview\\artifactviewpresenter' => '/Timesheeting/ArtifactView/ArtifactViewPresenter.php',
            'tuleap\\timesheeting\\permissions\\permissionsretriever' => '/Timesheeting/Permissions/PermissionsRetriever.php',
            'tuleap\\timesheeting\\router' => '/Timesheeting/Router.php',
            'tuleap\\timesheeting\\timesheetingplugindescriptor' => '/TimesheetingPluginDescriptor.php',
            'tuleap\\timesheeting\\timesheetingplugininfo' => '/TimesheetingPluginInfo.php',
            'tuleap\\timesheeting\\widget\\userwidget' => '/Timesheeting/Widget/UserWidget.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoloade54301205094db6fae6528cab2ab3896');
// @codeCoverageIgnoreEnd
