<?php

  /////////////////////////////////////
 // index.php for SimpleExample app //
/////////////////////////////////////

// Create f3 object then set various global properties of it
// These are available to the routing code below, but also to any 
// classes defined in autoloaded definitions

$f3 = require('../../../AboveWebRoot/fatfree-master-3.7/lib/base.php');

// autoload Controller class(es) and anything hidden above web root, e.g. DB stuff
$f3->set('AUTOLOAD','autoload/;../../../AboveWebRoot/autoload/');		

$db = DatabaseConnection::connect();		// defined as autoloaded class in AboveWebRoot/autoload/
$f3->set('DB', $db);

$f3->set('DEBUG',3);		// set maximum debug level
$f3->set('UI','ui/');		// folder for View templates

new \DB\SQL\Session($f3->get('DB'));
if (!$f3->exists('SESSION.userName')) $f3->set('SESSION.userName', 'UNSET');

  /////////////////////////////////////////////
 // Simple Example URL application routings //
/////////////////////////////////////////////

//home page (index.html)

$f3->route('GET /',
    function ($f3) {
        $controller = new SimpleController;
        $modules = $controller->getModules();

        $f3->set("modules", $modules);
        $f3->set('html_title','2021 Space Odyssey');
        $f3->set('content','index.html');
        echo Template::instance()->render('layout.html');
    }
);

$f3->route('POST /',
    function ($f3) {
        $f3->set('SESSION.currentModule', $f3->get('POST.module'));
        //$f3->reroute('/quiz');
        echo('/quiz');
    }
);

$f3->route('GET /progress/@mid',
    function ($f3) {
        $mid = $f3->get('PARAMS.mid');
        $controller = new SimpleController;
        $progress = $controller->getUserProgress($f3->get('SESSION.userName'), $mid);
        echo $progress;
    }
);


// --------------  LOGIN  ---------------- //



$f3->route('GET /login@msg',				// @msg is a parameter that tells us which message to give the user
    function($f3) {
        switch ($f3->get('PARAMS.msg')) {		// PARAMS.msg is whatever was the last element of the URL
            case "err":
                $msg = "Wrong user name and/or password; please try again.";
                break;
            case "lo":
                $msg = "You have been logged out.";
                break;
            default:						// this is the case if neither of the above cases is matched
                $msg = "Log in here";
        }
        $f3->set('html_title', 'Simple Login Form');
        $f3->set('message', $msg);				// set message that will be shown to user in the login.html template
        //$f3->set('thisIsLoginPage', 'true');	// set flag that will be tested in layout.html, to say this is login page
        $f3->set('content', 'login.html');		// the login form that will be shown to the user
        echo template::instance()->render('layout.html');
    }
);


$f3->route('POST /login',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->loginUser($f3->get('POST.uname'), $f3->get('POST.password'))) {		// user is recognised
            $f3->set('SESSION.userName', $f3->get('POST.uname'));			// note that this is a global that will be available elsewhere
            $f3->reroute('/');							// will always go to simpleform after successful login
            //echo template::instance()->render('layout.html');
        }
        else
            $f3->reroute('/loginerr');		// return to login page with the message that there was an error in the credentials
    }
);


// --------------  REGISTER  ---------------- //

$f3->route('GET /register@msg',				// @msg is a parameter that tells us which message to give the user
    function($f3) {
        switch ($f3->get('PARAMS.msg')) {		// PARAMS.msg is whatever was the last element of the URL
            case "exists":
                $msg = "This user name already exists, please choose a different one.";
                break;
            default:						// this is the case if neither of the above cases is matched
                $msg = "Register here";
        }
        $f3->set('html_title', 'Register');
        $f3->set('message', $msg);				// set message that will be shown to user in the login.html template
        //$f3->set('thisIsLPage', 'true');	// set flag that will be tested in layout.html, to say this is login page
        $f3->set('content', 'register.html');		// the login form that will be shown to the user
        echo template::instance()->render('layout.html');
    }
);

$f3->route('POST /register',
    function($f3) {
        $controller = new SimpleController;
        if ($controller->checkIfUserExists($f3->get('POST.uname'))) {
            $f3->reroute('/registerexists');
        } else {
            $controller->addNewUser($f3->get('POST.uname'), $f3->get('POST.password'));
            $f3->set('SESSION.userName', $f3->get('POST.uname'));			// note that this is a global that will be available elsewhere
            $f3->reroute('/');
        }
    }
);

$f3->route('GET /logout',
    function($f3) {
        $f3->set('SESSION.userName', 'UNSET');
        $f3->reroute('/');
    }
);

// --------------  EXPLORE  ---------------- //

$f3->route('GET /explore',
    function ($f3) {
        $controller = new SimpleController;
        $topics = $controller->getTopics();

        $f3->set('topics', $topics);
        $f3->set('html_title','Explore!');
        $f3->set('content','explore.html');
        echo Template::instance()->render('layout.html');
    }
);


// --------------  QUIZ  ---------------- //

$f3->route('GET /quiz',
    function ($f3) {
        $controller = new SimpleController;
        $questions = $controller->getQuestions($f3->get('SESSION.currentModule'));
        $module = $controller->getModule($f3->get('SESSION.currentModule'));

        $f3->set("moduleData", $module);
        $f3->set("questions", $questions);
        $f3->set('html_title','Learn something new!');
        $f3->set('content','quiz.html');
        echo template::instance()->render('layout.html');
    }
);

$f3->route('GET /quiz/@query',
    function ($f3) {
        $q = $f3->get('PARAMS.query');
        $controller = new SimpleController;
        $correctAnswer = $controller->getCorrectOption($q, $f3->get('SESSION.currentModule'));
        echo $correctAnswer;
    }
);

$f3->route('GET /quiz/next',
    function ($f3) {
        $controller = new SimpleController;
        $moduleExists = $controller->checkIfModuleExists($f3->get('SESSION.currentModule')+1);
        if ($moduleExists) {
            $f3->set('SESSION.currentModule', $f3->get('SESSION.currentModule')+1);
            echo true;
        } else {
            echo false;
        }
    }
);

$f3->route('GET /quiz/backg',
    function ($f3) {
        $controller = new SimpleController;
        $background = $controller->getBackground($f3->get('SESSION.currentModule'));
        echo $background;
    }
);

$f3->route('POST /savepoints',
    function ($f3) {
        $points = $f3->get('POST.points');
        $controller = new SimpleController;
        $pnts = $controller->savePoints($points, $f3->get('SESSION.userName'), $f3->get('SESSION.currentModule'));
        echo $pnts;
    }
);



  ////////////////////////
 // Run the F3 engine //
////////////////////////

$f3->run();

?>

