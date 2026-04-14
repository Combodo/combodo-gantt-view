<?php
/*
 * @copyright   Copyright (C) 2010-2024 Combodo SAS
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

use Combodo\iTop\Application\TwigBase\Twig\TwigHelper;
use Combodo\iTop\Application\UI\Base\Layout\UIContentBlockUIBlockFactory;

/**
 * Display of a named gantt into any WebPage
 */
class Gantt
{
	const MODULE_CODE = 'combodo-gantt-view';
	const DEFAULT_TITLE = '';
	const MODULE_SETTING_CLASSES = 'classes';
	const MODULE_SETTING_DEFAULT_COLORS = 'default_colors';
	const MODULE_SETTING_COLORED_FIELD = 'colored_field';// most of the time the status with life cycle

	protected $sTitle;
	protected $aExtraParams;
	protected $sOql;
	protected $sLabel;
	protected $sStartDate;
	protected $sEndDate;
	protected $sPercentage;
	protected $sDependsOn;
	protected $sStatus;
	protected $sTargetDependsOn;
	protected $sAdditionalInformation1;
	protected $sAdditionalInformation2;
	protected $bEditMode;
	protected $bSaveAllowed;
	protected $sParent;
	protected $aParentFields;
	protected $aScope;

	/**
	 * gantt constructor.
	 *
	 * @param array $aScope
	 * @param string $sTitle
	 * @param string $sMode
	 * @param string $sTimeframe
	 */
	public function __construct($aScope, $bEditMode = false)
	{
		$this->sTitle = $aScope['title'];
		$this->bEditMode = $bEditMode;
		$this->sOql = $aScope['oql'];
		$this->aExtraParams = (array_key_exists('extra_params', $aScope)) ? $aScope['extra_params'] : array();
		$this->sDependsOn = $aScope['depends_on'];
		$this->sTargetDependsOn = $aScope['target_depends_on'];
		$this->sLabel = $aScope['label'];
		$this->sStartDate = $aScope['start_date'];
		$this->sEndDate = $aScope['end_date'];
		$this->sPercentage = $aScope['percentage'];
		$this->sAdditionalInformation1 = $aScope['additional_info1'];
		$this->sAdditionalInformation2 = $aScope['additional_info2'];
		$this->sParent = $aScope['parent'];
		if ($this->sParent != '')
		{
			$this->aParentFields = new GanttParentFields($aScope['parent_fields']);
		}
		$this->sStatus = $aScope['status'];
		$this->aScope = $aScope;
		$this->bSaveAllowed = ($aScope['save_allowed'] == "true");//in first time fixed value
	}

	public function GetGanttValues()
	{
		$aDescription = array();
		//table for associate an id with a line number
		$aLinkedTable = array();

		if (isset($this->aExtraParams['query_params']))
		{
			$aQueryParams = $this->aExtraParams['query_params'];
		}
		elseif (isset($this->aExtraParams['this->class']) && isset($this->aExtraParams['this->id']))
		{
			$oObj = MetaModel::GetObject($this->aExtraParams['this->class'], $this->aExtraParams['this->id']);
			$aQueryParams = $oObj->ToArgsForQuery();
		}
		else
		{
			$aQueryParams = array();
		}

		$oQuery = DBSearch::FromOQL($this->sOql, $aQueryParams);

		$aFields = array($this->sLabel, $this->sStartDate, $this->sEndDate);
		if ($this->sDependsOn != null && $this->sDependsOn != '') {
			array_push($aFields, $this->sDependsOn);
		}
		if ($this->sPercentage != null && $this->sPercentage != '') {
			array_push($aFields, $this->sPercentage);
		}
		if ($this->sAdditionalInformation1 != '') {
			array_push($aFields, $this->sAdditionalInformation1);
		}
		if ($this->sAdditionalInformation2 != '') {
			array_push($aFields, $this->sAdditionalInformation2);
		}
		if ($this->sParent != '') {
			array_push($aFields, $this->sParent);
		}
		if ($this->sStatus != '') {
			array_push($aFields, $this->sStatus);
		}
		$oResultSql = new DBObjectSet($oQuery);
		$oResultSql->OptimizeColumnLoad([$oQuery->GetClassAlias() => $aFields]);
		$sClass = $oResultSql->GetClass();
		$aOrderBy = array();
		if ($this->sParent != '')
		{
			$aOrderBy[$sClass.'.'.$this->sParent] = true;
		}
		$aOrderBy[$sClass.'.'.$this->sStartDate] = true;
		$oResultSql->SetOrderBy($aOrderBy);
		$i = 0;
		$aLevelParent1 = array();
		$aLevelParent2 = array();
		while ($oRow = $oResultSql->Fetch()) {
			$Level = 0;
			$canWrite = $this->bSaveAllowed;
			if ($this->sParent != '' && $oRow->Get($this->sParent) != null) {
				if (!array_key_exists($oRow->Get($this->sParent), $aLevelParent1)) {
					$aFieldsParent1 = $this->aParentFields;
					$oObj = MetaModel::GetObject($aFieldsParent1->sClass, $oRow->Get($this->sParent), false /* MustBeFound */);
					if ($oObj != null) {
						if ($canWrite) {
							$iFlags = $oObj->GetAttributeFlags($this->sStartDate);
							if (($iFlags & OPT_ATT_READONLY) === OPT_ATT_READONLY
								|| ($iFlags & OPT_ATT_SLAVE) === OPT_ATT_SLAVE
								|| ($iFlags & OPT_ATT_HIDDEN) === OPT_ATT_HIDDEN) {
								$canWrite = false;
							}
						}
						if ($aFieldsParent1->sParent != '' && $oObj->Get($aFieldsParent1->sParent) != '' && !array_key_exists($oObj->Get($aFieldsParent1->sParent),
								$aLevelParent2)) {
							$aFieldsParent2 = $aFieldsParent1->aParentFields;
							$oObjParent = MetaModel::GetObject($aFieldsParent2->sClass, $oRow->Get($aFieldsParent1->sParent),
								false /* MustBeFound */);
							if ($oObjParent != null)
							{
								$canWriteParent = $this->bSaveAllowed;
								if ($canWriteParent)
								{
									$iFlags = $oObjParent->GetAttributeFlags($aFieldsParent2->sStartDate);
									if (($iFlags & OPT_ATT_READONLY) === OPT_ATT_READONLY
										|| ($iFlags & OPT_ATT_SLAVE) === OPT_ATT_SLAVE
										|| ($iFlags & OPT_ATT_HIDDEN) === OPT_ATT_HIDDEN)
									{
										$canWriteParent = false;
									}
								}
								$aDescription[$i] = $this->createRow($oObjParent, $aFieldsParent2->sClass, $aFieldsParent2, $Level,
									$canWriteParent, true);
								$i++;
								$aLevelParent2[$oRow->Get($this->sParent)] = $Level;
								$Level++;
							}
						}
						$aDescription[$i] = $this->createRow($oObj, $aFieldsParent1->sClass, $aFieldsParent1, $Level, $canWrite, true);
						$i++;
						$aLevelParent1[$oRow->Get($this->sParent)] = $Level;
						$Level++;
					}
				}
				else
				{
					$Level = $aLevelParent1[$oRow->Get($this->sParent)] + 1;
				}
			}

			$aDescription[$i] = $this->createRow($oRow, $sClass, $this, $Level, $canWrite);
			$aLinkedTable[$oRow->Get("id")] = $i;
			$i++;
		}

		return $this->renameLink($aDescription, $aLinkedTable);
	}

	private function createRow($oRow, $sClass, $aFields, $iLevel, $canWrite = false, $hasChild = false)
	{
		$aRow = array();
		$aRow['id'] = $sClass.'_'.$oRow->GetKey();
		$aRow['name'] = htmlspecialchars($oRow->Get($aFields->sLabel), ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, "UTF-8");
		if ($aFields->sPercentage != null && $aFields->sPercentage != '') {
			$aRow['progress'] = $oRow->Get($aFields->sPercentage);
		} else {
			$aRow['progress'] = 0;
		}
		$aRow['progressByWorklog'] = true;
		$aRow['relevance'] = 0;
		$aRow['type'] = "";
		$aRow['typeId'] = "";
		$aRow['description'] = "";
		$aRow['code'] = "";
		$aRow['level'] = $iLevel;
		if ($aFields->sStatus != null && $aFields->sStatus != '')
		{
			$aRow['status'] = $oRow->Get($aFields->sStatus);
		}
		else
		{
			$aRow['status'] = "";
		}
		if ($hasChild == false && $aFields->sDependsOn != null && $aFields->sDependsOn != '')
		{
			if (empty($oRow->Get($aFields->sDependsOn))) {
				$aRow['dependson'] = [];
			} else {
				if ($aFields->sTargetDependsOn == '') {
					$aRow['dependson'] = ($oRow->Get($aFields->sDependsOn) == 0) ? [] : array($oRow->Get($aFields->sDependsOn) => $oRow->Get($aFields->sDependsOn));
				} else {
					$aRow['dependson'] = $oRow->Get($aFields->sDependsOn)->GetColumnAsArray($aFields->sTargetDependsOn);
				}
			}
		}
		else
		{
			$aRow['dependson'] = [];
		}
		$aRow['canWrite'] = false;
		$sFormat = "Y-m-d H:i:s";
		if (strlen($oRow->Get($aFields->sStartDate)) < 12)
		{
			$sFormat = "Y-m-d";
		}
		$iStart = date_format(date_create_from_format($sFormat, $oRow->Get($aFields->sStartDate)), 'U') * 1000;
		$aRow['start'] = $iStart;
		$iEnd = 0;
		if ($oRow->Get($aFields->sEndDate) != null)
		{
			$sFormat = "Y-m-d H:i:s";
			if (strlen($oRow->Get($aFields->sEndDate)) < 12)
			{
				$sFormat = "Y-m-d";
			}
			$iEnd = date_format(date_create_from_format($sFormat, $oRow->Get($aFields->sEndDate)), 'U') * 1000;
		}
		else
		{
			//add one year to the start_date
			$iEnd = (date_format(date_create_from_format($sFormat, $oRow->Get($aFields->sStartDate)), 'U') + 33072000) * 1000;
		}
		$aRow['end'] = $iEnd;
		$aRow['duration'] = $iEnd - $iStart;
		if ($aFields->sAdditionalInformation1 != '') {
			//$aGroupBy['group1']->MakeValueLabel($this->m_oFilter, $sStateValue, $sStateValue);
			$aRow['info1'] = htmlspecialchars($oRow->Get($aFields->sAdditionalInformation1), ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, "UTF-8");
		}
		if ($aFields->sAdditionalInformation2 != '') {
			$aRow['info2'] = htmlspecialchars($oRow->Get($aFields->sAdditionalInformation2), ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, "UTF-8");
		}
		$aRow['collapsed'] = true;
		$aRow['assigs'] = [];
		$aRow['hasChild'] = $hasChild;

		return $aRow;
	}

	private function renameLink($aDescription, $aLinkedTable)
	{
		foreach ($aDescription as &$aRow)
		{
			if (sizeof($aRow['dependson']) > 0)
			{
				$newLink = array();
				foreach ($aRow['dependson'] as $sIdRow)
				{
					if (array_key_exists($sIdRow, $aLinkedTable))
					{
						array_push($newLink, $aLinkedTable[$sIdRow] + 1);
					}
				}
				$aRow['depends'] = implode(",", $newLink);
			}
			else
			{
				$aRow['depends'] = "";
			}
		}

		return $aDescription;
	}

	public function GetGanttDescription()
	{
		$oQuery = DBSearch::FromOQL($this->sOql);
		$sClass = $oQuery->GetClass();
		$oAttLabelDef = MetaModel::GetAttributeDef($sClass, $this->sLabel);
		$oAttLabelStartDate = MetaModel::GetAttributeDef($sClass, $this->sStartDate);
		$oAttLabelEndDate = MetaModel::GetAttributeDef($sClass, $this->sEndDate);
		$oAttAdditionalInformation1Def = null;
		$oAttAdditionalInformation2Def = null;
		if ($this->sAdditionalInformation1 != '')
		{
			$oAttAdditionalInformation1Def = MetaModel::GetAttributeDef($sClass, $this->sAdditionalInformation1);
		}
		if ($this->sAdditionalInformation2 != '')
		{
			$oAttAdditionalInformation2Def = MetaModel::GetAttributeDef($sClass, $this->sAdditionalInformation2);
		}
		$aHandlerOptions = array(
			'attr_label' => $oAttLabelDef->GetLabel(),
			'attr_start_date' => $oAttLabelStartDate->GetLabel(),
			'attr_end_date' => $oAttLabelEndDate->GetLabel(),
			'attr_add_info1' => ($oAttAdditionalInformation1Def != null) ? $oAttAdditionalInformation1Def->GetLabel() : '',
			'attr_add_info2' => ($oAttAdditionalInformation2Def != null) ? $oAttAdditionalInformation2Def->GetLabel() : '',
		);

		return $aHandlerOptions;
	}

	private function GetListeColorsByStatus()
	{
		$sClass=$this->aScope['class'];
		$aColorsByStatus = array();
		$aDefaultColors = MetaModel::GetModuleSetting(static::MODULE_CODE, static::MODULE_SETTING_DEFAULT_COLORS);
		$aClasses = MetaModel::GetModuleSetting(static::MODULE_CODE, static::MODULE_SETTING_CLASSES);
		while (!isset($aClasses[$sClass]) && !MetaModel::IsRootClass($sClass))
		{
			$sClass = MetaModel::GetParentClass($sClass);
		}
		if (isset($aClasses[$sClass]))
		{
			if (isset($aClasses[$sClass][static::MODULE_SETTING_DEFAULT_COLORS]))
			{
				$aDefaultColors = $aClasses[$sClass][static::MODULE_SETTING_DEFAULT_COLORS];
			}
			$aColorsByStatus = $aClasses[$sClass]['values'];
		}
		$aColorsByStatus[''] = $aDefaultColors;
		if(isset($this->aParentFields) && isset($this->aParentFields->sClass))
		{
			$sClass=$this->aParentFields->sClass;
			$aClasses = MetaModel::GetModuleSetting(static::MODULE_CODE, static::MODULE_SETTING_CLASSES);
			while (!isset($aClasses[$sClass]) && !MetaModel::IsRootClass($sClass))
			{
				$sClass = MetaModel::GetParentClass($sClass);
			}
			if (isset($aClasses[$sClass]))
			{
				$aColorsByStatus = array_merge($aClasses[$sClass]['values'], $aColorsByStatus);
			}
		}

		return $aColorsByStatus;
	}

	public static function GetNameOfStatusField($sClass)
	{
		$sName = '';
		$aClasses = MetaModel::GetModuleSetting(static::MODULE_CODE, static::MODULE_SETTING_CLASSES);
		while (!isset($aClasses[$sClass]) && !MetaModel::IsRootClass($sClass))
		{
			$sClass = MetaModel::GetParentClass($sClass);
		}
		if (isset($aClasses[$sClass]) && isset($aClasses[$sClass][static::MODULE_SETTING_COLORED_FIELD]))
		{
			$sName = $aClasses[$sClass][static::MODULE_SETTING_COLORED_FIELD];
		}
		if ($sName == '')
		{
			$sName = MetaModel::GetStateAttributeCode($sClass);
		}

		return $sName;
	}

	/**
	 * Inserts the gantt (as a div) in the dashboard
	 *
	 * @param \WebPage $oP The page used for the display
	 * @param string $sId
	 *
	 * @throws \Exception
	 * @throws \OQLException
	 */
	public function DisplayDashlet(WebPage $oP, $sId = '')
	{
		//render
		if ($sId == "") {
			$sId = "gantt".mt_rand();
		}
		//analyse of oql in order to resolve variableExpression
		$aScope = $this->aScope;
		$aScope["extra_params"] = $this->aExtraParams;
		$aData = array('sId' => $sId);
		$aData['sTitle'] = $this->sTitle;
		$aData['bEditMode'] = $this->bEditMode;
		$aData['sScope'] = json_encode($aScope);
		$aData['aDescription'] = $this->GetGanttDescription();
		$aData['sAbsUrlModulesRoot'] = utils::GetAbsoluteUrlModulesRoot();
		$aData['sModuleVersion'] = utils::GetCompiledModuleVersion(static::MODULE_CODE);
		$aData['bPrintable'] = $oP->isPrintableVersion();

		$aData['dateFormat'] = MetaModel::GetConfig()->Get('date_and_time_format')['default']['date'];
		$aData['dateFormat'] = str_replace(array("y", "Y", "m", "d"), array("yy", "yyyy", "MM", "dd"), $aData['dateFormat']);
		$aData['listeStatus'] = $this->GetListeColorsByStatus();

		$oBlock = UIContentBlockUIBlockFactory::MakeStandard("");
		TwigHelper::RenderIntoBlock($oP, $oBlock, MODULESROOT.'combodo-gantt-view/view', 'GanttViewerDashlet', $aData);

		return $oBlock;
	}

	/**
	 * Inserts the gantt (as a div) in the dashboard
	 *
	 * @deprecated since iTop 3.0 use DisplayDashlet
	 *
	 * @param string $sId
	 *
	 * @param \WebPage $oP The page used for the display
	 *
	 * @throws \OQLException
	 * @throws \Exception
	 */
	public function DisplayDashletLegacy(\WebPage $oP, $sId = '')
	{
		//render
		if ($sId == "") {
			$sId = "gantt".mt_rand();
		}
		//analyse of oql in order to resolve variableExpression
		$aScope = $this->aScope;
		$aScope["extra_params"] = $this->aExtraParams;
		$aData = array('sId' => $sId);
		$aData['sTitle'] = $this->sTitle;
		$aData['bEditMode'] = $this->bEditMode;
		$aData['sScope'] = json_encode($aScope);
		$aData['aDescription'] = $this->GetGanttDescription();
		$aData['sAbsUrlModulesRoot'] = utils::GetAbsoluteUrlModulesRoot();
		$aData['sModuleVersion'] = utils::GetCompiledModuleVersion(static::MODULE_CODE);
		$aData['bPrintable'] = $oP->isPrintableVersion();

		$aData['dateFormat'] = MetaModel::GetConfig()->Get('date_and_time_format')['default']['date'];
		$aData['dateFormat'] = str_replace(array("y", "Y", "m", "d"), array("yy", "yyyy", "MM", "dd"), $aData['dateFormat']);
		$aData['listeStatus'] = $this->GetListeColorsByStatus();

		$oP->add_twig_template(MODULESROOT.'combodo-gantt-view/view_legacy', 'GanttViewerDashlet', $aData);
	}

	/**
	 * Inserts the gantt (as a div) at the current position into the given page
	 *
	 * @param \WebPage $oP The page used for the display
	 * @param string $sId
	 *
	 * @throws \Exception
	 * @throws \OQLException
	 */
	public function Display(WebPage $oP, $sId = '')
	{
		if ($sId == "") {
			$sId = "gantt".mt_rand();
		}
		$aScope = $this->aScope;
		$aScope["extra_params"] = $this->aExtraParams;
		//CSS
		$oP->LinkStylesheetFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/dateField/jquery.dateField.css');
		$oP->LinkStylesheetFromModule('combodo-gantt-view/asset/lib/jQueryGantt/platform.css');
		$oP->LinkStylesheetFromModule('combodo-gantt-view/asset/lib/jQueryGantt/gantt.css');
		$oP->LinkStylesheetFromModule('combodo-gantt-view/asset/css/style.css');

		if ($oP->isPrintableVersion()) {
			$oP->LinkStylesheetFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttPrint.css');
			$oP->LinkStylesheetFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/valueSlider/mb.slider.css');
		}
		//JS
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/jquery.livequery.1.1.1.min.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/jquery.timers.js?v=$sModuleVersion');

		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/utilities.js?v=$sModuleVersion');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/forms.js?v=$sModuleVersion');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/date.js?v=$sModuleVersion');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/dialogs.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/layout.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/i18nJs.js');

		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/dateField/jquery.dateField.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/JST/jquery.JST.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/valueSlider/jquery.mb.slider.js');

		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/svg/jquery.svg.min.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/libs/jquery/svg/jquery.svgdom.1.8.js');

		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttUtilities.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttTask.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttDrawerSVG.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttZoom.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttGridEditor.js');
		$oP->LinkScriptFromModule('combodo-gantt-view/asset/lib/jQueryGantt/ganttMaster.js');
		//render
		$aData = array('sId' => $sId);
		$aData['sTitle'] = $this->sTitle;
		$aData['bEditMode'] = $this->bEditMode;
		$aData['bPrintable'] = $oP->isPrintableVersion();
		//$aData['bSaveAllowed'] = true;
		$aData['bSaveAllowed'] = $this->isSaveAllowed($this->aScope['class'], $this->bSaveAllowed);
		$aData['sScope'] = json_encode($aScope);
		$aData['aDescription'] = $this->GetGanttDescription();
		$aData['sAbsUrlModulesRoot'] = utils::GetAbsoluteUrlModulesRoot();
		$aData['sModuleVersion'] = utils::GetCompiledModuleVersion(static::MODULE_CODE);
		$aData['dateFormat'] = "yy-MM-dd";
		$aData['listeStatus'] = $this->GetListeColorsByStatus();
		$aData['aExtraParams'] = $this->aExtraParams;

		$oBlock = UIContentBlockUIBlockFactory::MakeStandard("");
		TwigHelper::RenderIntoBlock($oP, $oBlock, MODULESROOT.'combodo-gantt-view/view', 'GanttViewer', $aData);

		return $oBlock;
	}

	private function isSaveAllowed($sClass, $bSaveAllowed)
	{
		$oReflectionClass = new ReflectionClass($sClass);

		return (UserRights::IsActionAllowed($sClass, UR_ACTION_MODIFY) == UR_ALLOWED_YES)
			&& ($oReflectionClass->IsSubclassOf('cmdbAbstractObject'))
			&& $bSaveAllowed;
	}

	/**
	 * @return mixed
	 */
	public function GetTitle()
	{
		return $this->sTitle;
	}

	/**
	 * @param mixed $sTitle
	 *
	 * @return $this
	 */
	public function SetTitle($sTitle)
	{
		$this->sTitle = $sTitle;

		return $this;
	}

}

