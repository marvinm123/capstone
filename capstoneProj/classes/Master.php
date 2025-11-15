<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function base_url($path = '') {
		return 'http://' . $_SERVER['HTTP_HOST'] . '/uploads/facility/' . $path;
	}
	function capture_err(){
		if(!$this->conn->error) return false;
		else {
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}

	// ================================
	// DATE EVENTS FUNCTIONS
	// ================================
	function save_date_event(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, ['id'])){
				if($k == 'description'){
					$v = addslashes(htmlentities($v));
				}
				$data .= (!empty($data) ? "," : "") . " `{$k}`='{$v}' ";
			}
		}

		// Validate event date
		$event_date = $_POST['event_date'];
		$today = date('Y-m-d');
		if($event_date < $today){
			return json_encode(['status' => 'failed', 'msg' => 'Cannot create events for past dates.']);
		}

		if(empty($id)){
			$check = $this->conn->query("SELECT * FROM `date_events` WHERE `event_date` = '{$event_date}' AND `title` = '{$title}'")->num_rows;
			if($check > 0){
				return json_encode(['status' => 'failed', 'msg' => 'An event with this title already exists on the selected date.']);
			}

			$sql = "INSERT INTO `date_events` SET {$data}";
		}else{
			$check = $this->conn->query("SELECT * FROM `date_events` WHERE `event_date` = '{$event_date}' AND `title` = '{$title}' AND id != {$id}")->num_rows;
			if($check > 0){
				return json_encode(['status' => 'failed', 'msg' => 'An event with this title already exists on the selected date.']);
			}

			$sql = "UPDATE `date_events` SET {$data} WHERE id = '{$id}'";
		}

		$save = $this->conn->query($sql);
		if($save){
			$this->settings->set_flashdata('success', empty($id) ? 'Date event successfully created.' : 'Date event successfully updated.');
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'err' => $this->conn->error . "[{$sql}]"]);
		}
	}

	function delete_date_event(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `date_events` WHERE id = '{$id}'");
		if($del){
			$this->settings->set_flashdata('success',"Date event successfully deleted.");
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
		}
	}

	function load_date_events(){
		$start = $_GET['start'] ?? date('Y-m-01');
		$end = $_GET['end'] ?? date('Y-m-t');
		
		$events_qry = $this->conn->query("SELECT * FROM date_events WHERE event_date BETWEEN '{$start}' AND '{$end}' ORDER BY event_date");
		$events = [];
		
		while($row = $events_qry->fetch_assoc()){
			$events[] = [
				'id' => $row['id'],
				'title' => $row['title'],
				'start' => $row['event_date'],
				'color' => $row['color'],
				'extendedProps' => [
					'description' => $row['description'],
					'event_type' => $row['event_type']
				]
			];
		}
		
		return json_encode($events);
	}

	// ================================
	// CATEGORY FUNCTIONS
	// ================================
	function save_category(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, ['id','description'])){
				$data .= (!empty($data) ? "," : "") . " `{$k}`='{$v}' ";
			}
		}
		if(isset($_POST['description'])){
			$data .= (!empty($data) ? "," : "") . " `description`='".addslashes(htmlentities($description))."' ";
		}
		$check = $this->conn->query("SELECT * FROM `category_list` WHERE `name` = '{$name}' AND delete_flag = 0 " . (!empty($id) ? "AND id != {$id}" : ""))->num_rows;
		if($this->capture_err()) return $this->capture_err();
		if($check > 0){
			return json_encode(['status' => 'failed', 'msg' => 'Category already exists.']);
		}

		if(empty($id)){
			$sql = "INSERT INTO `category_list` SET {$data}";
		}else{
			$sql = "UPDATE `category_list` SET {$data} WHERE id = '{$id}'";
		}
		$save = $this->conn->query($sql);
		if($save){
			$this->settings->set_flashdata('success', empty($id) ? 'New Category successfully saved.' : 'Category successfully updated.');
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'err' => $this->conn->error . "[{$sql}]"]);
		}
	}
	function delete_category(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `category_list` SET delete_flag = 1 WHERE id = '{$id}'");
		if($del){
			$this->settings->set_flashdata('success',"Category successfully deleted.");
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
		}
	}

	// ================================
	// FACILITY FUNCTIONS
	// ================================
	function save_facility(){
		$_POST['description'] = html_entity_decode($_POST['description']);
		extract($_POST);

		// Auto-generate code if new
		if(empty($id)){
			$prefix = date('Ym-');
			$code = sprintf("%'.05d",1);
			while(true){
				$check = $this->conn->query("SELECT * FROM `facility_list` WHERE facility_code = '{$prefix}{$code}'")->num_rows;
				if($check > 0){
					$code = sprintf("%'.05d", (int)$code + 1);
				}else break;
			}
			$_POST['facility_code'] = $prefix.$code;
		}

		$data = "";
		foreach($_POST as $k => $v){
			if($k != 'id'){
				$v = $this->conn->real_escape_string($v);
				$data .= (!empty($data) ? "," : "") . " `{$k}`='{$v}' ";
			}
		}

		if(empty($id)){
			$check = $this->conn->query("SELECT * FROM `facility_list` WHERE `name` = '{$name}'")->num_rows;
			if($check > 0){
				return json_encode(['status' => 'failed', 'msg' => 'Facility already exists.']);
			}
			$save = $this->conn->query("INSERT INTO `facility_list` SET {$data}");
			$cid = $this->conn->insert_id;
		}else{
			$save = $this->conn->query("UPDATE `facility_list` SET {$data} WHERE id = '{$id}'");
			$cid = $id;
		}

		if($save){
			$resp = ['status' => 'success', 'id' => $cid, 'msg' => (empty($id) ? "New facility successfully saved." : "Facility successfully updated.")];

			// Handle image upload here
			if(isset($_FILES['images']) && count($_FILES['images']['tmp_name']) > 0){
				$upload_dir = base_app . "uploads/facility/";
				if(!is_dir($upload_dir)){
					mkdir($upload_dir, 0755, true);
				}

				// For now, handle only the first image uploaded
				$tmp_name = $_FILES['images']['tmp_name'][0];
				$original_name = $_FILES['images']['name'][0];
				$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
				$allowed = ['png','jpg','jpeg','gif'];

				if(in_array($ext, $allowed)){
					$new_filename = $cid . '.' . $ext;
					$target_path = $upload_dir . $new_filename;
					
					if(move_uploaded_file($tmp_name, $target_path)){
						$image_path = "uploads/facility/" . $new_filename . "?v=" . time();
						// Update image path in DB
						$this->conn->query("UPDATE facility_list SET image_path = '{$image_path}' WHERE id = '{$cid}'");
					} else {
						$resp['msg'] .= " But image upload failed.";
					}
				} else {
					$resp['msg'] .= " Invalid image file type.";
				}
			}

			$this->settings->set_flashdata('success', $resp['msg']);
			return json_encode($resp);
		} else {
			return json_encode(['status' => 'failed', 'err' => $this->conn->error]);
		}
	}

	function delete_facility(){
		extract($_POST);
		$del = $this->conn->query("UPDATE `facility_list` SET `delete_flag` = 1 WHERE id = '{$id}'");
		if($del){
			$this->settings->set_flashdata('success',"Facility successfully deleted.");
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
		}
	}

	// ================================
	// BOOKING FUNCTIONS
	// ================================
	function save_booking(){
		if(empty($_POST['id'])){
			$prefix = date('Ym-');
			$code = sprintf("%'.05d",1);
			while(true){
				$check = $this->conn->query("SELECT * FROM `booking_list` WHERE ref_code = '{$prefix}{$code}'")->num_rows;
				if($check > 0){
					$code = sprintf("%'.05d", (int)$code + 1);
				}else break;
			}
			$_POST['ref_code'] = $prefix . $code;
			$_POST['client_id'] = $this->settings->userdata('id');
		}

		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if($k != 'id') $data .= (!empty($data) ? "," : "") . " `{$k}`='{$v}' ";
		}

		$check = $this->conn->query("SELECT * FROM `booking_list` WHERE facility_id = '{$facility_id}' AND ('{$date_from}' BETWEEN date(date_from) AND date(date_to) OR '{$date_to}' BETWEEN date(date_from) AND date(date_to)) AND status = 1")->num_rows;
		if($check > 0){
			return json_encode(['status' => 'failed', 'msg' => 'Facility is not available on the selected dates.']);
		}

		if(empty($id)){
			$sql = "INSERT INTO `booking_list` SET {$data}";
		}else{
			$sql = "UPDATE `booking_list` SET {$data} WHERE id = '{$id}'";
		}

		$save = $this->conn->query($sql);
		if($save){
			$this->settings->set_flashdata('success', empty($id) ? 'Facility has been booked successfully.' : 'Booking successfully updated.');
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'err' => $this->conn->error . "[{$sql}]"]);
		}
	}

	function delete_booking(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `booking_list` WHERE id = '{$id}'");
		if($del){
			$this->settings->set_flashdata('success',"Booking successfully deleted.");
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
		}
	}

	function update_booking_status(){
		extract($_POST);
		$update = $this->conn->query("UPDATE `booking_list` SET `status` = '{$status}' WHERE id = '{$id}'");
		if($update){
			$this->settings->set_flashdata('success',"Booking status successfully updated.");
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'error' => $this->conn->error]);
		}
	}

	// ================================
	// RECEIPT AMOUNT FUNCTION
	// ================================
	function save_receipt_amount(){
		if(!isset($_POST['id']) || !isset($_POST['amount'])){
			return json_encode(['status' => 'failed', 'message' => 'Missing required data.']);
		}

		$id = intval($_POST['id']);
		$amount = floatval($_POST['amount']);

		// Validate amount
		if($amount <= 0){
			return json_encode(['status' => 'failed', 'message' => 'Amount must be greater than 0.']);
		}

		// Verify booking exists
		$booking_check = $this->conn->query("SELECT id FROM `booking_list` WHERE id = '{$id}'");
		if($booking_check->num_rows == 0){
			return json_encode(['status' => 'failed', 'message' => 'Booking not found.']);
		}

		// Update booking record with paid amount
		$update = $this->conn->query("UPDATE `booking_list` SET `paid_amount` = '{$amount}' WHERE id = '{$id}'");
		
		if($update){
			$this->settings->set_flashdata('success', 'Receipt amount saved successfully.');
			return json_encode(['status' => 'success']);
		} else {
			return json_encode(['status' => 'failed', 'message' => 'Failed to save receipt amount: ' . $this->conn->error]);
		}
	}

	// ================================
	// RECEIPT GENERATION FUNCTION
	// ================================
	function generate_receipt(){
		if(!isset($_POST['booking_id'])){
			return json_encode(['status' => 'failed', 'message' => 'Booking ID required.']);
		}

		$booking_id = intval($_POST['booking_id']);
		
		// Get complete booking data for receipt
		$booking_qry = $this->conn->query("SELECT 
			b.*, 
			CONCAT(c.lastname, ', ', c.firstname, ' ', COALESCE(c.middlename, '')) as client_name,
			c.email as client_email,
			c.contact as client_contact,
			c.address as client_address,
			f.name as facility_name,
			f.facility_code,
			f.price as facility_price,
			cat.name as category_name,
			COALESCE(b.paid_amount, 0) as paid_amount
			FROM `booking_list` b 
			INNER JOIN client_list c ON b.client_id = c.id 
			INNER JOIN facility_list f ON b.facility_id = f.id
			INNER JOIN category_list cat ON f.category_id = cat.id
			WHERE b.id = '{$booking_id}'");
		
		if($booking_qry->num_rows == 0){
			return json_encode(['status' => 'failed', 'message' => 'Booking not found.']);
		}

		$booking_data = $booking_qry->fetch_assoc();
		
		// Calculate amounts
		$facility_price = floatval($booking_data['facility_price']);
		$paid_amount = floatval($booking_data['paid_amount']);
		$date_from = $booking_data['date_from'];
		$date_to = $booking_data['date_to'];
		$time_from = $booking_data['time_from'];
		$time_to = $booking_data['time_to'];
		
		// Calculate duration and total amount
		if(empty($time_from) || empty($time_to)) {
			// All-day booking
			$start_date = new DateTime($date_from);
			$end_date = new DateTime($date_to);
			$end_date->modify('+1 day');
			$interval_days = $start_date->diff($end_date);
			$total_days = $interval_days->days;
			$total_amount = $facility_price * $total_days;
			$rate_type = "Daily Rate";
		} else {
			// Hourly booking
			$datetime_from = new DateTime($date_from . ' ' . $time_from);
			$datetime_to = new DateTime($date_to . ' ' . $time_to);
			$interval = $datetime_from->diff($datetime_to);
			$total_hours = ($interval->days * 24) + $interval->h + ($interval->i / 60);
			$total_hours = round($total_hours, 2);
			$total_amount = $facility_price * $total_hours;
			$rate_type = "Hourly Rate";
		}
		
		$balance = $total_amount - $paid_amount;
		
		// Prepare receipt data
		$receipt_data = [
			'status' => 'success',
			'receipt' => [
				'ref_code' => $booking_data['ref_code'],
				'client_name' => $booking_data['client_name'],
				'client_email' => $booking_data['client_email'],
				'client_contact' => $booking_data['client_contact'],
				'client_address' => $booking_data['client_address'],
				'facility_name' => $booking_data['facility_name'],
				'facility_code' => $booking_data['facility_code'],
				'category_name' => $booking_data['category_name'],
				'date_from' => $date_from,
				'date_to' => $date_to,
				'time_from' => $time_from,
				'time_to' => $time_to,
				'facility_price' => $facility_price,
				'total_amount' => $total_amount,
				'paid_amount' => $paid_amount,
				'balance' => $balance,
				'rate_type' => $rate_type,
				'issued_date' => date('Y-m-d H:i:s'),
				'status' => $booking_data['status']
			]
		];
		
		return json_encode($receipt_data);
	}

	// ================================
	// PAYMENT PROOF FUNCTIONS
	// ================================
	function upload_payment_proof(){
		if(!isset($_FILES['payment_proof']) || !isset($_POST['booking_id'])){
			return json_encode(['status' => 'failed', 'message' => 'Missing required data.']);
		}

		$booking_id = intval($_POST['booking_id']);
		
		// Verify booking exists
		$booking_check = $this->conn->query("SELECT id FROM `booking_list` WHERE id = '{$booking_id}'");
		if($booking_check->num_rows == 0){
			return json_encode(['status' => 'failed', 'message' => 'Booking not found.']);
		}

		// Create upload directory if it doesn't exist - Use absolute path from document root
		$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/payment_proofs/";
		if(!is_dir($upload_dir)){
			mkdir($upload_dir, 0755, true);
		}

		$file = $_FILES['payment_proof'];
		$original_name = $file['name'];
		$tmp_name = $file['tmp_name'];
		$file_size = $file['size'];
		$file_error = $file['error'];

		// Validate file upload
		if($file_error !== UPLOAD_ERR_OK){
			return json_encode(['status' => 'failed', 'message' => 'File upload error: ' . $file_error]);
		}

		// Validate file size (5MB limit)
		if($file_size > 5 * 1024 * 1024){
			return json_encode(['status' => 'failed', 'message' => 'File size must be less than 5MB.']);
		}

		// Validate file type
		$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
		$file_type = mime_content_type($tmp_name);
		if(!in_array($file_type, $allowed_types)){
			return json_encode(['status' => 'failed', 'message' => 'Only JPG, PNG, and GIF files are allowed.']);
		}

		// Generate unique filename
		$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
		$new_filename = 'payment_' . $booking_id . '_' . time() . '.' . $ext;
		$target_path = $upload_dir . $new_filename;

		// Move uploaded file
		if(move_uploaded_file($tmp_name, $target_path)){
			// Store relative path from web root (this is what gets saved to database)
			$file_path = "uploads/payment_proofs/" . $new_filename;
			
			// Update booking record with payment proof path
			$update = $this->conn->query("UPDATE `booking_list` SET `payment_proof` = '{$file_path}' WHERE id = '{$booking_id}'");
			
			if($update){
				return json_encode([
					'status' => 'success', 
					'message' => 'Payment proof uploaded successfully.',
					'file_path' => $file_path,
					'debug_info' => [
						'upload_dir' => $upload_dir,
						'target_path' => $target_path,
						'file_path' => $file_path,
						'file_exists' => file_exists($target_path)
					]
				]);
			} else {
				// Delete uploaded file if database update fails
				unlink($target_path);
				return json_encode(['status' => 'failed', 'message' => 'Failed to update booking record: ' . $this->conn->error]);
			}
		} else {
			return json_encode(['status' => 'failed', 'message' => 'Failed to upload file. Check directory permissions.']);
		}
	}

	function get_payment_proofs(){
		if(!isset($_POST['booking_id'])){
			return json_encode(['status' => 'failed', 'message' => 'Booking ID required.']);
		}

		$booking_id = intval($_POST['booking_id']);
		
		$result = $this->conn->query("SELECT payment_proof FROM `booking_list` WHERE id = '{$booking_id}'");
		
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();
			$proofs = [];
			
			if(!empty($row['payment_proof'])){
				$proofs[] = [
					'id' => 'existing',
					'path' => $row['payment_proof'],
					'name' => 'Payment Proof',
					'uploaded_at' => 'Previously uploaded'
				];
			}
			
			return json_encode([
				'status' => 'success',
				'proofs' => $proofs
			]);
		} else {
			return json_encode(['status' => 'failed', 'message' => 'Booking not found.']);
		}
	}

	function delete_payment_proof(){
		if(!isset($_POST['booking_id'])){
			return json_encode(['status' => 'failed', 'message' => 'Booking ID required.']);
		}

		$booking_id = intval($_POST['booking_id']);
		
		// Get current payment proof path
		$result = $this->conn->query("SELECT payment_proof FROM `booking_list` WHERE id = '{$booking_id}'");
		
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();
			$file_path = $row['payment_proof'];
			
			// Update database to remove payment proof
			$update = $this->conn->query("UPDATE `booking_list` SET `payment_proof` = NULL WHERE id = '{$booking_id}'");
			
			if($update){
				// Delete physical file if it exists
				if(!empty($file_path) && file_exists(base_app . $file_path)){
					unlink(base_app . $file_path);
				}
				
				return json_encode([
					'status' => 'success',
					'message' => 'Payment proof deleted successfully.'
				]);
			} else {
				return json_encode(['status' => 'failed', 'message' => 'Failed to delete payment proof.']);
			}
		} else {
			return json_encode(['status' => 'failed', 'message' => 'Booking not found.']);
		}
	}
}

// Action router
$Master = new Master();
$action = isset($_GET['f']) ? strtolower($_GET['f']) : 'none';
$sysset = new SystemSettings();

switch ($action) {
	case 'save_category': echo $Master->save_category(); break;
	case 'delete_category': echo $Master->delete_category(); break;
	case 'save_facility': echo $Master->save_facility(); break;
	case 'delete_facility': echo $Master->delete_facility(); break;
	case 'save_booking': echo $Master->save_booking(); break;
	case 'delete_booking': echo $Master->delete_booking(); break;
	case 'update_booking_status': echo $Master->update_booking_status(); break;
	case 'save_receipt_amount': echo $Master->save_receipt_amount(); break;
	case 'generate_receipt': echo $Master->generate_receipt(); break;
	case 'upload_payment_proof': echo $Master->upload_payment_proof(); break;
	case 'get_payment_proofs': echo $Master->get_payment_proofs(); break;
	case 'delete_payment_proof': echo $Master->delete_payment_proof(); break;
	// Date Events Functions
	case 'save_date_event': echo $Master->save_date_event(); break;
	case 'delete_date_event': echo $Master->delete_date_event(); break;
	case 'load_date_events': echo $Master->load_date_events(); break;
	default: break;
}