<?php
/*
 * @copyright   Copyright (C) 2010-2024 Combodo SAS
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'combodo-gantt-view/1.2.1',
	array(
		// Identification
		//
		'label'        => 'Gantt',
		'category'     => 'business',

		// Setup
		//
		'dependencies' => array(),
		'mandatory'    => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'src/Dashlet/Gantt.php',
			'src/Dashlet/GanttParentFields.php',
			'src/Dashlet/GanttDashlet.php',
			'src/Controller/AbstractGanttViewController.php',
			'src/Controller/AjaxGanttViewController.php',
			'src/Controller/GanttViewController.php',
			'src/Hook/GanttUiExtension.php',
		),
		'webservice' => array(),
		'data.struct' => array(// add your 'structure' definition XML files here,
		),
		'data.sample' => array(// add your sample data XML files here,
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any 

		// Default settings
		//
		'settings' => array(),
	)
);

