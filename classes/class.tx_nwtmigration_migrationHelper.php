<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_migration_basic.php'));

class tx_nwtmigration_migrationHelper {

	// TODO dry-run mode?
	// TODO log all actions!
	
	// TODO presetValues  
	// TODO migration todos für manuelle änderungen
	
	// TODO Dateien lesen/schreiben/searchReplace
	
	// TODO TV helper
	
	// TODO flexForm helper
	
	/*

getTSValue($page_uid, $key);
appendOnFile($line);
appendOnTemplate($page_uid, $field, $line)

updateTVTemplateMapping(); // just update no changes
getTVMapping($to_uid)
setTVMapping($to_uid)

createContent($fields)
copyContent($source,$destination)
moveContent

searchContent($page, $type='')

t3lib_BEfunc::getRecordWSOL

// get flex helper

setFFvalue

	*/
	
	public function __construct() {
		$this->tce = t3lib_div::makeInstance("t3lib_TCEmain");
        $tce->stripslashes_values = 0;
	}
	
	
	public static function getInstance() {
		if (!$GLOBALS['nwt_migration']['tx_nwtmigration_migrationHelper']) {
			$GLOBALS['nwt_migration']['tx_nwtmigration_migrationHelper'] = t3lib_div::makeInstance('tx_nwtmigration_migrationHelper');
		}
		return $GLOBALS['nwt_migration']['tx_nwtmigration_migrationHelper'];
	}
	
	
	
	
	/**
	 * see http://typo3.org/documentation/document-library/core-documentation/doc_core_api/4.1.0/view/3/3/
	 *
	 * @param array $cmd
	 */
	public function execTCEcmd($cmd) {
		$this->tce->start(array(),$cmd);
		$this->tce->process_cmdmap();
	}
	
	/**
	 * see http://typo3.org/documentation/document-library/core-documentation/doc_core_api/4.1.0/view/3/3/
	 *
	 * @param array $data
	 */
	public function execTCEdata($data) {
		$this->tce->start($data,array());
		$this->tce->process_datamap();		
	}
	
	/**
	 * create a new page with given values
	 * 
	 * @param array $page array of field/value for the pages table; if pid > 0: insert into this page; if pid < 0: insert after this page
	 * @return int page id of the new page
	 */
	public function createPage($page) {
		if (is_array($page) && $page['pid']) { 
			$key = substr(md5(serialize($page)), 0, 8);
			$data['pages']['NEW'.$key] = $page;
			$this->execTCEdata($data);
			return $this->tce->substNEWwithIDs['NEW'.$key];
		}
		return false;
	}
	
	
	
	public function updateData($table, $uid, $values) {
		if ($table && $uid && is_array($values)) { 
			$data[$table][$uid] = $values;
			$this->execTCEdata($data);
			return true;
		} 
		return false;
	}
	
	public function clearCache() { // TODO clearCacheAll
   		$this->tce->start(array(),array());
		$this->tce->clear_cacheCmd('all');
		return true;
	}	
	
	public function copyPage($source, $destination) {
		if (is_int($source) && is_int($destination)) { 
			$cmd['pages'][$source]['copy'] = $destination;
			$this->execTCEcmd($cmd);
			return $this->tce->copyMappingArray['pages'][$source];
		}
		return false;
	}
	
	public function movePage($source, $destination) {
		if (is_int($source) && is_int($destination)) { 
			$cmd['pages'][$source]['move'] = $destination;
			$this->execTCEcmd($cmd);
			return true;
		} 
		return false;
	}
	
	public function getContent($pageid, $column, $fields = '*', $search = array()) {
		$where = '1 ';
		$where .= ' AND hidden = 0 AND deleted = 0 ';

		if (is_array($search)) {
			foreach ($search as $field => $searchvalue) {
				$where .= ' AND '. mysql_real_escape_string($field) . ' = "' . mysql_real_escape_string($searchvalue) . '" ';		
			}
		}

		if (t3lib_extMgm::isLoaded('templavoila')) {
			$page = $this->tce->recordInfo('pages', $pageid, 'tx_templavoila_flex');

			$tvflex = $this->initflexForm($page['tx_templavoila_flex']);
			$pidlist = $this->getFFvalue($tvflex, $column);
			
			$where .= ' AND FIND_IN_SET(uid, "'. $pidlist .'") ';
			
		} else {
			// TODO else if normal CE
			die('currently only TV website are supported!');
		}
		
		$contents = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				$fields,
				'tt_content',
				$where
		);
		
		return $contents;
	}
	
		
	
	/**
	 * Converts $this->cObj->data['pi_flexform'] from XML string to flexForm array.
	 *
	 * @param	string		Field name to convert
	 * @return	void
	 */
	public function initflexForm($xml)	{
		$array = array();
		
		if (!is_array($xml) && $xml)	{
			$array = t3lib_div::xml2array($xml);
			if (!is_array($array))	$array=array();
		}
		return $array;
	}

	/**
	 * Return value from somewhere inside a FlexForm structure
	 *
	 * @param	array		FlexForm data
	 * @param	string		Field name to extract. Can be given like "test/el/2/test/el/field_templateObject" where each part will dig a level deeper in the FlexForm data.
	 * @param	string		Sheet pointer, eg. "sDEF"
	 * @param	string		Language pointer, eg. "lDEF"
	 * @param	string		Value pointer, eg. "vDEF"
	 * @return	string		The content.
	 */
	public function getFFvalue($T3FlexForm_array,$fieldName,$sheet='sDEF',$lang='lDEF',$value='vDEF')	{
		$sheetArray = is_array($T3FlexForm_array) ? $T3FlexForm_array['data'][$sheet][$lang] : '';
		if (is_array($sheetArray))	{
			return $this->getFFvalueFromSheetArray($sheetArray,explode('/',$fieldName),$value);
		}
	}
	
	public function setFFvalue($T3FlexForm_array,$fieldName,$newvalue ,$sheet='sDEF',$lang='lDEF',$value='vDEF')	{
		$T3FlexForm_array['data'][$sheet][$lang][$fieldName][$value] = $newvalue;
		return $T3FlexForm_array;
	}

	/**
	 * Returns part of $sheetArray pointed to by the keys in $fieldNameArray
	 *
	 * @param	array		Multidimensiona array, typically FlexForm contents
	 * @param	array		Array where each value points to a key in the FlexForms content - the input array will have the value returned pointed to by these keys. All integer keys will not take their integer counterparts, but rather traverse the current position in the array an return element number X (whether this is right behavior is not settled yet...)
	 * @param	string		Value for outermost key, typ. "vDEF" depending on language.
	 * @return	mixed		The value, typ. string.
	 * @access private
	 * @see pi_getFFvalue()
	 */
	public function getFFvalueFromSheetArray($sheetArray,$fieldNameArr,$value)	{

		$tempArr=$sheetArray;
		foreach($fieldNameArr as $k => $v)	{
			if (t3lib_div::testInt($v))	{
				if (is_array($tempArr))	{
					$c=0;
					foreach($tempArr as $values)	{
						if ($c==$v)	{
							#debug($values);
							$tempArr=$values;
							break;
						}
						$c++;
					}
				}
			} else {
				$tempArr = $tempArr[$v];
			}
		}
		return $tempArr[$value];
	}
	
}
?>