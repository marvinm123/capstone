<?php
// Start session as early as possible
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!class_exists('DBConnection')) {
    require_once('../config.php');
    require_once('DBConnection.php');
}

class SystemSettings extends DBConnection {
    function set_userdata($field = '', $value = '') {
        if (is_array($field) && !empty($field)) {
            foreach ($field as $key => $val) {
                $_SESSION['userdata'][$key] = $val;
            }
            return true;
        } elseif (!empty($field)) {
            $_SESSION['userdata'][$field] = $value;
            return true;
        }
        return false;
    }

    function unset_userdata($field = '') {
        if (empty($field)) {
            unset($_SESSION['userdata']);
        } elseif (is_array($field)) {
            foreach ($field as $key) {
                unset($_SESSION['userdata'][$key]);
            }
        } else {
            unset($_SESSION['userdata'][$field]);
        }
        return true;
    }

    public function check_connection(){
        return $this->conn;
    }

    public function load_system_info(){
        $sql = "SELECT * FROM system_info";
        $qry = $this->conn->query($sql);
        if(!$qry){
            error_log("Failed to load system_info: " . $this->conn->error);
            return false;
        }
        while($row = $qry->fetch_assoc()){
            $_SESSION['system_info'][$row['meta_field']] = $row['meta_value'];
        }
        return true;
    }

    public function update_system_info(){
        $sql = "SELECT * FROM system_info";
        $qry = $this->conn->query($sql);
        if(!$qry){
            error_log("Failed to update system_info session cache: " . $this->conn->error);
            return false;
        }
        while($row = $qry->fetch_assoc()){
            $_SESSION['system_info'][$row['meta_field']] = $row['meta_value'];
        }
        return true;
    }

    public function update_settings_info(){
        $resp = [];

        foreach ($_POST as $key => $value) {
            if ($key == 'content') continue;
            $value = $this->conn->real_escape_string($value);
            $exists = $this->conn->query("SELECT 1 FROM system_info WHERE meta_field='{$key}'")->num_rows > 0;
            if ($exists) {
                $this->conn->query("UPDATE system_info SET meta_value='{$value}' WHERE meta_field='{$key}'");
            } else {
                $this->conn->query("INSERT INTO system_info (meta_field, meta_value) VALUES('{$key}', '{$value}')");
            }
            $_SESSION['system_info'][$key] = $value;
        }

        if(isset($_POST['content']) && is_array($_POST['content'])){
            foreach($_POST['content'] as $k => $v){
                file_put_contents(base_app."{$k}.html", $v);
            }
        }

        if(isset($_FILES['img']) && $_FILES['img']['tmp_name'] != ''){
            $this->handle_image_upload('img','system-logo.png','logo');
        }

        if(isset($_FILES['cover']) && $_FILES['cover']['tmp_name'] != ''){
            $this->handle_image_upload('cover','system-cover.png','cover');
        }

        // Handle multiple gallery uploads
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $galleryDir = base_app . 'uploads/gallery/';
            if (!is_dir($galleryDir)) mkdir($galleryDir, 0755, true);

            $galleryList = [];

            foreach ($_FILES['gallery_images']['tmp_name'] as $index => $tmpName) {
                $originalName = $_FILES['gallery_images']['name'][$index];
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($ext, $allowed)) continue;

                $newName = uniqid("img_") . '.' . $ext;
                $dest = $galleryDir . $newName;

                if (move_uploaded_file($tmpName, $dest)) {
                    $galleryList[] = 'uploads/gallery/' . $newName;
                }
            }

            if (!empty($galleryList)) {
                $galleryJsonPath = $galleryDir . 'gallery.json';
                $existing = [];

                if (file_exists($galleryJsonPath)) {
                    $existing = json_decode(file_get_contents($galleryJsonPath), true) ?? [];
                }

                $allImages = array_merge($existing, $galleryList);
                file_put_contents($galleryJsonPath, json_encode($allImages));
            }
        }

        $this->set_flashdata('success', 'System Info Successfully Updated.');
        $resp['status'] = 'success';
        return json_encode($resp);
    }

    private function handle_image_upload($input_name, $file_name, $db_field){
        $upload_path = base_app . "uploads/";
        if(!is_dir($upload_path)){
            if(!mkdir($upload_path, 0755, true)){
                error_log("Failed to create upload directory: $upload_path");
                return false;
            }
        }

        if(!isset($_FILES[$input_name])){
            error_log("No file uploaded for input: $input_name");
            return false;
        }

        $file_error = $_FILES[$input_name]['error'];
        if($file_error !== UPLOAD_ERR_OK){
            error_log("Upload error code {$file_error} for {$input_name}");
            return false;
        }

        $tmp_name = $_FILES[$input_name]['tmp_name'];
        $file_size = $_FILES[$input_name]['size'];
        $original_name = $_FILES[$input_name]['name'];

        $allowed_extensions = ['jpg','jpeg','png','gif','bmp','webp'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowed_extensions)){
            error_log("Invalid file extension for {$input_name}: {$ext}");
            return false;
        }

        if($file_size > 5 * 1024 * 1024){
            error_log("File too large for {$input_name}: {$file_size}");
            return false;
        }

        $unique_file_name = pathinfo($file_name, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
        $new_file_path = $upload_path . $unique_file_name;

        if(move_uploaded_file($tmp_name, $new_file_path)){
            $img_path = "uploads/" . $unique_file_name;

            $stmt = $this->conn->prepare("UPDATE system_info SET meta_value = ? WHERE meta_field = ?");
            if($stmt){
                $stmt->bind_param('ss', $img_path, $db_field);
                $stmt->execute();
                $stmt->close();
            } else {
                error_log("Failed to prepare statement for image update: " . $this->conn->error);
                return false;
            }

            $_SESSION['system_info'][$db_field] = $img_path;
            return true;
        } else {
            error_log("Failed to move uploaded file for {$input_name}");
            return false;
        }
    }

    public function userdata($field = ''){
        if(!empty($field)){
            return $_SESSION['userdata'][$field] ?? null;
        }
        return false;
    }

    public function set_flashdata($flash='', $value=''){
        if(!empty($flash) && !empty($value)){
            $_SESSION['flashdata'][$flash] = $value;
            return true;
        }
        return false;
    }

    public function chk_flashdata($flash = ''){
        return isset($_SESSION['flashdata'][$flash]);
    }

    public function flashdata($flash = ''){
        if(!empty($flash) && isset($_SESSION['flashdata'][$flash])){
            $_tmp = $_SESSION['flashdata'][$flash];
            unset($_SESSION['flashdata'][$flash]);
            return $_tmp;
        }
        return false;
    }

    public function sess_des(){
        if(isset($_SESSION['userdata'])){
            unset($_SESSION['userdata']);
            return true;
        }
        return true;
    }

    public function info($field=''){
        if(!empty($field)){
            return $_SESSION['system_info'][$field] ?? false;
        }
        return false;
    }

    public function set_info($field='', $value=''){
        if(!empty($field) && !empty($value)){
            $_SESSION['system_info'][$field] = $value;
        }
    }
}

// Instantiate the settings object and load system info
$_settings = new SystemSettings();
$_settings->load_system_info();

$action = $_GET['f'] ?? 'none';

switch(strtolower($action)){
    case 'update_settings':
        header('Content-Type: application/json');
        echo $_settings->update_settings_info();
        exit;
        break;
    default:
        // no action
        break;
}
