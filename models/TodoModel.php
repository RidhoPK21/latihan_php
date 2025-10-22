<?php

class TodoModel
{
    private $db;

    public function __construct() {
        // Cek jika ada environment variable DATABASE_URL dari Heroku
        $dbUrl = getenv('DATABASE_URL');

        if ($dbUrl) {
            // Parse URL database dari Heroku
            $dbopts = parse_url($dbUrl);
            $host = $dbopts["host"];
            $port = $dbopts["port"];
            $dbname = ltrim($dbopts["path"], '/');
            $user = $dbopts["user"];
            $password = $dbopts["pass"];
            
            // String koneksi untuk Heroku
            $this->db = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$password}");
        } else {
            // String koneksi untuk local development (jika DATABASE_URL tidak ada)
            // PASTIKAN ANDA MENGGANTI INI SESUAI PENGATURAN LOKAL ANDA
            $host = 'localhost';
            $port = '5432';
            $dbname = 'latihan_php'; // Ganti dengan nama db lokal Anda
            $user = 'postgres';        // Ganti dengan user db lokal Anda
            $password = 'password'; // Ganti dengan password db lokal Anda
            $this->db = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$password}");
        }

        if (!$this->db) {
            die("Error in connection: " . pg_last_error());
        }
    }

    public function getAllTodos($filter = 'all', $search = '')
    {
        // Menggunakan nama tabel 'todo' (tanpa 's')
        $query = 'SELECT * FROM todo';
        $params = [];

        if ($filter === 'finished') {
            $query .= ' WHERE is_finished = true'; // Gunakan boolean true
        } elseif ($filter === 'unfinished') {
            $query .= ' WHERE is_finished = false'; // Gunakan boolean false
        }

        if (!empty($search)) {
            if (strpos($query, 'WHERE') !== false) {
                $query .= ' AND (title ILIKE $1 OR description ILIKE $1)';
            } else {
                $query .= ' WHERE (title ILIKE $1 OR description ILIKE $1)';
            }
            $params[] = '%' . $search . '%';
        }

        $query .= ' ORDER BY id ASC';
        
        // GUNAKAN $this->db, BUKAN $this->conn
        $result = pg_query_params($this->db, $query, $params);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function getTodoById($id)
    {
        $query = 'SELECT * FROM todo WHERE id = $1';
        // GUNAKAN $this->db, BUKAN $this->conn
        $result = pg_query_params($this->db, $query, [$id]);
        return pg_fetch_assoc($result);
    }

    public function createTodo($title, $description)
    {
        $query = 'INSERT INTO todo (title, description) VALUES ($1, $2)';
        // GUNAKAN $this->db, BUKAN $this->conn
        $result = pg_query_params($this->db, $query, [$title, $description]);
        return $result !== false;
    }

    public function updateTodo($id, $title, $description, $is_finished)
    {
        $query = 'UPDATE todo SET title = $1, description = $2, is_finished = $3 WHERE id = $4';
        // GUNAKAN $this->db, BUKAN $this->conn
        $result = pg_query_params($this->db, $query, [$title, $description, $is_finished, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id = $1';
        // GUNAKAN $this->db, BUKAN $this->conn
        $result = pg_query_params($this->db, $query, [$id]);
        return $result !== false;
    }

    public function isTitleExists($title, $id = null)
    {
        $query = 'SELECT id FROM todo WHERE title ILIKE $1';
        $params = [$title];
        if ($id) {
            $query .= ' AND id != $2';
            $params[] = $id;
        }
        // GUNAKAN $this->db, BUKAN $this->conn
        $result = pg_query_params($this->db, $query, $params);
        return pg_num_rows($result) > 0;
    }
}