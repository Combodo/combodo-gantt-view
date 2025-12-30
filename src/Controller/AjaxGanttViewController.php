<?php

/**
 *  @copyright   Copyright (C) 2010-2024 Combodo SAS
 *  @license     http://opensource.org/licenses/AGPL-3.0
 */

class AjaxGanttViewController extends AbstractGanttViewController
{
	public function OperationGetProject()
	{
		$aScope = $_POST;
		$oGantt = new Gantt($aScope);
		$aParams = [];
		$aParams['tasks'] =  $oGantt->GetGanttValues();//$oGantt->GetGanttValuesTest();
		$aParams['selectedRow'] = 0;
		$aParams['deletedTaskIds'] = [];
		$aParams['resources'] = [];
		$aParams['roles'] = [];
		$aParams['canWrite'] = false;
		$aParams['canDelete'] = false;
		$aParams['canWriteOnParent'] = false;
		$aParams['canAdd'] = false;

		$this->DisplayJSONPage($aParams);

	}
}
