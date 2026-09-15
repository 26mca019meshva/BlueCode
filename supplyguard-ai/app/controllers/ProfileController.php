<?php
/**
 * Profile Controller
 */
class ProfileController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        if (!Auth::isLoggedIn()) {
            $this->redirect('auth/login');
        }
        $this->userModel = $this->model('User');
    }
    
    public function index() {
        $user = $this->userModel->getById(Auth::getUserId());
        
        $data = [
            'pageTitle' => 'My Profile',
            'user' => $user,
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'confirm_password' => '',
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];
        
        $this->view('profile/index', $data);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            // Validate CSRF token (if implemented, skipping for brevity but good practice)
            
            $user = $this->userModel->getById(Auth::getUserId());
            
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'pageTitle' => 'My Profile',
                'user' => $user,
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            
            // Validate Name
            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter name';
            }
            
            // Validate Email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            } else {
                // Check if email changed and if new email exists
                if ($data['email'] !== $user->email) {
                    if ($this->userModel->findByEmail($data['email'])) {
                        $data['email_err'] = 'Email is already taken';
                    }
                }
            }
            
            // Validate Password if filled
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 6) {
                    $data['password_err'] = 'Password must be at least 6 characters';
                }
                if ($data['password'] !== $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Passwords do not match';
                }
            }
            
            // Make sure no errors
            if (empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                // Hash Password if provided
                if (!empty($data['password'])) {
                    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                } else {
                    $data['password'] = $user->password; // Keep old password
                }
                
                // Update User
                if ($this->userModel->updateProfile(Auth::getUserId(), $data)) {
                    // Update session name if changed
                    $_SESSION['user_name'] = $data['name'];
                    $_SESSION['user_email'] = $data['email'];
                    
                    // Add Activity Log
                    $this->model('ActivityLog')->log(
                        Auth::getUserId(),
                        'update',
                        'Profile',
                        'User updated their profile details.'
                    );
                    
                    Session::setFlash('success', 'Profile updated successfully!');
                    $this->redirect('profile');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('profile/index', $data);
            }
        } else {
            $this->redirect('profile');
        }
    }
}
