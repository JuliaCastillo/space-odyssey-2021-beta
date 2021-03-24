<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $mapper;
	private $modulesMapper;
	private $questionsMapper;
	private $optionsMapper;
	private $answersMapper;
	private $loginMapper;

	
	public function __construct() {
		global $f3;						// needed for $f3->get() 
		$this->mapper = new DB\SQL\Mapper($f3->get('DB'),"simpleModel");	// create DB query mapper object
																			// for the "simpleModel" table
		$this->modulesMapper = new DB\SQL\Mapper($f3->get('DB'), 'modules');
		$this->questionsMapper = new DB\SQL\Mapper($f3->get('DB'), 'quiz_questions');
		$this->optionsMapper = new DB\SQL\Mapper($f3->get('DB'), 'quiz_options');
		$this->answersMapper = new DB\SQL\Mapper($f3->get('DB'), 'quiz_answers');
		$this->loginMapper = new DB\SQL\Mapper($f3->get('DB'), 'user_login');
	}
	
	public function putIntoDatabase($data) {	
		$this->mapper->name = $data["name"];					// set value for "name" field
		$this->mapper->colour = $data["colour"];				// set value for "colour" field
		$this->mapper->save();									// save new record with these fields
	}
	
	public function getData() {
		$list = $this->mapper->find();
		return $list;
	}
	
	public function deleteFromDatabase($idToDelete) {
		$this->mapper->load(['id=?', $idToDelete]);				// load DB record matching the given ID
		$this->mapper->erase();									// delete the DB record
	}

	public function getModules() {
		return $this->modulesMapper->find();
	}

	public function getModule($moduleId) {
		return $this->modulesMapper->load(['module_id=?', $moduleId]);
	}

	public function getQuestions($moduleId) {
		return $this->questionsMapper->find(['module_id=?', $moduleId]);
	}

	public function getQuestionOptions($questionId) {
		return $this->optionsMapper->find(['question_id=?', $questionId]);
	}

	public function getCorrectOption($questionNumber, $module) {
		//$list =  $this->answersMapper->find(['question_id=?', $questionId]);
		$this->answersMapper->load(array('module=? and question_number=?', $module, $questionNumber));
		//$record = $this->answersMapper->select($record,'option_number');
//		foreach ($list as $record) {
//			$array = $this->answersMapper->cast($record);
//			$opt = $array["option_number"];
//		}
//		return $opt;
		return $this->answersMapper->option_number;
	}

	public function loginUser($user, $pwd) {		// very simple login -- no use of encryption, hashing etc.
		$auth = new \Auth($this->loginMapper, array('id'=>'username', 'pw'=>'password'));	// fields in table
		return $auth->login($user, $pwd); 			// returns true on successful login
	}

	public function checkIfUserExists($user) {
		$this->loginMapper->load(['username LIKE ?', $user]);
		$username = $this->loginMapper->username;
		if ($user == $username) {
			return true;
		}
		return false;
	}

	public function addNewUser($user, $pwd) {
		$this->loginMapper->username = $user;
		$this->loginMapper->password = $pwd;
		$this->loginMapper->save();
	}
	
}

?>
