<?php
// Updated 11/5/2019
namespace Api;

use Model\Task;
use Email\TekBotsMailer;
use DataAccess\UserDao;

/**
 * Defines the logic for how to handle AJAX requests made to modify user information.
 */
class TaskActionHandler extends ActionHandler {

    /** @var \DataAccess\* */
    private $taskDao;
	private $userDao;
	private $messageDao;
	
	/** @var \Util\Logger */
 //   private $logger;
	
	/******
	$replacements is an array that contains items that should be accessable for emails/template replacement. General things are filled here with overwriting when needed in document
	***/
	private $replacements;

    /**
     * Constructs a new instance of the action handler for requests on user resources.
     *
     * @param \DataAccess\UsersDao $dao the data access object for users
     * @param \Util\Logger $logger the logger to use for logging information about actions
     */
    public function __construct($taskDao, $userDao, $messageDao, $logger)
    {
        parent::__construct($logger);
        $this->taskDao = $taskDao;
		$this->userDao = $userDao;
		$this->messageDao = $messageDao;
    }

	/**
     * Completes a task inserting current time and the user id for the person completing it.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleCompleteTask() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('task');
		$this->requireParam('user');
        $body = $this->requestBody;

		$task = $this->taskDao->getTaskById($body['task']);
		
		// Update the task
        $task->setCompleter($body['user']);
        $task->setCompleted(new \DateTime('now')); 
        $ok = $this->taskDao->updateTask($task);
		
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Task Failed to Update'));
        }
		$this->logger->info('Task Updated: '.$body['task']);
        $this->respond(new Response(Response::OK, 'Task Updated: '.$body['task']));
    }

	/**
     * Marks a task as urgent to prioritize it in the task listing.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleMarkUrgent() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('task');
        $body = $this->requestBody;

		$task = $this->taskDao->getTaskById($body['task']);
		
		// Update the task
        $task->setUrgent(true);
        $ok = $this->taskDao->updateTask($task);
		
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Task Failed to Update'));
        }
		$this->logger->info('Task Updated: '.$body['task']);
        $this->respond(new Response(Response::OK, 'Task Updated: '.$body['task']));
    }
	
	/**
     * Completes a task inserting current time and the user id for the person completing it.
     * 
     * This function, after invocation is finished, will exit the script via the `ActionHandler\respond()` function.
     *
     * @return void
     */
    public function handleAddTask() {
        // Ensure the user has permission to make the change
        $this->verifyAccessLevel('employee');
        
        // Ensure the required parameters exist
        $this->requireParam('desc');
		$this->requireParam('user');
        $body = $this->requestBody;

		$task = new Task();
        $task->setCreator($body['user']);
        $task->setDescription($body['desc']);
        $task->setCreated(new \DateTime('now')); 
        $ok = $this->taskDao->addNewTask($task);
		
        if(!$ok) {
            $this->respond(new Response(Response::INTERNAL_SERVER_ERROR, 'Task Failed to Update'));
        }
		$this->logger->info('Task Added');
        $this->respond(new Response(Response::OK, 'Task Added'));
    }

 
    /**
     * Handles the HTTP request on the API resource. 
     * 
     * This effectively will invoke the correct action based on the `action` parameter value in the request body. If
     * the `action` parameter is not in the body, the request will be rejected. The assumption is that the request
     * has already been authorized before this function is called.
     *
     * @return void
     */
    public function handleRequest() {
        // Make sure the action parameter exists
        $this->requireParam('action');

        // Call the correct handler based on the action
        switch($this->requestBody['action']) {

            case 'addTask':
                $this->handleAddTask();
				break;

			case 'completeTask':
                $this->handleCompleteTask();
				break;

            case 'markTaskUrgent':
                $this->handleMarkUrgent();
                break;
			
			case 'deleteTask':
                $this->handleDeleteTask();
				break;

            default:
                $this->respond(new Response(Response::BAD_REQUEST, 'Invalid action on Part resource'));
        }
    }

}