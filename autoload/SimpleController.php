<?php
// Class that provides methods for working with the form data.
// There should be NOTHING in this file except this class definition.

class SimpleController {
	private $modulesMapper;
	private $questionsMapper;
	private $answersMapper;
	private $loginMapper;
	private $progressMapper;
	private $topicsMapper;

	
	public function __construct() {
		global $f3;						// needed for $f3->get() 

		$this->modulesMapper = new DB\SQL\Mapper($f3->get('DB'), 'quiz_modules');
		$this->questionsMapper = new DB\SQL\Mapper($f3->get('DB'), 'quiz_questions');
		$this->answersMapper = new DB\SQL\Mapper($f3->get('DB'), 'quiz_answers');
		$this->loginMapper = new DB\SQL\Mapper($f3->get('DB'), 'user_login');
		$this->progressMapper = new DB\SQL\Mapper($f3->get('DB'), 'user_progress');
		$this->topicsMapper = new DB\SQL\Mapper($f3->get('DB'), 'topics');
	}


	public function getModules() {
		return $this->modulesMapper->find();
	}

	public function getModule($moduleId) {
		return $this->modulesMapper->load(['module_id=?', $moduleId]);
	}

	public function isModuleAvailable($moduleId) {
		$this->modulesMapper->load(['module_id=?', $moduleId]);
		return $this->modulesMapper->available;

	}

	public function getQuestions($moduleId) {
		return $this->questionsMapper->find(['module_id=?', $moduleId]);
	}


	public function getCorrectOption($questionNumber, $module) {
		$this->answersMapper->load(array('module=? and question_number=?', $module, $questionNumber));
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

		$this->progressMapper->username = $user;
		$this->progressMapper->save();
	}

	public function getUserProgress($user, $mid) {
		$this->progressMapper->load(['username LIKE ?', $user]);
		return $this->progressMapper->$mid;
	}

	public function checkIfModuleExists($module) {
		$this->modulesMapper->load(['module_id = ?', $module]);
		$mod = $this->modulesMapper->module_id;
		if ($mod == $module) {
			if ($this->modulesMapper->available == 'true') {
				return true;
			}
		}
		return false;
	}

	public function getBackground($module) {
		$this->modulesMapper->load(['module_id = ?', $module]);
		return $this->modulesMapper->background;
	}

	public function savePoints($points, $user, $module) {
		// get user in table
		$this->progressMapper->load(['username LIKE ?', $user]);
		// get points for that module
		$pnts = $this->progressMapper->$module;
		// check if points for that module smaller than points scored
		if (intval($points) > intval($pnts)) {
			$this->progressMapper->$module = $points;
			$this->progressMapper->save();
			return 'larger';
		}
		return 'smaller';
	}

	public function getTopics() {
		return $this->topicsMapper->find();
	}
	
}

?>
