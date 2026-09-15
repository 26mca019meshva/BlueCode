<?php
/**
 * Authentication Controller
 */
class AuthController extends Controller {

    public function login(): void {
        if (Auth::isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $data = ['pageTitle' => 'Login'];
        $this->view('auth/login', $data);
    }

    public function doLogin(): void {
        if (!$this->isPost()) {
            $this->redirect('login');
        }

        $email = $this->getPost('email');
        $password = $this->getPost('password');

        // Validate
        $validator = new Validator($_POST);
        $validator->required('email', 'Email')
                  ->email('email', 'Email')
                  ->required('password', 'Password');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('login');
        }

        // Authenticate
        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            Auth::login($user);
            $userModel->updateLastLogin($user->id);
            $this->logActivity('login', 'auth', 'User logged in successfully');
            Session::setFlash('success', 'Welcome back, ' . $user->name . '!');
            $this->redirect('dashboard');
        } else {
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect('login');
        }
    }

    public function logout(): void {
        if (Auth::isLoggedIn()) {
            $this->logActivity('logout', 'auth', 'User logged out');
        }
        Auth::logout();
        Session::start();
        Session::setFlash('success', 'You have been logged out.');
        $this->redirect('login');
    }
}
