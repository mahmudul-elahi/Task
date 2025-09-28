<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Module</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    <div class="container mt-3">
        <div class="row">
            <div class="col-md-4 ms-auto text-end">
                <a class="btn btn-dark" href="{{ route('readme') }}">Read Me</a>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Task Module</h5>
                <div id="alert-placeholder"></div>

                <form id="task-form" onsubmit="return false;">
                    <div id="repeater"></div>
                    <div class="mt-3 d-flex gap-2">
                        <button type="button" id="submit-btn" class="btn btn-primary">Submit All</button>
                        <div class="text-end ms-auto">
                            <button type="button" id="add-row" class="btn btn-outline-secondary me-2">Add
                                Row</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Saved Tasks</h5>
                <div class="table-responsive">
                    <table class="table m-0" id="task-table">
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

    <template id="row-template">
        <div class="repeater-row">
            <div class="mb-3">
                <label class="form-label">Title <span class="text-warning">*</span></label>
                <input type="text" class="form-control title" placeholder="Task title">
                <div class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description <small class="text-muted">(opt)</small></label>
                <textarea rows="4" class="form-control description" placeholder="Optional"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select status">
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <button type="button" class="btn-remove cross-temp-btn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </template>

    <script>
        $(function() {
            const repeater = $('#repeater');
            const tpl = document.getElementById('row-template');

            function addRow(values = {}) {
                const clone = $(tpl.content.cloneNode(true));
                if (values.title) clone.find('.title').val(values.title);
                if (values.description) clone.find('.description').val(values.description);
                if (values.status) clone.find('.status').val(values.status);
                repeater.append(clone);
            }

            addRow();

            $('#add-row').click(() => addRow());

            $(document).on('click', '.btn-remove', function() {
                $(this).closest('.repeater-row').remove();
            });

            function collectRows() {
                const rows = [];
                $('.repeater-row').each(function() {
                    const row = $(this);
                    rows.push({
                        title: row.find('.title').val().trim(),
                        description: row.find('.description').val().trim(),
                        status: row.find('.status').val()
                    });
                });
                return rows;
            }

            function showAlert(msg, type = 'success') {
                $('#alert-placeholder').html(`<div class="alert alert-${type} alert-dismissible fade show">
                ${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`);
            }

            function loadTasks(tasks) {
                const tbody = $('#task-list');
                if (!tasks || tasks.length === 0) {
                    tbody.html('<tr><td colspan="5" class="text-center">No tasks yet.</td></tr>');
                    return;
                }

                let rows = '';
                tasks.forEach((t, i) => {
                    rows += `
                    <tr data-id="${t.id}">
                        <td class="ps-3">${i + 1}</td>
                        <td>${t.title || ''}</td>
                        <td>${t.description || ''}</td>
                        <td>${t.status || 'Pending'}</td>
                        <td><button class="btn btn-sm btn-danger btn-delete"><i class="bi bi-trash"></i></button></td>
                    </tr>
                `;
                });
                tbody.html(rows);
            }

            $('#submit-btn').click(function() {
                $('.invalid-feedback').text('');
                $('.title').removeClass('is-invalid');

                const payload = {
                    tasks: collectRows()
                };
                let hasError = false;

                $('.repeater-row').each(function() {
                    if (!$(this).find('.title').val().trim()) {
                        $(this).find('.title').addClass('is-invalid');
                        $(this).find('.invalid-feedback').text('Title is required');
                        hasError = true;
                    }
                });
                if (hasError) return;

                const btn = $(this).prop('disabled', true).text('Submitting...');

                $.ajax({
                    url: '/tasks',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(payload),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        btn.prop('disabled', false).text('Submit All');
                        showAlert('Tasks added!');
                        repeater.empty();
                        addRow();
                        loadTasks(res.savedTasks);
                    },
                    error: function() {
                        btn.prop('disabled', false).text('Submit All');
                        showAlert('Error occurred', 'danger');
                    }
                });
            });

            $(document).on('click', '.btn-delete', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');

                if (!confirm('Are you sure you want to delete this task?')) return;

                $.ajax({
                    url: `/tasks/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.success) {
                            row.remove();
                            showAlert('Task deleted!');
                        } else {
                            showAlert('Failed to delete.', 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error deleting task.', 'danger');
                    }
                });
            });

            const savedTasksFromDB = @json($savedTasks ?? []);
            loadTasks(savedTasksFromDB);
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
