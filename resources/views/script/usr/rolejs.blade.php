<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#addRole').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: rolesCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('roleAdded');
                        $('#addRole')[0].reset();
                    } else {
                        toastr.error(response.message);
                        console.log(response);
                    }
                },
                error: function(xhr, status, error, message) {
                    var errorMessage = xhr.responseText ? JSON.parse(xhr.responseText).message : 'An error occurred';
                    toastr.error(errorMessage);
                }
            });
        });

        var dataTable = $('#rolesTable').DataTable({
            "ajax": {
                "url": rolesReadRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'rolename'},
                {data: 'status',
                        render: function(data, type, row) {
                        switch(parseInt(data)) {
                            case 1:
                                return '<span class="badge bg-info">Enabled</span>';
                            case 2:
                                return '<span class="badge bg-warning">Disabled</span>';
                            case 3:
                                return '<span class="badge bg-warning">Deleted</span>';
                            default:
                                return '<span class="badge bg-secondary">Unknown Status</span>';
                        }
                    },
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var buttons = '<button type="button" class="btn btn-sm btn-success btn-roleedit mr-1 text-light" data-id="' + row.id + '" data-rolename="' + row.rolename + '" data-status="' + row.status + '" data-toggle="tooltip" data-placement="top" title="Edit Year.">';
                            buttons += '<i class="ti ti-pencil"></i> </button>'+'&nbsp;';
                            buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger role-delete" data-toggle="tooltip" data-placement="top" title="Delete Year."><i class="ti ti-trash"></i> </button>';
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
        $(document).on('roleAdded', function() {
            dataTable.ajax.reload();
        });
        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    $(document).on('click', '.btn-roleedit', function() {
        var id = $(this).data('id');
        var roleName = $(this).data('rolename');
        var roleStatus = $(this).data('status');

        $('#editRoleId').val(id);
        $('#editRoleName').val(roleName);
        $('#editYearStatus').val(roleStatus);
        $('#editRoleModal').modal('show');
    });

    $('#editRoleForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: rolesUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editRoleModal').modal('hide');
                    $(document).trigger('roleAdded');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error, message) {
                var errorMessage = xhr.responseText ? JSON.parse(xhr.responseText).message : 'An error occurred';
                toastr.error(errorMessage);
            }
        });
    });

    $(function () {
        $('#addRole').validate({
            rules: {
                rolename: {
                    required: true,
                },
            },
            messages: {
                rolename: {
                    required: "Please Enter Role",
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.col-md-12').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
        });
    });
</script>
