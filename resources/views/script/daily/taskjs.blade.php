<script>
    // Helper function to load subcategories into any select element
    function loadSubcategories(categoryId, $subCatSelect, selectedSubcatId = null) {
        $subCatSelect.html('<option value="" disabled selected>Loading...</option>').prop('disabled', true);

        if (categoryId) {
            $.ajax({
                url: subcategorySelect.replace(':categoryId', categoryId),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $subCatSelect.html('<option value="">Select Sub Category</option>');

                    if (data.length > 0) {
                        $.each(data, function(key, value) {
                            $subCatSelect.append('<option value="' + value.id + '">' + value.ticketsubcatname + '</option>');
                        });
                        $subCatSelect.prop('disabled', false);

                        // Pre-select saved subcategory if editing
                        if (selectedSubcatId) {
                            $subCatSelect.val(selectedSubcatId);
                        }
                    } else {
                        $subCatSelect.html('<option value="" disabled selected>No Subcategories Found</option>');
                    }
                },
                error: function() {
                    $subCatSelect.html('<option value="" disabled selected>Error loading subcategories</option>');
                }
            });
        } else {
            $subCatSelect.html('<option value="">Select Sub Category</option>').prop('disabled', true);
        }
    }

    $(document).ready(function() {

        // --- ADD MODAL: Dynamic Subcategories ---
        $('#categorySelect').on('change', function() {
            var categoryId = $(this).val();
            loadSubcategories(categoryId, $('#subcategorySelect'));
        });

        // --- EDIT MODAL: Dynamic Subcategories on Category Change ---
        $('#editDailyTaskCategory').on('change', function() {
            var categoryId = $(this).val();
            loadSubcategories(categoryId, $('#editDailyTaskSubcategory'));
        });

        // --- ADD DAILY TASK FORM SUBMIT ---
        $('#adDailyTask').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: dailyTaskCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        $('#adDailyTask')[0].reset();
                        $('#subcategorySelect').html('<option value="">Select Sub Category</option>').prop('disabled', true);
                        $(document).trigger('dailyTaskAdded');
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseText ? JSON.parse(xhr.responseText).message : 'An error occurred';
                    toastr.error(errorMessage);
                }
            });
        });

        // --- EDIT DAILY TASK FORM SUBMIT ---
        $('#editDailyTaskForm').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: dailyTaskUpdateRoute, // Ensure this route variable points to your update route
                type: "POST",
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        $('#editDailyTaskModal').modal('hide');
                        $(document).trigger('dailyTaskAdded'); // Reloads DataTables
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseText ? JSON.parse(xhr.responseText).message : 'An error occurred';
                    toastr.error(errorMessage);
                }
            });
        });

        var currentMonth = ("0" + (new Date().getMonth() + 1)).slice(-2);
        $('#filterMonth').val(currentMonth);

        // --- DATATABLE INITIALIZATION ---
        var dataTable = $('#dailyTaskTable').DataTable({
            "ajax": {
                "url": dailyTaskReadRoute,
                "type": "GET",
                "data": function (d) {
                    // Pass selected month to backend
                    d.month = $('#filterMonth').val();
                }
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {
                    data: 'category.ticketcatname',
                    defaultContent: '<i>-</i>'
                },
                {
                    data: 'subcategory.ticketsubcatname',
                    defaultContent: '<i>-</i>'
                },
                {
                    data: 'dailytaskdesc',
                    defaultContent: ''
                },
                {
                    data: 'status',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data === 'Completed') {
                                return '<span class="badge bg-success">Completed</span>';
                            } else if (data === 'In Progress') {
                                return '<span class="badge bg-warning text-dark">In Progress</span>';
                            } else {
                                return '<span class="badge bg-secondary">Pending</span>';
                            }
                        }
                        return data;
                    }
                },
                {
                    data: 'completed_at',
                    defaultContent: '<span class="text-muted">-</span>',
                    render: function(data, type, row) {
                        if (!data) return '<span class="text-muted">-</span>';

                        if (type === 'sort' || type === 'type') {
                            return data;
                        }

                        return moment(data).format('MMM DD, YYYY h:mm A');
                    }
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var buttons = '<button type="button" class="btn btn-sm btn-success btn-dailytaskedit mr-1 text-light" ' +
                                'data-id="' + row.id + '" ' +
                                'data-cat="' + row.cat_id + '" ' +
                                'data-subcat="' + row.subcat_id + '" ' +
                                'data-dailytaskdesc="' + row.dailytaskdesc + '" ' +
                                'data-status="' + row.status + '" ' +
                                'data-toggle="tooltip" data-placement="top" title="Edit Daily Task.">' +
                                '<i class="ti ti-pencil"></i></button>&nbsp;';
                            buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger dailytask-delete" data-toggle="tooltip" data-placement="top" title="Delete Daily Task."><i class="ti ti-trash"></i></button>';
                            return buttons;
                        } else {
                            return data;
                        }
                    },
                },
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.id);
            }
        });

        // Reload DataTables when month filter changes
        $('#filterMonth').on('change', function() {
            dataTable.ajax.reload();
        });

        $(document).on('dailyTaskAdded', function() {
            dataTable.ajax.reload();
        });

        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    // --- EDIT BUTTON CLICK HANDLER ---
    $(document).on('click', '.btn-dailytaskedit', function() {
        var id = $(this).data('id');
        var catId = $(this).data('cat');
        var subcatId = $(this).data('subcat');
        var dailyTaskDescription = $(this).data('dailytaskdesc');
        var isStatus = $(this).data('status');

        $('#editDailyTaskId').val(id);
        $('#editDailyTaskCategory').val(catId);
        $('#editDailyTaskDescription').val(dailyTaskDescription);
        $('#editDailyTaskStatus').val(isStatus);

        // Fetch subcategories for the selected category and pre-select current subcatId
        loadSubcategories(catId, $('#editDailyTaskSubcategory'), subcatId);

        $('#editDailyTaskModal').modal('show');
    });
</script>
