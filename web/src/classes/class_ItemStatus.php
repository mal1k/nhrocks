<?

	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
	#                                                                    #
	# This file may not be redistributed in whole or part.               #
	# eDirectory is licensed on a per-domain basis.                      #
	#                                                                    #
	# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
	#                                                                    #
	# http://www.edirectory.com | http://www.edirectory.com/license.html #
	######################################################################
	\*==================================================================*/

	# ----------------------------------------------------------------------------------------------------
	# * FILE: /classes/class_itemStatus.php
	# ----------------------------------------------------------------------------------------------------

	class ItemStatus {

		##################################################
		# PRIVATE
		##################################################

		var $default;
		var $value;
		var $name;
		var $style;

        public function __construct($additionalStatus = false) {
			$this->default = "P";
			$this->value = ["A", "S", "E", "P"];
			$this->name = [system_showText(LANG_LABEL_ACTIVE), system_showText(LANG_LABEL_SUSPENDED), system_showText(LANG_LABEL_EXPIRED), system_showText(LANG_LABEL_PENDING)];
			$this->style = ["status-active", "status-suspended", "status-expired", "status-pending"];

			if ($additionalStatus) {
			    $auxValue = ["R", "F", "Q"];
			    $auxName = [system_showText(LANG_SITEMGR_IMPORT_RUNNING), system_showText(LANG_SITEMGR_IMPORT_FINISHED), system_showText(LANG_SITEMGR_IMPORT_QUEUE)];
			    $auxStyle = ["status-running", "status-finished", "status-pending"];

                $this->value = array_merge($this->value, $auxValue);
                $this->name = array_merge($this->name, $auxName);
                $this->style = array_merge($this->style, $auxStyle);

            }
		}

		function getValues() {
			return $this->value;
		}

		function getNames() {
			return $this->name;
		}

		function getStyles() {
			return $this->style;
		}

		function union($key, $value) {
			for ($i=0; $i<count($key); $i++) {
				$aux[$key[$i]] = $value[$i];
			}
			return $aux;
		}

		function getValueName() {
			return $this->union($this->getValues(), $this->getNames());
		}

		function getValueStyle() {
			return $this->union($this->getValues(), $this->getStyles());
		}

		function getDefault() {
			return $this->default;
		}

		function getName($value) {
			$value_name = $this->getValueName();
			return $value_name[$value];
		}

		function getStyle($value) {
			$value_style = $this->getValueStyle();
			return $value_style[$value];
		}

		##################################################
		# PRIVATE
		##################################################

		##################################################
		# PUBLIC
		##################################################

		function getStatus($value) {
			if ($this->getName($value)) return string_ucwords($this->getName($value));
			else return string_ucwords($this->getStatus($this->getDefaultStatus()));
		}

		function getStatusWithStyle($value) {
			if ($this->getName($value)) {
				//space | ie8 bug fix//
				return " <span class=".$this->getStyle($value).">".string_ucwords($this->getName($value))."</span> ";
			}
			return string_ucwords($this->getStatusWithStyle($this->getDefaultStatus()));
		}

		function getDefaultStatus() {
			return $this->getDefault();
		}

		function getStatusValues() {
			return $this->getValues();
		}

		function getStatusNames() {
			return $this->getNames();
		}

		##################################################
		# PUBLIC
		##################################################

	}

?>
