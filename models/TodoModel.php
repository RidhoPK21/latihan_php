<?php
require_once(__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

public function __construct() {
        // Mengambil detail koneksi dari Environment Variables Vercel
        $host = getenv('PGHOST');
        $port = getenv('PGPORT');
        $dbname = getenv('PGDATABASE');
        $user = getenv('PGUSER');
        $password = getenv('PGPASSWORD');

        // Membangun connection string
        $conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

        // Mencoba terhubung ke database
        $this->conn = pg_connect($conn_string);

        // Opsional: tambahkan pengecekan error
        if (!$this->conn) {
            // Ini akan membantu debugging jika koneksi masih gagal
            error_log("Database connection failed: " . pg_last_error());
            // Hentikan eksekusi untuk mencegah error lebih lanjut
            die("Connection failed. Check server logs.");
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