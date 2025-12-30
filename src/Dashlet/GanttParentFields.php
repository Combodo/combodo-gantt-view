<?php

/**
 * Copyright (C) 2013-2024 Combodo SAS
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 */

class GanttParentFields
{
	public $sClass;
	public $sLabel;
	public $sStartDate;
	public $sEndDate;
	public $sPercentage;
	public $sAdditionalInformation1;
	public $sAdditionalInformation2;
	public $sParent;
	public $sStatus;
	public $aParentFields;

	/**
	 * DependsOnObject constructor.
	 *
	 * @param array $aScope
	 * @param int $nb
	 */
	public function __construct($aScope)
	{
		$this->sLabel = $aScope['label'];
		$this->sStartDate = $aScope['start_date'];
		$this->sEndDate = $aScope['end_date'];
		$this->sPercentage = $aScope['percentage'];
		$this->sAdditionalInformation1 = $aScope['additional_info1'];
		$this->sAdditionalInformation2 = $aScope['additional_info2'];
		$this->sParent = $aScope['parent'];
		$this->sClass = $aScope['class'];
		$this->sStatus = $aScope['status'];
		if ($aScope['parent'] != null && $aScope['parent'] != '') {
			$this->aParentFields = new GanttParentFields($aScope['parent_fields']);
		}
	}

}
