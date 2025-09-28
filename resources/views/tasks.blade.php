<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Module</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-dark" href="{{ route('readme') }}">Read Me</a>
        </div>

        <div class="row g-4">
            <!-- Task Form -->
            <div class="col-md-6">
                <div class="card p-4">
                    <h5 class="mb-3">Task Module</h5>
                    <div id="alert-placeholder"></div>

                    <form id="task-form" onsubmit="return false;">
                        <div id="repeater"></div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="button" id="submit-btn" class="btn btn-primary">Submit All</button>
                            <button type="button" id="add-row" class="btn btn-outline-secondary ms-auto">Add
                                Row</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Saved Tasks Table -->
            <div class="col-md-6">
                <div class="card p-4">
                    <h5 class="mb-3">Saved Tasks</h5>
                    <div class="table-responsive">
                        <table class="table table-hover m-0" id="task-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="task-list">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row Template -->
    <template id="row-template">
        <div class="repeater-row">
            <div class="mb-3">
                <label class="form-label">Title <span class="text-warning">*</span></label>
                <input type="text" class="form-control title" placeholder="Task title">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description <small class="text-muted">(opt)</small></label>
                <textarea rows="3" class="form-control description" placeholder="Optional"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select status">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            <button type="button" class="btn-remove cross-temp-btn btn btn-outline-danger">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </template>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function() {
            //repeater create task form
            const repeater = $('#repeater');
            const tpl = document.getElementById('row-template');


            const addRow = (values = {}) => {
                const clone = $(tpl.content.cloneNode(true));
                if (values.title) clone.find('.title').val(values.title);
                if (values.description) clone.find('.description').val(values.description);
                if (values.status) clone.find('.status').val(values.status);
                repeater.append(clone);
            };

            //first form
            addRow();

            //add form
            $('#add-row').click(() => addRow());

            //remove form
            $(document).on('click', '.btn-remove', function() {
                $(this).closest('.repeater-row').remove();
            });

            const collectRows = () => $('.repeater-row').map(function() {
                const row = $(this);
                return {
                    title: row.find('.title').val().trim(),
                    description: row.find('.description').val().trim(),
                    status: row.find('.status').val()
                };
            }).get();

            const showAlert = (msg, type = 'success') => {
                $('#alert-placeholder').html(
                    `<div class="alert alert-${type} alert-dismissible fade show">
                ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`
                );
            };

            //load data on the table
            const loadTasks = tasks => {
                const tbody = $('#task-list');
                if (!tasks.length) {
                    tbody.html('<tr><td colspan="5" class="text-center">No tasks yet.</td></tr>');
                    return;
                }

                let rows = '';
                tasks.forEach((t, i) => {
                    rows += `<tr data-id="${t.id}">
                        <td class="ps-3">${i + 1}</td>
                        <td>${t.title}</td>
                        <td>${t.description || ''}</td>
                        <td>
                            <select class="form-select form-select-sm status-dropdown" data-id="${t.id}">
                                <option value="Pending" ${t.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                <option value="In Progress" ${t.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
                                <option value="Completed" ${t.status === 'Completed' ? 'selected' : ''}>Completed</option>
                            </select>
                        </td>
                        <td><button class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i></button></td>
                    </tr>`;
                });

                tbody.html(rows);
            };

            //get all tasks when page loads
            const fetchTasks = () => $.get('/api/tasks', res => res.success && loadTasks(res.savedTasks));
            fetchTasks();

            //update status
            $(document).on('change', '.status-dropdown', function() {
                const select = $(this);
                const id = select.data('id');
                const status = select.val();

                $.ajax({
                    url: `/api/tasks/${id}/status`,
                    type: 'PATCH',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        status: status
                    }),
                    success: res => {
                        if (res.success) {
                            showAlert(`Task status updated to "${status}"`);
                        } else {
                            showAlert(res.message || 'Failed to update status', 'danger');
                        }
                    },
                    error: () => showAlert('Error updating status', 'danger')
                });
            });



            //create tasks
            $('#submit-btn').click(function() {
                let hasError = false;
                $('.repeater-row').each(function() {
                    const input = $(this).find('.title');
                    input.removeClass('is-invalid');
                    $(this).find('.invalid-feedback').text('');
                    if (!input.val().trim()) {
                        input.addClass('is-invalid');
                        $(this).find('.invalid-feedback').text('Title is required');
                        hasError = true;
                    }
                });
                if (hasError) return;

                const payload = {
                    tasks: collectRows()
                };
                const btn = $(this).prop('disabled', true).text('Submitting...');
                $.ajax({
                    url: '/api/tasks',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    success: res => {
                        btn.prop('disabled', false).text('Submit All');
                        showAlert('Tasks added!');
                        repeater.empty();
                        addRow();
                        loadTasks(res.savedTasks);
                    },
                    error: () => {
                        btn.prop('disabled', false).text('Submit All');
                        showAlert('Error occurred', 'danger');
                    }
                });
            });


            //delete task
            $(document).on('click', '.btn-delete', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                if (!confirm('Are you sure you want to delete this task?')) return;

                $.ajax({
                    url: `/api/tasks/${id}`,
                    type: 'DELETE',
                    success: res => res.success ? (row.remove(), showAlert('Task deleted!')) :
                        showAlert('Failed to delete.', 'danger'),
                    error: () => showAlert('Error deleting task.', 'danger')
                });
            });
        });
    </script>

</body>

</html>
