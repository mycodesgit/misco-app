<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#addOffice').submit(function(event) {
            event.preventDefault();

            var formData = $(this).serialize();

            $.ajax({
                url: officeCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('officeAdded');
                        $('input[name="office_name"]').val('');
                        $('input[name="office_abbr"]').val('');
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

        var dataTable = $('#officeTable').DataTable({
            "ajax": {
                "url": officeReadRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'office_name'},
                {data: 'office_abbr'},
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var buttons = '<button type="button" class="btn btn-sm btn-success btn-officeedit mr-1 text-light" data-id="' + row.id + '" data-offname="' + row.office_name + '" data-offabbr="' + row.office_abbr + '" data-toggle="tooltip" data-placement="top" title="Edit Office.">';
                            buttons += '<i class="ti ti-pencil"></i> </button>'+'&nbsp;';
                            buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger office-delete" data-toggle="tooltip" data-placement="top" title="Delete Office."><i class="ti ti-trash"></i> </button>';

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
        $(document).on('officeAdded', function() {
            dataTable.ajax.reload();
        });
        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    $(document).on('click', '.btn-officeedit', function() {
        var id = $(this).data('id');
        var offName = $(this).data('offname');
        var offAbbr = $(this).data('offabbr');

        $('#editOfficeId').val(id);
        $('#editOfficeName').val(offName);
        $('#editOfficeAbbr').val(offAbbr);

        $('#editOfficeModal').modal('show');
    });

    $('#editOfficeForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: officeUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editOfficeModal').modal('hide');
                    $(document).trigger('officeAdded');
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
        $('#addOffice').validate({
            rules: {
                office_name: {
                    required: true,
                },
                office_abbr: {
                    required: true,
                },
            },
            messages: {
                office_name: {
                    required: "Please Enter Office Name",
                },
                office_abbr: {
                    required: "Please Enter Office Abbreviation",
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
