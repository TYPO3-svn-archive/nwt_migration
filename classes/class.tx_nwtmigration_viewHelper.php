<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_migration_basic.php'));

class tx_nwtmigration_viewHelper {

	// TODO do not use static methods
	// TODO get instance
	// TODO list of migrations	
	
	public function arrayToTable($header, $data) {
		$content .= '<table cellspacing="0" cellpadding="0" border="0" class="typo3-dblist">';
					
		$content .= '<tbody><tr>
			<td nowrap="nowrap" class="c-table-row-spacer"><img height="8" width="1" alt="" src="clear.gif" /></td>
			<td nowrap="nowrap" colspan="4" class="c-table-row-spacer"/>
		</tr>';
		
		$content .= '<tr>';
		
		foreach ($header as $head) {
			$content .= '<td nowrap="nowrap" class="c-headLineTable">'.
				$GLOBALS['LANG']->getLL($head) . 
				'</td>';
		}
			
		$content .= '</tr>';

		foreach ($data as $row) {
			$content .= '<tr>';
			foreach ($row as $value) {
				$content .= '<td nowrap="nowrap">'.
					$value . 
					'</td>';
			}
			$content .= '</tr>';
		}
		$content .= '</tbody></table>';
		
		return $content;
		
	}
	
	public function getMigrationStatusIcon($statuscode) {
		
		$path = '../../../../'. t3lib_extMgm::siteRelPath('nwt_migration'). 'mod1/gfx/';
		$imagesrc = '';
		$imagetitle = '';
		
		switch ($statuscode) {
			case tx_nwtmigration_migration_basic::STATUS_UNKNOWN;
				$imagesrc = 'icon_unknown.gif';
				$imagetitle = 'Unknown Status';		
				break;
				
			case tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED;
				$imagesrc = 'icon_add.gif';
				$imagetitle = 'Migration not yet applied';		
				break;
				
			case tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL:
				$imagesrc = 'icon_ok2.gif';
				$imagetitle = 'Migration successful applied';		
				break;
				
			case tx_nwtmigration_migration_basic::STATUS_FAILED:
				$imagesrc = 'icon_fatalerror.gif';
				$imagetitle = 'Migration failed';		
				break;
		}
		
		if ($imagesrc) {
			$imagetag = '<img src="'. $path . $imagesrc . '" title="' . $imagetitle . '" alt="' . $imagetitle . '" />';
		}
		
		return $imagetag;
	}
	
	public function getMigrationActionLink(tx_nwtmigration_migration_basic $migration) {
		$status = $migration->getStatus();
		
		switch ($status) {
			case tx_nwtmigration_migration_basic::STATUS_UNKNOWN;
				return ''; // no action possible
				break;
				
			case tx_nwtmigration_migration_basic::STATUS_NOTAPPLIED;
				return self::getActionLink($GLOBALS['LANG']->getLL('migration_runup'), 'runUp', array('mid' => $migration->getId()));
				break;
				
			case tx_nwtmigration_migration_basic::STATUS_SUCCESSFUL:
				return self::getActionLink($GLOBALS['LANG']->getLL('migration_rundown'), 'runDown', array('mid' => $migration->getId()));
				break;
				
			case tx_nwtmigration_migration_basic::STATUS_FAILED:
				return ''; // no action possible		
				break;
		}
		
	}
	
	public function getActionLink($text, $action, $parameter = array()) {
		$parameterString = t3lib_div::implodeArrayForUrl('', $parameter);
		
		return '<a href="?action=' . urlencode($action) . $parameterString . '">' . $text . '</a>';
	}
	
}
?>