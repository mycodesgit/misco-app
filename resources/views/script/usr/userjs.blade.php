<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#addUser').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: userCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('userAdded');
                        $('#createUserModal').modal('hide');
                        $('#addUser')[0].reset();
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

        var dataTable = $('#userviewTable').DataTable({
            "ajax": {
                "url": userReadRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'lname'},
                {data: 'fname'},
                {data: 'mname'},
                // {data: 'campus_name'},
                // {data: 'office_abbr'},
                {data: 'email'},
                {data: 'role'},
                {data: 'ustatus',
                        render: function(data, type, row) {
                        switch(parseInt(data)) {
                            case 1:
                                return '<span class="badge bg-info">Enabled</span>';
                            case 2:
                                return '<span class="badge bg-danger">Disabled</span>';
                            case 3:
                                return '<span class="badge bg-warning">Deleted</span>';
                            default:
                                return '<span class="badge bg-secondary">Unknown Status</span>';
                        }
                    },
                },
                {data: 'isAllowed'},
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var buttons = '<button type="button" class="btn btn-sm btn-success btn-useredit text-light" data-id="' + row.id + '" data-fname="' + row.fname + '" data-mname="' + row.mname + '" data-lname="' + row.lname + '" data-ext="' + row.ext + '" data-email="' + row.email + '" data-office="' + row.office_id + '" data-gender="' + row.gender + '" data-role="' + row.role + '" data-campus="' + row.campus_id + '" data-permission="' + row.isAllowed + '" data-toggle="tooltip" data-placement="top" title="Edit User."><i class="ti ti-pencil"></i> </button>'+'&nbsp;';
                                buttons += '<button type="button" class="btn btn-sm btn-light btn-passedit" data-id="' + row.id + '" data-password="' + row.password + '" data-toggle="tooltip" data-placement="top" title="Edit User Password."><i class="ti ti-lock"></i> </button>'+'&nbsp;';
                                buttons += '<button type="button" class="btn btn-sm btn-warning btn-ustatusedit" data-id="' + row.id + '" data-ustatus="' + row.ustatus + '" data-toggle="tooltip" data-placement="top" title="Enabled/Disabled."><i class="ti ti-toggle-left"></i> </button>'+'&nbsp;';
                            if (isAdmin || isChecker) {
                                buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger userpr-delete" data-toggle="tooltip" data-placement="top" title="Delete Category."><i class="ti ti-trash"></i> </button>';
                            }
                            return buttons;
                        } else {
                            return data;
                        }
                    },
                },
            ],
            "createdRow": function (row, data, index) {
                $(row).attr('id', 'tr-' + data.uid);
            }
        });
        $(document).on('userAdded', function() {
            dataTable.ajax.reload();
        });

        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    $(document).on('click', '.btn-useredit', function() {
        var id = $(this).data('id');
        var fName = $(this).data('fname');
        var mName = $(this).data('mname');
        var lName = $(this).data('lname');
        var extension = $(this).data('ext');
        var email = $(this).data('email');
        var office = $(this).data('office');
        var gender = $(this).data('gender');
        var role = $(this).data('role');
        var campus = $(this).data('campus');
        var permission = $(this).data('permission');

        $('#edituserId').val(id);
        $('#editfirstname').val(fName);
        $('#editmiddlename').val(mName);
        $('#editlastname').val(lName);
        $('#editextension').val(extension);
        $('#edituseremail').val(email);
        $('#editoffice').val(office);
        $('#editgender').val(gender);
        $('#editrole').val(role);
        $('#editcampus').val(campus);
        $('#editpermission').val(permission);

        $('#editInfoModal').modal('show');
    });

    $('#editInfoForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: userUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editInfoModal').modal('hide');
                    $(document).trigger('userAdded');
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

    $(document).on('click', '.btn-passedit', function() {
        var id = $(this).data('id');
        var password = $(this).data('password');

        $('#editPasswordId').val(id);
        $('#editPassword').val(password);

        $('#editPasswordModal').modal('show');
    });

    $('#editPasswordForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: userPassUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editPasswordModal').modal('hide');
                    $(document).trigger('userAdded');
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

    $(document).on('click', '.btn-ustatusedit', function() {
        var id = $(this).data('id');
        var ustatus = $(this).data('ustatus');

        $('#editUstatusId').val(id);
        $('#editUstatusName').val(ustatus);

        $('#editUstatusModal').modal('show');
    });

    $('#editUstatusForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: userStatusUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editUstatusModal').modal('hide');
                    $(document).trigger('userAdded');
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
</script>
