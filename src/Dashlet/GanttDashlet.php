<?php
/*
 * @copyright   Copyright (C) 2010-2024 Combodo SAS
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

use Combodo\iTop\Application\UI\Base\Layout\UIContentBlockUIBlockFactory;

/**
 *
 * Dashlet to embed a gantt into a Dashboard
 *
 */
class GanttDashlet extends Dashlet
{
	protected $iLevelCount;

	/**
	 * GanttDashlet constructor.
	 *
	 * @param \ModelReflection $oModelReflection
	 * @param string $sId
	 */
	public function __construct(\ModelReflection $oModelReflection, $sId)
	{
		parent::__construct($oModelReflection, $sId);
		if ($this->oModelReflection->IsValidClass("UserRequest")
			&& $this->oModelReflection->IsValidAttCode("UserRequest", "title")
			&& $this->oModelReflection->IsValidAttCode("UserRequest", "start_date")
			&& $this->oModelReflection->IsValidAttCode("UserRequest", "end_date")
			&& $this->oModelReflection->IsValidAttCode("UserRequest", "related_request_list"))
		{
			$this->aProperties['title'] = Dict::S('GanttDashlet/Prop:DefaultTitle');
			$this->aProperties['oql'] = 'SELECT UserRequest WHERE id = 1';
			$this->aProperties['class_0'] = 'UserRequest';
			$this->aProperties['depends_on'] = 'parent_request_id';
			$this->aProperties['target_depends_on'] = '';
			$this->aProperties['label_0'] = 'title';
			$this->aProperties['start_date_0'] = 'start_date';
			$this->aProperties['end_date_0'] = 'end_date';
		}
		else {
			$this->aProperties['title'] = Dict::S('GanttDashlet/Prop:DefaultTitle2');
			$this->aProperties['oql'] = 'SELECT Contact WHERE id = 1';
			$this->aProperties['class_0'] = 'Contact';
			$this->aProperties['depends_on'] = '';
			$this->aProperties['target_depends_on'] = '';
			$this->aProperties['label_0'] = '';
			$this->aProperties['start_date_0'] = '';
			$this->aProperties['end_date_0'] = '';
		}
		$this->aProperties['additional_info1_0'] = '';
		$this->aProperties['additional_info2_0'] = '';
		$this->aProperties['percentage_0'] = '';
		$this->aProperties['status_0'] = '';
		$this->aProperties['parent_0'] = '';
		$this->aProperties['class_1'] = '';
		$this->aProperties['label_1'] = '';
		$this->aProperties['start_date_1'] = '';
		$this->aProperties['end_date_1'] = '';
		$this->aProperties['status_1'] = '';
		$this->aProperties['additional_info1_1'] = '';
		$this->aProperties['additional_info2_1'] = '';
		$this->aProperties['percentage_1'] = '';
		$this->aProperties['parent_1'] = '';
		$this->aProperties['class_2'] = '';
		$this->aProperties['label_2'] = '';
		$this->aProperties['start_date_2'] = '';
		$this->aProperties['end_date_2'] = '';
		$this->aProperties['status_2'] = '';
		$this->aProperties['additional_info1_2'] = '';
		$this->aProperties['additional_info2_2'] = '';
		$this->aProperties['percentage_2'] = '';
		$this->aProperties['parent_2'] = '';
		$this->aProperties['save_allowed'] = 0;
	}

	/**
	 * @inheritdoc
	 * @throws \OQLException
	 */
	public function Render($oPage, $bEditMode = false, $aExtraParams = array())
	{
		// Prepare scopes for Gantt object
		$aScope = array(
			'title' => $this->aProperties['title'],
			'oql' => $this->aProperties['oql'],
			'depends_on' => $this->aProperties['depends_on'],
			'target_depends_on' => $this->aProperties['target_depends_on'],
			'save_allowed' => $this->aProperties['save_allowed'],
		);
		$aScope = array_merge($aScope, $this->addFieldsToScope(0));

		if ($aScope['oql'] == ''
			|| $aScope['label'] == ''
			|| $aScope['start_date'] == ''
			|| $aScope['end_date'] == '')
		{
			if ($this->aProperties['class_0'] == '' && isset($this->aProperties['error_class'])) {
				throw new Exception($this->aProperties['error_class']);
			} else {
				throw new Exception(Dict::Format('GanttDashlet/Error:ParametersMissing'));
			}
		}
		if (isset($aExtraParams['query_params']['this->object()']))
		{
			/** @var \DBObject $oObj */
			$oObj = $aExtraParams['query_params']['this->object()'];
			unset($aExtraParams['query_params']);
			$aExtraParams['this->class'] = get_class($oObj);
			$aExtraParams['this->id'] = $oObj->GetKey();
		}
		$aScope['extra_params'] = $aExtraParams;

		$oView = new Gantt($aScope, $bEditMode);
		$sViewId = 'gantt_'.$this->sId.($bEditMode ? '_edit' : ''); // make a unique id (edition occuring in the same DOM)

        $oBlock = $oView->DisplayDashlet($oPage, $sViewId);
        if ($bEditMode) {
            $oBlock->AddSubBlock(UIContentBlockUIBlockFactory::MakeStandard(null, ["gantt-view-blocker"]));
        }

        return $oBlock;
	}

	protected function addFieldsToScope($idx)
	{
		$aScope = array(
			'label' => $this->aProperties['label_'.$idx],
			'start_date' => $this->aProperties['start_date_'.$idx],
			'end_date' => $this->aProperties['end_date_'.$idx],
			'additional_info1' => $this->aProperties['additional_info1_'.$idx],
			'additional_info2' => $this->aProperties['additional_info2_'.$idx],
			'percentage' => $this->aProperties['percentage_'.$idx],
			'parent' => $this->aProperties['parent_'.$idx],
			'class' => $this->aProperties['class_'.$idx],
			'status' => $this->aProperties['status_'.$idx],
		);
		if ($this->aProperties['parent_'.$idx])
		{
			try
			{
				$aScope['parent_fields'] = $this->addFieldsToScope($idx + 1);
			}
			catch (Exception $e)
			{
				$aScope['parent_fields'] = array();
			}
		}

		return $aScope;
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function GetPropertiesFields(\DesignerForm $oForm)
	{
		//title
		$oField = new DesignerTextField('title', Dict::S('UI:DashletHeaderDynamic:Prop-Title'), $this->aProperties['title']);
		$oForm->AddField($oField);

		//oql
		$oField = new DesignerLongTextField('oql', Dict::S('UI:DashletHeaderDynamic:Prop-Query'), $this->aProperties['oql']);
		$oField->SetMandatory();
		$oForm->AddField($oField);

		// Date by field: build the list of possible values (attribute codes + ...)
		$oQuery = null;
		$sClass = null;
		try
		{
			$oQuery = $this->oModelReflection->GetQuery($this->aProperties['oql']);
			$sClass = $oQuery->GetClass();
			$aLink = $this->GetOptions($sClass, false, false, true);
		}
		catch (Exception $e)
		{
			$oQuery = null;
			$sClass = null;
			$aLink = null;
		}

		//depends_on
		if ($aLink != null)
		{
			$oField = new DesignerComboField('depends_on', Dict::S('GanttDashlet/Prop:DependsOn'), $this->aProperties['depends_on']);
			$oField->SetAllowedValues($aLink);
		}
		else
		{
			$oField = new DesignerTextField('depends_on', Dict::S('GanttDashlet/Prop:DependsOn'), $this->aProperties['depends_on']);
			$oField->SetReadOnly();
		}
		$oForm->AddField($oField);

		if ($this->aProperties['depends_on'] != '')
		{
			if (MetaModel::GetAttributeDef($sClass, $this->aProperties['depends_on']) instanceof AttributeLinkedSetIndirect)
			{
				$this->aProperties['target_depends_on'] = MetaModel::GetAttributeDef($sClass,
					$this->aProperties['depends_on'])->GetExtKeyToRemote();
			}
			elseif (MetaModel::GetAttributeDef($sClass, $this->aProperties['depends_on']) instanceof AttributeLinkedSet)
			{
				$this->aProperties['target_depends_on'] = MetaModel::GetAttributeDef($sClass,
					$this->aProperties['depends_on'])->GetExtKeyToMe();
			}
		}

		$oForm->AddField(new DesignerHiddenField('target_depends_on', '', $this->aProperties['target_depends_on']));

		$idx = 0;
		while ($idx < 2)
		{
			$aDateOption = null;
			$aFieldText = null;
			$aLinkParent = null;
			$aNumberField = null;
			try
			{
				$aDateOption = $this->GetOptions($sClass, true, false, false, false);
				$aFieldText = $this->GetOptions($sClass, false, false, false, false);
				$aLinkParent = $this->GetOptions($sClass, false, false, false, true);
				$aNumberField = $this->GetOptions($sClass, false, true);
			}
			catch (Exception $e)
			{
				$aDateOption = null;
				$aFieldText = null;
				$aLinkParent = null;
				$aNumberField = null;
			}

			if ($idx != 0 && $sClass != null)
			{
				$oForm->StartFieldSet(Dict::Format('GanttDashlet/Prop:GroupByInformations', ($idx)));
				$oField = new DesignerTextField('class_'.$idx, '', $sClass);
				$oField->SetReadOnly();
				$oForm->AddField($oField);
			}
			else
			{
				$oField = new DesignerHiddenField('class_'.$idx, '', $sClass);
				$oForm->AddField($oField);
			}

			//label
			$oForm->AddField($this->DisplayDesignerComboField('label_'.$idx, Dict::S('GanttDashlet/Prop:Name'),
				$this->aProperties['label_'.$idx], $aFieldText, ($sClass != null), true));

			//start date
			$oForm->AddField($this->DisplayDesignerComboField('start_date_'.$idx, Dict::S('GanttDashlet/Prop:StartDate'),
				$this->aProperties['start_date_'.$idx], $aDateOption, ($sClass != null), true));

			//end date
			$oForm->AddField($this->DisplayDesignerComboField('end_date_'.$idx, Dict::S('GanttDashlet/Prop:EndDate'),
				$this->aProperties['end_date_'.$idx], $aDateOption, ($sClass != null), true));

			//percentage
			$oForm->AddField($this->DisplayDesignerComboField('percentage_'.$idx, Dict::S('GanttDashlet/Prop:Percentage'),
				$this->aProperties['percentage_'.$idx], $aNumberField, ($sClass != null), false));

			//status_attr
			if($sClass!='')
			{
				$oField = new DesignerTextField('status_'.$idx, Dict::S('GanttDashlet/Prop:ColoredField'), Gantt::GetNameOfStatusField($sClass));
				$oField->SetReadOnly();
				$oForm->AddField($oField);
			}
			else
			{
				$oField = new DesignerHiddenField('status_'.$idx, '', '');
				$oForm->AddField($oField);
			}

			//additional_info
			$oForm->AddField($this->DisplayDesignerComboField('additional_info1_'.$idx,
				Dict::Format('GanttDashlet/Prop:AdditionalInfoLeft'),
				$this->aProperties['additional_info1_'.$idx], $aFieldText, ($sClass != null), false));

			//additional_info2
			$oForm->AddField($this->DisplayDesignerComboField('additional_info2_'.$idx,
				Dict::Format('GanttDashlet/Prop:AdditionalInfoRight'),
				$this->aProperties['additional_info2_'.$idx], $aFieldText, ($sClass != null), false));

			//parent item
			if ($idx == 0)
			{
				$oForm->AddField($this->DisplayDesignerComboField('parent_'.$idx, Dict::S('GanttDashlet/Prop:ParentField'),
					$this->aProperties['parent_'.$idx], $aLinkParent, ($sClass != null), false));

				if ($this->aProperties['parent_'.$idx] != '')
				{
					try
					{
						$sClass = $this->oModelReflection->GetAttributeProperty($sClass, $this->aProperties['parent_'.$idx], 'targetclass');
					}
					catch (Exception $e)
					{
						$sClass = null;
					}
				}
				else
				{
					$sClass = null;
				}
			}
			else
			{
				$oField = new DesignerHiddenField('parent_'.$idx, '', $sClass);
				$oForm->AddField($oField);
			}
			$idx++;
		}
	}

	private function DisplayDesignerComboField($sName, $sLabel, $sValue, $aAllowedValues, $bDisplay, $bMandatory)
	{
		$oField = null;
		if ($bDisplay)
		{
			$oField = new DesignerComboField($sName, $sLabel, $sValue);
			if ($aAllowedValues != null)
			{
				$oField->SetAllowedValues($aAllowedValues);
				$oField->SetDisplayed($bDisplay);
				if ($bMandatory && $bDisplay)
				{
					$oField->SetMandatory();
				}
			}
			else
			{
				$oField->SetDisplayed(false);
			}
		}
		else
		{
			$oField = new DesignerHiddenField($sName, '', $sValue);
		}

		return $oField;
	}

	/*
	 * Get default attribute from xml params
	 */
	private function GetDefaultAttributes($sClass, $sAttribute)
	{
		$sName = "";
		$aClasses = MetaModel::GetModuleSetting(Gantt::MODULE_CODE, Gantt::MODULE_SETTING_CLASSES);
		while (!isset($aClasses[$sClass]) && !MetaModel::IsRootClass($sClass))
		{
			$sClass = MetaModel::GetParentClass($sClass);
		}
		if (isset($aClasses[$sClass]) && isset($aClasses[$sClass][$sAttribute]))
		{
			$sName = $aClasses[$sClass][$sAttribute];
		}

		return $sName;
	}

	public function Update($aValues, $aUpdatedFields)
	{
		if (in_array('oql', $aUpdatedFields)) {
			try {
				$sCurrClass = '';
				$sPrevClass = '';
				try {
					$sCurrQuery = $aValues['oql'];
					$oCurrSearch = $this->oModelReflection->GetQuery($sCurrQuery);
					$sCurrClass = $oCurrSearch->GetClass();
				}
				catch (Exception $e) {
					$sCurrClass = '';
				}
				try {
					$sPrevQuery = $this->aProperties['oql'];
					$oPrevSearch = $this->oModelReflection->GetQuery($sPrevQuery);
					$sPrevClass = $oPrevSearch->GetClass();
				}
				catch (Exception $e) {
					$sPrevClass = '';
				}
				if ($sCurrClass != $sPrevClass) {
					$this->bFormRedrawNeeded = true;
					if ($sCurrClass != "") {
						$this->aProperties['depends_on'] = $this->GetDefaultAttributes($sCurrClass, 'depends_on');
						$this->aProperties['label_0'] = $this->GetDefaultAttributes($sCurrClass, 'name');
						$this->aProperties['start_date_0'] = $this->GetDefaultAttributes($sCurrClass, 'start_date');
						$this->aProperties['end_date_0'] = $this->GetDefaultAttributes($sCurrClass, 'end_date');
						$this->aProperties['percentage_0'] = $this->GetDefaultAttributes($sCurrClass, 'completion');
						$this->aProperties['parent_0'] = $this->GetDefaultAttributes($sCurrClass, 'group_by');

						try {
							$sClass = $this->oModelReflection->GetAttributeProperty($sCurrClass, $this->aProperties['parent_0'], 'targetclass');
							if ($sClass != "") {
								$this->aProperties['label_1'] = $this->GetDefaultAttributes($sClass, 'name');
								$this->aProperties['start_date_1'] = $this->GetDefaultAttributes($sClass, 'start_date');
								$this->aProperties['end_date_1'] = $this->GetDefaultAttributes($sClass, 'end_date');
								$this->aProperties['percentage_1'] = $this->GetDefaultAttributes($sClass, 'completion');
								$this->aProperties['parent_1'] = $this->GetDefaultAttributes($sClass, 'group_by');
							} else {
								$this->aProperties['label_1'] = '';
								$this->aProperties['start_date_1'] = '';
								$this->aProperties['end_date_1'] = '';
								$this->aProperties['percentage_1'] = '';
								$this->aProperties['parent_1'] = '';
							}
							$this->aProperties['class_1'] = $sClass;
							$aValues['class_1'] = $sClass;
						}
						catch (Exception $e)
						{
							$this->aProperties['label_1'] = '';
							$this->aProperties['start_date_1'] = '';
							$this->aProperties['end_date_1'] = '';
							$this->aProperties['percentage_1'] = '';
							$this->aProperties['parent_1'] = '';
							$this->aProperties['class_1'] = '';
							$aValues['class_1'] = '';
						}
						$this->aProperties['additional_info1_1'] = '';
						$this->aProperties['additional_info2_1'] = '';
					}
					else
					{
						$this->aProperties['depends_on'] = '';
						$this->aProperties['label_0'] = '';
						$this->aProperties['start_date_0'] = '';
						$this->aProperties['end_date_0'] = '';
						$this->aProperties['percentage_0'] = '';
						$this->aProperties['parent_0'] = '';
					}
					// wrong but not necessary - unset($aUpdatedFields['group_by']);
					$this->aProperties['target_depends_on'] = '';
					$this->aProperties['additional_info1_0'] = '';
					$this->aProperties['additional_info2_0'] = '';
					$this->aProperties['class_0'] = $sCurrClass;
					$aValues['class_0'] = $sCurrClass;
					array_push($aUpdatedFields, 'class_0');
				}
			}
			catch (Exception $e)
			{
				$this->bFormRedrawNeeded = true;
			}
		}
		if (in_array('depends_on', $aUpdatedFields))
		{
			$this->bFormRedrawNeeded = true;
			if ($this->aProperties['depends_on'])
			{
				if (MetaModel::GetAttributeDef($aValues['class_0'], $aValues['depends_on']) instanceof AttributeLinkedSetIndirect)
				{
					$aValues['target_depends_on'] = MetaModel::GetAttributeDef($aValues['class_0'],
						$aValues['depends_on'])->GetExtKeyToRemote();
					$this->aProperties['target_depends_on'] = $aValues['target_depends_on'];
				}
				elseif (MetaModel::GetAttributeDef($aValues['class_0'], $aValues['depends_on']) instanceof AttributeLinkedSet)
				{
					$aValues['target_depends_on'] = MetaModel::GetAttributeDef($aValues['class_0'],
						$aValues['depends_on'])->GetExtKeyToMe();
					$this->aProperties['target_depends_on'] = $aValues['target_depends_on'];
				}
				$this->aProperties['target_depends_on'] = $aValues['target_depends_on'];
			}
			array_push($aUpdatedFields, 'target_depends_on');
		}
		if (in_array('parent_0', $aUpdatedFields))
		{
			//redraw
			$this->bFormRedrawNeeded = true;
			try
			{
				$sClass = $this->oModelReflection->GetAttributeProperty($this->aProperties['class_0'], $this->aProperties['parent_0'],
					'targetclass');
				if ($sClass != "")
				{
					$this->aProperties['label_1'] = $this->GetDefaultAttributes($sClass, 'name');
					$this->aProperties['start_date_1'] = $this->GetDefaultAttributes($sClass, 'start_date');
					$this->aProperties['end_date_1'] = $this->GetDefaultAttributes($sClass, 'end_date');
					$this->aProperties['percentage_1'] = $this->GetDefaultAttributes($sClass, 'completion');
					$this->aProperties['parent_1'] = $this->GetDefaultAttributes($sClass, 'group_by');
				}
				else
				{
					$this->aProperties['label_1'] = '';
					$this->aProperties['start_date_1'] = '';
					$this->aProperties['end_date_1'] = '';
					$this->aProperties['percentage_1'] = '';
					$this->aProperties['parent_1'] = '';
				}
				$this->aProperties['class_1'] = $sClass;
				$aValues['class_1'] = $sClass;
			}
			catch (Exception $e)
			{
				$this->aProperties['label_1'] = '';
				$this->aProperties['start_date_1'] = '';
				$this->aProperties['end_date_1'] = '';
				$this->aProperties['percentage_1'] = '';
				$this->aProperties['parent_1'] = '';
				$this->aProperties['class_1'] = '';
				$aValues['class_1'] = '';
			}
			$this->aProperties['additional_info1_1'] = '';
			$this->aProperties['additional_info2_1'] = '';
			array_push($aUpdatedFields, 'class_1');
		}

		return parent::Update($aValues, $aUpdatedFields);
	}

	/**
	 * @param array $aParams
	 */
	public function OnUpdate()
	{
		$sCurrQuery = $this->aProperties['oql'];
		try {
			$oQuery = $this->oModelReflection->GetQuery($sCurrQuery);
			$sClass = $oQuery->GetClass();
			$this->aProperties['error_class'] = '';
		}
		catch (UnknownClassOqlException $e) {
			$this->aProperties['error_class'] = $e->GetUserFriendlyDescription();
			$sClass = '';
		}
		catch (OqlException $e) {
			$this->aProperties['error_class'] = $e->GetUserFriendlyDescription();
			$sClass = '';
		}
		catch (Exception $e) {
			// Invalid query, let the user edit the dashlet/dashboard anyhow
			$sClass = '';
		}
		$this->aProperties['class_0'] = $sClass;

		if ($this->aProperties['depends_on'] != '') {
			if (MetaModel::GetAttributeDef($sClass, $this->aProperties['depends_on']) instanceof AttributeLinkedSetIndirect) {
				$this->aProperties['target_depends_on'] = MetaModel::GetAttributeDef($sClass,
					$this->aProperties['depends_on'])->GetExtKeyToRemote();
			} elseif (MetaModel::GetAttributeDef($sClass, $this->aProperties['depends_on']) instanceof AttributeLinkedSet) {
				$this->aProperties['target_depends_on'] = MetaModel::GetAttributeDef($sClass,
					$this->aProperties['depends_on'])->GetExtKeyToMe();
			}
		}
	}
	/**
	 * @inheritdoc
	 */
	public static function GetInfo()
	{
		return array(
			'label' => Dict::S('GanttDashlet:Label'),
			'icon' => 'env-'.utils::GetCurrentEnvironment().'/combodo-gantt-view/asset/img/gantt-dashlet.png',
			'description' => Dict::S('GanttDashlet:Description'),
		);
	}

	/**
	 * @param \DesignerForm $oForm
	 * @param string|null $sOQL
	 */
	public function GetPropertiesFieldsFromOQL(DesignerForm $oForm, $sOQL = null)
	{
		// Default: do nothing since it's not supported
	}

	/**
	 * @param string $sOql
	 *
	 * @return array
	 */
	protected function GetOptions($sClass, $isDate = false, $isNumber = false, $isLink = false, $isLinkParent = false)
	{
		$aFields = array();
		try
		{
			foreach ($this->oModelReflection->ListAttributes($sClass) as $sAttCode => $sAttType)
			{
				// For external fields, find the real type of the target
				$sExtFieldAttCode = $sAttCode;
				$sTargetClass = $sClass;
				if ($isDate)
				{
					if (!is_a($sAttType, 'AttributeDateTime', true)
						&& !is_a($sAttType, 'AttributeDate', true))
					{
						continue;
					}
				}
				elseif ($isLink)
				{
					if (!is_a($sAttType, 'AttributeLinkedSet', true)
						&& !is_a($sAttType, 'AttributeLinkedSetIndirect', true)
						&& !is_a($sAttType, 'AttributeExternalKey', true)
						&& !is_a($sAttType, 'AttributeHierarchicalKey', true))
					{
						continue;
					}
				}
				elseif ($isLinkParent)
				{
					if (!is_a($sAttType, 'AttributeExternalKey', true)
						&& !is_a($sAttType, 'AttributeHierarchicalKey', true))
					{
						continue;
					}
				}
				else
				{
					if (is_a($sAttType, 'AttributeLinkedSet', true)
						|| is_a($sAttType, 'AttributeLinkedSetIndirect', true))
					{
						continue;
					}
				}

				while (is_a($sAttType, 'AttributeExternalField', true))
				{
					$sExtKeyAttCode = $this->oModelReflection->GetAttributeProperty($sTargetClass, $sExtFieldAttCode, 'extkey_attcode');
					$sTargetAttCode = $this->oModelReflection->GetAttributeProperty($sTargetClass, $sExtFieldAttCode, 'target_attcode');
					$sTargetClass = $this->oModelReflection->GetAttributeProperty($sTargetClass, $sExtKeyAttCode, 'targetclass');
					$aTargetAttCodes = $this->oModelReflection->ListAttributes($sTargetClass);
					$sAttType = $aTargetAttCodes[$sTargetAttCode];
					$sExtFieldAttCode = $sTargetAttCode;
				}
				if (is_a($sAttType, 'AttributeMetaEnum', true)
					|| is_a($sAttType, 'AttributeOneWayPassword', true))
				{
					continue;
				}
				if (!$isDate && !$isLink)
				{
					if ( is_a($sAttType, 'AttributeDateTime', true)
						|| is_a($sAttType, 'AttributeCaseLog', true)
						|| is_a($sAttType, 'AttributeText', true)
						|| is_a($sAttType, 'AttributeLongText', true)
						|| is_a($sAttType, 'AttributeStopWatch', true)
						|| is_a($sAttType, 'AttributeHTML', true)
						|| is_a($sAttType, 'AttributeImage', true)
						|| is_a($sAttType, 'AttributeBlob', true)
						|| is_a($sAttType, 'AttributeSubItem', true)
						|| is_a($sAttType, 'AttributeDuration', true)
						|| is_a($sAttType, 'AttributeObsolescenceFlag', true)
					)
					{
						continue;
					}
				}
				$sLabel = $this->oModelReflection->GetLabel($sClass, $sAttCode);
				if (!in_array($sLabel, $aFields))
				{
					$aFields[$sAttCode] = $sLabel;
				}
				elseif (!$isLink and !$isLinkParent)
				{
					$key = array_search($sLabel, $aFields);
					if ($key !== false) {
						unset($aFields[$key]);
					}
					$aFields[$sAttCode] = $sLabel;
				}
			}
			asort($aFields);
		}
		catch (Exception $e)
		{
			// Fallback in case of OQL problem
		}

		return $aFields;
	}
}
