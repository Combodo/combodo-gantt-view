<?php

/*
 * @copyright   Copyright (C) 2010-2024 Combodo SAS
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

if (interface_exists('iPageUIBlockExtension')) {
	/**
	 * Class KanbanUiBlockExtension
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

} else {
	/**
	 * Class GanttUIextension
	 *
	 * Used to loaded resources necessary for Gantt display and edition (dashboard editor)
	 * deprecated since 3.0 use GanttUiBlockExtension instead
	 */
	class GanttUiExtension implements iPageUIExtension
	{
		/**
		 * @inheritdoc
		 */
		public function GetNorthPaneHtml(\iTopWebPage $oPage)
		{
			// SCSS files can't be loaded asynchroniously before of a bug in the output() method prior to iTop 2.6
			$oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/'.Gantt::MODULE_CODE.'/asset/css/style.scss');

		}

		/**
		 * @inheritdoc
		 */
		public function GetSouthPaneHtml(\iTopWebPage $oPage)
		{
			// Do nothing.
		}

		/**
		 * @inheritdoc
		 */
		public function GetBannerHtml(\iTopWebPage $oPage)
		{
			// Do nothing.
		}

	}
}
