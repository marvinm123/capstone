<?php
require_once '../config.php';
class Login extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;

		parent::__construct();
		ini_set('display_error', 1);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function index(){
		echo "<h1>Access Denied</h1> <a href='".base_url."'>Go Back.</a>";
	}
	public function login(){
		extract($_POST);
		$password = md5($password);
		$stmt = $this->conn->prepare("SELECT * from users where username = ? and `password` = ? ");
		$stmt->bind_param("ss",$username,$password);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			foreach($result->fetch_array() as $k => $v){
				if(!is_numeric($k) && $k != 'password'){
					$this->settings->set_userdata($k,$v);
				}

			}
			$this->settings->set_userdata('login_type',1);
		return json_encode(array('status'=>'success'));
		}else{
		return json_encode(array('status'=>'incorrect','last_qry'=>"SELECT * from users where username = '$username' and `password` = md5('$password') "));
		}
	}
	public function logout(){
		if($this->settings->sess_des()){
			redirect('login.php');
		}
	}
	public function login_client(){
		extract($_POST);
		$password = md5($password);
		$stmt = $this->conn->prepare("SELECT * from client_list where email = ? and `password` =? and delete_flag = ?  ");
		$delete_flag = 0;
		$stmt->bind_param("ssi",$email,$password,$delete_flag);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			$data = $result->fetch_array();
			if($data['status'] == 1){
				foreach($data as $k => $v){
					if(!is_numeric($k) && $k != 'password'){
						$this->settings->set_userdata($k,$v);
					}

				}
				$this->settings->set_userdata('login_type',2);
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['msg'] = ' Your Account has been blocked by the management.';
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = ' Incorrect Email or Password.';
			$resp['error'] = $this->conn->error;
			$resp['res'] = $result;
		}
		return json_encode($resp);
	}
	public function logout_client(){
		if($this->settings->sess_des()){
			redirect('?');
		}
	}
	public function login_driver(){
		extract($_POST);
		$password = md5($password);
		$stmt = $this->conn->prepare("SELECT * from facility_list where reg_code = ? and `password` =? and delete_flag = ?  ");
		$delete_flag = 0;
		$stmt->bind_param("ssi",$reg_code,$password,$delete_flag);
		$stmt->execute();
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			$data = $result->fetch_array();
			if($data['status'] == 1){
				foreach($data as $k => $v){
					if(!is_numeric($k) && $k != 'password'){
						$this->settings->set_userdata($k,$v);
					}

				}
				$this->settings->set_userdata('login_type',3);
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['msg'] = ' Your Account has been blocked by the management.';
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = ' Incorrect Code or Password.';
			$resp['error'] = $this->conn->error;
			$resp['res'] = $result;
		}
		return json_encode($resp);
	}
	public function logout_driver(){
		if($this->settings->sess_des()){
			redirect('driver');
		}
	}

	// NEW UNIFIED LOGIN METHOD - FIXED VERSION
	public function unified_login() {
		// Check if the required POST data is set
		if (!isset($_POST['login_identifier']) || !isset($_POST['password'])) {
			return json_encode([
				'status' => 'error', 
				'msg' => 'Username/Email and password are required.'
			]);
		}
		
		extract($_POST);
		$identifier = trim($login_identifier);
		$password = trim($password);
		$password_md5 = md5($password);
		
		// Normalize identifier for flexible matching
		$normalized_identifier = strtolower($identifier);
		
		// 1. Try Admin/Staff Authentication (users table)
		$stmt = $this->conn->prepare("SELECT * FROM users WHERE (
			LOWER(username) = ? OR 
			username = ?
		) LIMIT 1");
		$stmt->bind_param('ss', $normalized_identifier, $identifier);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();
			// Check password using MD5
			if ($password_md5 === $user['password']) {
				// Set session data
				foreach($user as $k => $v){
					if(!is_numeric($k) && $k != 'password'){
						$this->settings->set_userdata($k,$v);
					}
				}
				$this->settings->set_userdata('login_type', 1);
				
				return json_encode([
					'status' => 'success', 
					'user_type' => 'admin',
					'msg' => 'Login successful!'
				]);
			} else {
				// Password doesn't match for admin/staff
				return json_encode([
					'status' => 'error', 
					'msg' => 'Invalid password. Please try again.'
				]);
			}
		}
		
		// 2. Try Client Authentication (client_list table) - FIXED
		// First check if username field exists in client_list table
		$check_username = $this->conn->query("SHOW COLUMNS FROM client_list LIKE 'username'");
		$has_username = ($check_username->num_rows > 0);
		
		if ($has_username) {
			// If username field exists, check both email and username
			$stmt = $this->conn->prepare("SELECT * FROM client_list WHERE delete_flag = 0 AND (
				email = ? OR email = ? OR username = ? OR username = ?
			) LIMIT 1");
			$stmt->bind_param('ssss', $normalized_identifier, $identifier, $normalized_identifier, $identifier);
		} else {
			// If username field doesn't exist, check only email
			$stmt = $this->conn->prepare("SELECT * FROM client_list WHERE delete_flag = 0 AND (
				email = ? OR email = ?
			) LIMIT 1");
			$stmt->bind_param('ss', $normalized_identifier, $identifier);
		}
		
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();
			// Check if account is active
			if ($user['status'] == 1) {
				// Check password using MD5
				if ($password_md5 === $user['password']) {
					// Set session data
					foreach($user as $k => $v){
						if(!is_numeric($k) && $k != 'password'){
							$this->settings->set_userdata($k,$v);
						}
					}
					$this->settings->set_userdata('login_type', 2);
					
					return json_encode([
						'status' => 'success', 
						'user_type' => 'client',
						'msg' => 'Login successful!'
					]);
				} else {
					// Password doesn't match for client
					return json_encode([
						'status' => 'error', 
						'msg' => 'Invalid password. Please try again.'
					]);
				}
			} else {
				return json_encode([
					'status' => 'error', 
					'msg' => 'Your account has been blocked by the management.'
				]);
			}
		}
		
		// 3. Try Driver Authentication (facility_list table)
		$stmt = $this->conn->prepare("SELECT * FROM facility_list WHERE delete_flag = 0 AND (
			reg_code = ? OR reg_code = ?
		) LIMIT 1");
		$stmt->bind_param('ss', $normalized_identifier, $identifier);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			$user = $result->fetch_assoc();
			if ($user['status'] == 1) {
				if ($password_md5 === $user['password']) {
					foreach($user as $k => $v){
						if(!is_numeric($k) && $k != 'password'){
							$this->settings->set_userdata($k,$v);
						}
					}
					$this->settings->set_userdata('login_type', 3);
					
					return json_encode([
						'status' => 'success', 
						'user_type' => 'driver',
						'msg' => 'Login successful!'
					]);
				} else {
					// Password doesn't match for driver
					return json_encode([
						'status' => 'error', 
						'msg' => 'Invalid password. Please try again.'
					]);
				}
			} else {
				return json_encode([
					'status' => 'error', 
					'msg' => 'Your account has been blocked by the management.'
				]);
			}
		}
		
		// No user found with that identifier
		return json_encode([
			'status' => 'error', 
			'msg' => 'Invalid username or email. Please check your credentials.'
		]);
	}
}

$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$auth = new Login();
switch ($action) {
	case 'login':
		echo $auth->login();
		break;
	case 'logout':
		echo $auth->logout();
		break;
	case 'login_client':
		echo $auth->login_client();
		break;
	case 'logout_client':
		echo $auth->logout_client();
		break;
	case 'login_driver':
		echo $auth->login_driver();
		break;
	case 'logout_driver':
		echo $auth->logout_driver();
		break;
	case 'unified_login':
		echo $auth->unified_login();
		break;
	default:
		echo $auth->index();
		break;
}