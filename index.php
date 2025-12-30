<?php

/**
 *  @copyright   Copyright (C) 2010-2019 Combodo SARL
 *  @license     http://opensource.org/licenses/AGPL-3.0
 */

require_once '../approot.inc.php';
require_once APPROOT.'application/application.inc.php';
//remove require itopdesignformat at the same time as version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0
if (! defined("ITOP_DESIGN_LATEST_VERSION")) {
	require_once APPROOT.'setup/itopdesignformat.class.inc.php';
}
if (version_compare(ITOP_DESIGN_LATEST_VERSION, '3.0') < 0) {
	require_once APPROOT.'application/itopwebpage.class.inc.php';
}
require_once APPROOT.'application/startup.inc.php';
require_once APPROOT.'application/loginwebpage.class.inc.php';

$oGanttController = new GanttViewController(MODULESROOT.'combodo-gantt-view/view', 'combodo-gantt-view');
$oGanttController->SetDefaultOperation('GanttViewer');
$oGanttController->HandleOperation();
