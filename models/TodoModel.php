<?php
require_once(__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

   public function __construct() {
    // Prioritas 1: Cek environment variable dari Vercel (POSTGRES_URL)
    $dbUrl = getenv('POSTGRES_URL');

    // Prioritas 2: Jika tidak ada, cek punya Heroku (DATABASE_URL)
    if ($dbUrl === false) {
        $dbUrl = getenv('DATABASE_URL');
    }

    if ($dbUrl !== false) {
        // Parse URL database dari Vercel/Heroku
        $dbopts = parse_url($dbUrl);
        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $dbname = ltrim($dbopts["path"], '/');
        $user = $dbopts["user"];
        $password = $dbopts["pass"];
        
        // String koneksi untuk Vercel/Heroku
        $this->db = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$password}");
    } else {
        // Prioritas 3: Koneksi untuk local development
        $host = 'localhost';
        $port = '5432';
        $dbname = 'latihan_php'; // Ganti dengan nama db lokal Anda
        $user = 'postgres';     // Ganti dengan user db lokal Anda
        $password = 'password'; // Ganti dengan password db lokal Anda
        $this->db = pg_connect("host={$host} port={$port} dbname={$dbname} user={$user} password={$password}");
    }

    if (!$this->db) {
        die("Error in connection: " . pg_last_error());
    }
}

    public function getAllTodos($filter = 'all', $search = '')
    {
        $query = 'SELECT * FROM todo';
        $params = [];

        if ($filter === 'finished') {
            $query .= ' WHERE is_finished = 1';
        } elseif ($filter === 'unfinished') {
            $query .= ' WHERE is_finished = 0';
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
        $result = pg_query_params($this->conn, $query, $params);
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
        $result = pg_query_params($this->conn, $query, [$id]);
        return pg_fetch_assoc($result);
    }

    public function createTodo($title, $description)
    {
        $query = 'INSERT INTO todo (title, description) VALUES ($1, $2)';
        $result = pg_query_params($this->conn, $query, [$title, $description]);
        return $result !== false;
    }

    public function updateTodo($id, $title, $description, $is_finished)
    {
        $query = 'UPDATE todo SET title = $1, description = $2, is_finished = $3 WHERE id = $4';
        $result = pg_query_params($this->conn, $query, [$title, $description, $is_finished, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id = $1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    public function isTitleExists($title, $id = null)
{
    // Ganti '=' menjadi 'ILIKE' untuk perbandingan case-insensitive
    $query = 'SELECT id FROM todo WHERE title ILIKE $1';
    $params = [$title];
    if ($id) {
        $query .= ' AND id != $2';
        $params[] = $id;
    }
    $result = pg_query_params($this->conn, $query, $params);
    return pg_num_rows($result) > 0;
}
}