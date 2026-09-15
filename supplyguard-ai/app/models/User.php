<?php
/**
 * User Model
 */
class User extends Model {
    
    public function findByEmail(string $email): mixed {
        $this->db->query('SELECT * FROM users WHERE email = :email AND is_active = 1');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function getById(int $id): mixed {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateLastLogin(int $id): void {
        $this->db->query('UPDATE users SET last_login = :now WHERE id = :id');
        $this->db->bind(':now', date('Y-m-d H:i:s'));
        $this->db->bind(':id', $id);
        $this->db->execute();
    }

    public function updateProfile(int $id, array $data): bool {
        $this->db->query('UPDATE users SET name = :name, email = :email, password = :password WHERE id = :id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
}
