<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload535892899b914f7d635967750ca3a550($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'labelplugin' => '/labelPlugin.class.php',
            'tuleap\\label\\exceptions\\duplicatedparametervalueexception' => '/Label/Exceptions/DuplicatedParameterValueException.php',
            'tuleap\\label\\exceptions\\emptyparameterexception' => '/Label/Exceptions/EmptyParameterException.php',
            'tuleap\\label\\exceptions\\invalidparametertypeexception' => '/Label/Exceptions/InvalidParameterTypeException.php',
            'tuleap\\label\\exceptions\\missingmandatoryparameterexception' => '/Label/Exceptions/MissingMandatoryParameterException.php',
            'tuleap\\label\\labeleditemqueryparser' => '/Label/LabeledItemQueryParser.php',
            'tuleap\\label\\plugin\\plugindescriptor' => '/Label/Plugin/PluginDescriptor.php',
            'tuleap\\label\\plugin\\plugininfo' => '/Label/Plugin/PluginInfo.php',
            'tuleap\\label\\rest\\resourcesinjector' => '/Label/REST/ResourcesInjector.php',
            'tuleap\\label\\rest\\v1\\collectionoflabeleditemsrepresentation' => '/Label/REST/v1/CollectionOfLabeledItemsRepresentation.php',
            'tuleap\\label\\rest\\v1\\labeleditemrepresentation' => '/Label/REST/v1/LabeledItemRepresentation.php',
            'tuleap\\label\\rest\\v1\\projectresource' => '/Label/REST/v1/ProjectResource.php',
            'tuleap\\label\\widget\\projectlabeleditems' => '/Label/Widget/ProjectLabeledItems.php',
            'tuleap\\label\\widget\\projectlabeleditemspresenter' => '/Label/Widget/ProjectLabeledItemsPresenter.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload535892899b914f7d635967750ca3a550');
// @codeCoverageIgnoreEnd
