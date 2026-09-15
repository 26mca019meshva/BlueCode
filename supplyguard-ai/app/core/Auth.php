<?php
/**
 * SupplyGuard AI - Authentication Helper
 * 
 * Handles user authentication, role checking, and CSRF protection.
 */

class Auth {

    /**
     * Login a user (store in session)
     */
    public static function login(object $user): void {
        Session::set('user_id', $user->id);
        Session::set('user_name', $user->name);
        Session::set('user_email', $user->email);
        Session::set('user_role', $user->role);
        Session::set('logged_in', true);
        session_regenerate_id(true);
    }

    /**
     * Logout the current user
     */
    public static function logout(): void {
        Session::destroy();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return Session::get('logged_in', false) === true;
    }

    /**
     * Get current user ID
     */
    public static function getUserId(): ?int {
        return Session::get('user_id');
    }

    /**
     * Get current user name
     */
    public static function getUserName(): ?string {
        return Session::get('user_name');
    }

    /**
     * Get current user email
     */
    public static function getUserEmail(): ?string {
        return Session::get('user_email');
    }

    /**
     * Get current user role
     */
    public static function getUserRole(): ?string {
        return Session::get('user_role');
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole(string|array $roles): bool {
        $userRole = self::getUserRole();
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        return $userRole === $roles;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool {
        return self::hasRole(ROLE_ADMIN);
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string {
        if (!Session::has(CSRF_TOKEN_NAME)) {
            $token = bin2hex(random_bytes(32));
            Session::set(CSRF_TOKEN_NAME, $token);
        }
        return Session::get(CSRF_TOKEN_NAME);
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(?string $token): bool {
        $sessionToken = Session::get(CSRF_TOKEN_NAME);
        if ($sessionToken && $token && hash_equals($sessionToken, $token)) {
            // Regenerate token after verification
            Session::remove(CSRF_TOKEN_NAME);
            return true;
        }
        return false;
    }

    /**
     * Get CSRF hidden input field
     */
    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
