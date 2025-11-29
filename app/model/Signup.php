<?php
require_once __DIR__ . '/../core/db.php';

class Signup {

    private $con;

    public function __construct(){
        $db = new Database();
        $this->con = $db->getConnection();
    }

    public function emailExists($email){
        $stmt = $this->con->prepare("SELECT user_id FROM users WHERE user_email = ? LIMIT 1");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function registerUser($data){
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Handle profile image
        $avatar = 'account_icon.png';
        if($data['avatar'] && $data['avatar']['error']===0){
            $uploadDir = __DIR__ . '/../../public/assets/images/';
            $avatar = time().'_'.$data['avatar']['name'];
            move_uploaded_file($data['avatar']['tmp_name'],$uploadDir.$avatar);
        }

        $roleId = 2; // Customer
        $stmt = $this->con->prepare(
            "INSERT INTO users (role_id,user_name,user_email,user_contact,user_password,user_avatar,birthdate,province,city,address) 
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            "isssssssss",
            $roleId,
            $data['name'],
            $data['email'],
            $data['phone'],
            $passwordHash,
            $avatar,
            $data['birthdate'],
            $data['province'],
            $data['city'],
            $data['address']
        );
        $stmt->execute();
    }
}