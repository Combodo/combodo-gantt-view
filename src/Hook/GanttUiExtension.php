<?php

/*
 * @copyright   Copyright (C) 2010-2024 Combodo SAS
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

/**
 * Class GanttUiBlockExtension
 *
 * @since 3.0.0
 *
 * Used to loaded resources necessary for kanban display and edition (dashboard editor)
 */
class GanttUiBlockExtension implements iPageUIBlockExtension
{
	public function GetBannerBlock()
	{
		//compile scss file
		utils::GetCSSFromSASS('env-'.utils::GetCurrentEnvironment().'/'.Gantt::MODULE_CODE.'/asset/css/style.scss');
	}

	public function GetHeaderBlock()
	{
		// TODO: Implement GetHeaderBlock() method.
	}

	public function GetFooterBlock()
	{
		// TODO: Implement GetFooterBlock() method.
	}
}
