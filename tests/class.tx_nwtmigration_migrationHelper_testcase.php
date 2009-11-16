<?php

require_once(t3lib_extMgm::extPath('nwt_migration', 'classes/class.tx_nwtmigration_defaultController.php'));


class tx_nwtmigration_migrationHelper_testcase extends tx_phpunit_testcase {

	/*
		for TCE commands see http://typo3.org/documentation/document-library/core-documentation/doc_core_api/4.1.0/view/3/3/
	*/
	
	public function testExecTCEcmdCallsTCEMain() {
		$helper = new tx_nwtmigration_migrationHelper();
		
		$tce = $this->getMock('t3lib_TCEmain', array('start','process_cmdmap'));
		$helper->tce = $tce;

		$cmd['pages'][1203]['move'] = 303;  // Moves page id=1203 to the first position in page 303
		
		$tce->expects($this->once())
			->method('start')
			->with(array(), $this->equalTo($cmd));
			
		$tce->expects($this->once())
			->method('process_cmdmap');
		
		$helper->execTCEcmd($cmd);
	}	
	
	
	
	
	
	public function testExecTCEDataCallsTCEMain() {
		$helper = new tx_nwtmigration_migrationHelper();
		
		$tce = $this->getMock('t3lib_TCEmain', array('start','process_datamap'));
		$helper->tce = $tce;

		// create a new page into page 45
		$data['pages']['NEW9823be87'] = array(
    		"title" => "The page title",
    		"pid" => "45"
		);
		
		$tce->expects($this->once())
			->method('start')
			->with($this->equalTo($data), array());
			
		$tce->expects($this->once())
			->method('process_datamap');
		
		$helper->execTCEdata($data);
	}
	
	
	
	
	public function testCreatePage() {
		// array of fields for the new page
		// if pid >0 : create page "into" the page with the given pid on first sub position
		// if pid <0 : create page "after" the page with the given pid
		$newpage = array(
    		"title" => "The page title",
    		"pid" => "45",
		);
		
		$key = substr(md5(serialize($newpage)),0,8);
		$expecteddata['pages']['NEW'.$key] = $newpage;
		
		$helper = new tx_nwtmigration_migrationHelper;

		$helper = $this->getMock('tx_nwtmigration_migrationHelper', array('execTCEdata'));
		$helper->expects($this->once())
			->method('execTCEdata')
			->with($this->equalTo($expecteddata));
			
		// fake new page id (can't mock this)
		$helper->tce->substNEWwithIDs['NEW'.$key] = 1234;
		
		$pageid = $helper->createPage($newpage);
		
		$this->assertEquals(1234, $pageid);
	}
	
	
	
	
	public function testUpdateData() {
		$data = array(
    		"title" => "The new page title",
			"hidden" => 0,
		);

		$expecteddata['pages'][4711] = $data;
		
		$helper = new tx_nwtmigration_migrationHelper;
		$helper = $this->getMock('tx_nwtmigration_migrationHelper', array('execTCEdata'));
		$helper->expects($this->once())
			->method('execTCEdata')
			->with($this->equalTo($expecteddata));
		
		$helper->updateData('pages', 4711, $data);
	}
	
	
	
	public function testClearCache()	{
		$helper = new tx_nwtmigration_migrationHelper();
		
		$tce = $this->getMock('t3lib_TCEmain', array('start','clear_cacheCmd'));
		$helper->tce = $tce;
		
		$tce->expects($this->once())
			->method('start')
			->with($this->equalTo(array()), $this->equalTo(array()));
			
		$tce->expects($this->once())
			->method('clear_cacheCmd')
			->with($this->equalTo('all'));
			
		$helper->clearCache();
	}

	
	
	
	
	public function testCopyPage()	{
		//Copies page id=1203 to the position after page 303
		$expectedcmd['pages'][1203]['copy'] = -303;

		$helper = new tx_nwtmigration_migrationHelper;
		$helper = $this->getMock('tx_nwtmigration_migrationHelper', array('execTCEcmd'));
		$helper->expects($this->once())
			->method('execTCEcmd')
			->with($this->equalTo($expectedcmd));
		
		$helper->tce->copyMappingArray['pages'][1203] = 1234;
			
		$pageid = $helper->copyPage(1203, -303);
		
		$this->assertEquals(1234, $pageid);
	}	
	
	
	
	public function testMovePage()	{
		//Moves page id=1203 to the first position in p. 303
		$expectedcmd['pages'][1203]['move'] = 303;

		$helper = new tx_nwtmigration_migrationHelper;
		$helper = $this->getMock('tx_nwtmigration_migrationHelper', array('execTCEcmd'));
		$helper->expects($this->once())
			->method('execTCEcmd')
			->with($this->equalTo($expectedcmd));
		
		$helper->movePage(1203, 303);
	}

	
	
	// TODO
	public function testGetContent() {
		$this->markTestIncomplete();
		$helper = new tx_nwtmigration_migrationHelper;
		$return = $helper->getContent(80, 'field_content', 'uid', array('CType' => 'list', 'list_type' => 'opo_main_comments_pi1'));
	}
	
	
	/*
	public function __construct() {
	public static function getInstance() {
	public function execTCEcmd($cmd) {
	public function execTCEdata($data) {
	public function createPage($page) {
	public function updateData($table, $uid, $values) {
	public function copyPage($source, $destination) {
	public function movePage($source, $destination) {
	public function getContent($pageid, $column, $fields = '*', $search = array()) {
	function initflexForm($xml)	{
	function getFFvalue($T3FlexForm_array,$fieldName,$sheet='sDEF',$lang='lDEF',$value='vDEF')	{
	function setFFvalue($T3FlexForm_array,$fieldName,$newvalue ,$sheet='sDEF',$lang='lDEF',$value='vDEF')	{
	function getFFvalueFromSheetArray($sheetArray,$fieldNameArr,$value)
	*/
}
?>