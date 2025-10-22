<?php
require_once(__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    public function index()
    {
        $todoModel = new TodoModel();
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $todos = $todoModel->getAllTodos($filter, $search);
        include(__DIR__ . '/../views/TodoView.php');
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $todoModel = new TodoModel();
            if ($todoModel->isTitleExists($title)) {
                // Ganti redirect dengan menyimpan pesan ke session
                $_SESSION['error_message'] = 'Judul todo sudah ada, silakan gunakan judul lain.';
                header('Location: index.php');
                exit;
            }
            $todoModel->createTodo($title, $description);
        }
        header('Location: index.php');
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $is_finished = $_POST['is_finished'];
            $todoModel = new TodoModel();
            if ($todoModel->isTitleExists($title, $id)) {
                // Ganti juga di sini
                $_SESSION['error_message'] = 'Judul todo sudah ada, silakan gunakan judul lain.';
                header('Location: index.php');
                exit;
            }
            $todoModel->updateTodo($id, $title, $description, $is_finished);
        }
        header('Location: index.php');
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            $todoModel->deleteTodo($id);
        }
        header('Location: index.php');
    }

    public function detail()
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            $todo = $todoModel->getTodoById($id);
            // Anda bisa membuat view baru untuk detail atau menampilkannya dalam modal
            // Untuk sekarang, kita akan kirim sebagai JSON
            header('Content-Type: application/json');
            echo json_encode($todo);
        }
    }
}