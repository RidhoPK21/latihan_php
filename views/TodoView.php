<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP - Aplikasi Todolist</title>
    
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

    body {
        background: linear-gradient(to right top, #d3e4f5, #f7f8fa);
        font-family: 'Poppins', sans-serif;
    }
    .todo-wrapper {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background-color: #fdfdfd;
        border-radius: 15px;
        box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }
    .todo-header h1 {
        font-weight: 600;
        color: #333;
    }
    .todo-item {
        display: flex;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid #e0e0e0;
        border-left-width: 5px;
        border-radius: 10px;
        transition: all 0.2s ease-in-out;
    }
    .todo-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .todo-item-pending {
        background-color: #fff8e1;
        border-left-color: #ffab00;
    }
    .todo-item-finished {
        background-color: #e8f5e9;
        border-left-color: #4caf50;
    }
    .todo-item-finished .todo-title {
        text-decoration: line-through;
        color: #888;
    }
    .todo-title {
        font-weight: 500;
        color: #495057;
    }
    .todo-actions {
        margin-left: auto;
        display: flex;
        gap: 10px;
    }
    .btn-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .badge {
        font-weight: 500;
    }

    /* === GAYA UNTUK SEMUA MODAL === */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    .modal-header {
        border-bottom: 0;
        padding: 1.25rem;
    }
    /* GAYA HEADER MODAL EDIT (KUNING) */
    .modal-header-edit {
        background-color: #fff9e6;
        color: #664d03;
    }
    /* GAYA HEADER MODAL DETAIL (BIRU) */
    .modal-header-detail {
        background-color: #e7f3ff;
        color: #0a58ca;
    }
    /* GAYA HEADER MODAL HAPUS (MERAH) */
    .modal-header-delete {
        background-color: #f8d7da;
        color: #842029;
    }
    .form-control, .form-select {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(255, 193, 7, 0.25);
        border-color: #ffca2c;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="todo-wrapper">
            <div class="todo-header d-flex justify-content-between align-items-center mb-4">
                <h1>My Todos</h1>
                <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addTodo">
                    <i class="bi bi-plus-lg"></i> Tambah Todo
                </button>
            </div>

            <?php
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Gagal!</strong> ' . htmlspecialchars($_SESSION['error_message']) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
                unset($_SESSION['error_message']);
            }
            ?>

            <div class="row mb-4 align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <form action="index.php" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Cari todo..." value="<?= htmlspecialchars($search ?? '') ?>">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter ?? 'all') ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                <div class="col-md-6 d-flex justify-content-md-end">
                    <div class="btn-group">
                        <a href="?filter=all&search=<?= htmlspecialchars($search ?? '') ?>" class="btn btn-outline-primary <?= ($filter ?? 'all') === 'all' ? 'active' : '' ?>">Semua</a>
                        <a href="?filter=finished&search=<?= htmlspecialchars($search ?? '') ?>" class="btn btn-outline-success <?= ($filter ?? '') === 'finished' ? 'active' : '' ?>">Selesai</a>
                        <a href="?filter=unfinished&search=<?= htmlspecialchars($search ?? '') ?>" class="btn btn-outline-danger <?= ($filter ?? '') === 'unfinished' ? 'active' : '' ?>">Belum Selesai</a>
                    </div>
                </div>
            </div>

<div class="todo-list" id="todo-list">
    <?php if (!empty($todos)): ?>
        <?php foreach ($todos as $todo): ?>
            <?php
                // Tentukan class CSS berdasarkan status is_finished
                $item_class = $todo['is_finished'] ? 'todo-item-finished' : 'todo-item-pending';
            ?>
            <div class="todo-item <?= $item_class ?>" data-id="<?= $todo['id'] ?>">
                <div>
                    <p class="mb-0 todo-title"><?= htmlspecialchars($todo['title']) ?></p>
                    <small class="text-muted"><?= date('d F Y', strtotime($todo['created_at'])) ?></small>
                </div>
                <div class="ms-4">
                    <?php if ($todo['is_finished']): ?>
                        <span class="badge rounded-pill bg-success">Selesai</span>
                    <?php else: ?>
                        <span class="badge rounded-pill bg-warning text-dark">Belum Selesai</span>
                    <?php endif; ?>
                </div>
                <div class="todo-actions">
                    <button class="btn btn-info btn-icon text-white" onclick="showModalDetailTodo(<?= $todo['id'] ?>)" title="Lihat Detail">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-warning btn-icon text-white" onclick="showModalEditTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>', '<?= htmlspecialchars(addslashes($todo['description'] ?? '')) ?>', <?= $todo['is_finished'] ?>)" title="Ubah">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-danger btn-icon" onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars(addslashes($todo['title'])) ?>')" title="Hapus">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-muted p-5 border rounded-3">
            <i class="bi bi-journal-x fs-1"></i>
            <p class="mt-3 mb-0">Belum ada todo yang ditambahkan.</p>
        </div>
    <?php endif; ?>
</div>
        </div>
    </div>

    <div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTodoLabel">Tambah Data Todo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="?page=create" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="inputTitle" class="form-label">Judul</label>
                            <input type="text" name="title" class="form-control" id="inputTitle" placeholder="Contoh: Belajar PHP" required>
                        </div>
                        <div class="mb-3">
                            <label for="inputDescription" class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" id="inputDescription" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-edit"> 
                <h5 class="modal-title" id="editTodoLabel"><i class="bi bi-pencil-fill me-2"></i>Ubah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <form action="?page=update" method="POST">
                    <input name="id" type="hidden" id="inputEditTodoId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="inputEditTitle" class="form-label">Judul</label>
                            <input type="text" name="title" class="form-control" id="inputEditTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="inputEditDescription" class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" id="inputEditDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="selectEditStatus" class="form-label">Status</label>
                            <select class="form-select" name="is_finished" id="selectEditStatus">
                                <option value="0">Belum Selesai</option>
                                <option value="1">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailTodo" tabindex="-1" aria-labelledby="detailTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-detail">
                <h5 class="modal-title" id="detailTodoLabel"><i class="bi bi-eye me-2"></i>Detail Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 id="detailTodoTitle" class="mb-3"></h5>
                <p class="text-muted">Deskripsi:</p>
                <div id="detailTodoDescription" class="p-3 bg-light rounded border mb-3"></div>
                <small class="text-muted" id="detailTodoCreatedAt"></small>
            </div>
        </div>
    </div>
</div>
    <div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-delete">
                <h5 class="modal-title" id="deleteTodoLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Kamu akan menghapus todo <strong class="text-danger" id="deleteTodoTitle"></strong> secara permanen.</p>
                <p class="fw-bold">Apakah kamu yakin?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="btnDeleteTodo" class="btn btn-danger">Ya, Hapus Saja</a>
            </div>
        </div>
    </div>
</div>

    <script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        // Fungsi untuk Modal
        function showModalEditTodo(todoId, title, description, is_finished) {
            document.getElementById("inputEditTodoId").value = todoId;
            document.getElementById("inputEditTitle").value = title;
            document.getElementById("inputEditDescription").value = description;
            document.getElementById("selectEditStatus").value = is_finished;
            new bootstrap.Modal(document.getElementById("editTodo")).show();
        }

        async function showModalDetailTodo(todoId) {
            const response = await fetch(`?page=detail&id=${todoId}`);
            const todo = await response.json();
            document.getElementById("detailTodoTitle").innerText = todo.title;
            document.getElementById("detailTodoDescription").innerText = todo.description || "Tidak ada deskripsi.";
            document.getElementById("detailTodoCreatedAt").innerText = `Dibuat pada: ${new Date(todo.created_at).toLocaleString()}`;
            new bootstrap.Modal(document.getElementById("detailTodo")).show();
        }

        function showModalDeleteTodo(todoId, title) {
            document.getElementById("deleteTodoTitle").innerText = `'${title}'`;
            document.getElementById("btnDeleteTodo").setAttribute("href", `?page=delete&id=${todoId}`);
            new bootstrap.Modal(document.getElementById("deleteTodo")).show();
        }

        // Inisialisasi SortableJS
        new Sortable(document.getElementById('todo-list'), {
            animation: 150,
            ghostClass: 'bg-light'
        });

        // Alert auto-close
        window.addEventListener('load', function() {
            const alert = document.querySelector('.alert-dismissible');
            if (alert) {
                setTimeout(() => new bootstrap.Alert(alert).close(), 3000);
            }
        });
    </script>
</body>
</html>