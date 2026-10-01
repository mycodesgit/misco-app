<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#adSubCategory').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: subcategoryCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('subcategoryAdded');
                        $('#adSubCategory')[0].reset();
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

        var dataTable = $('#subcategoryTable').DataTable({
            "ajax": {
                "url": subcategoryReadRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'category.ticketcatname'},
                {data: 'ticketsubcatname'},
                {data: 'status',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data == 1 ? 'Available' : 'No';
                        } else {
                            return data;
                        }
                    },
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var buttons = '<button type="button" class="btn btn-sm btn-success btn-categorysubedit mr-1 text-light" data-id="' + row.id + '" data-catname="' + row.cat_id + '" data-categorysubname="' + row.ticketsubcatname + '" data-status="' + row.status + '" data-toggle="tooltip" data-placement="top" title="Edit Sub Category.">';
                            buttons += '<i class="ti ti-pencil"></i> </button>' +'&nbsp;';
                            buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger category-delete" data-toggle="tooltip" data-placement="top" title="Delete Sub Category."><i class="ti ti-trash"></i> </button>';
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
        $(document).on('subcategoryAdded', function() {
            dataTable.ajax.reload();
        });

        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    $(document).on('click', '.btn-categorysubedit', function() {
        var id = $(this).data('id');
        var cateName = $(this).data('catname');
        var subcategoryName = $(this).data('categorysubname');
        var issubStatus = $(this).data('status');

        $('#editCategorySubId').val(id);
        $('#editCatName').val(cateName);
        $('#editCategorySubName').val(subcategoryName);
        $('#editIssubStatus').val(issubStatus);

        $('#editCategorySubModal').modal('show');
    });

    $('#editCategorySubForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: subcategoryUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editCategorySubModal').modal('hide');
                    $(document).trigger('subcategoryAdded');
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

    $(document).on('click', '.category-delete', function(e) {
        var id = $(this).val();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        });
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to recover this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: subcategoryDeleteRoute.replace(':id', id),
                    success: function(response) {
                        $("#tr-" + id).delay(1000).fadeOut();
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Successfully Deleted!',
                            icon: 'warning',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        if(response.success) {
                            toastr.success(response.message);
                            console.log(response);
                        }
                    }
                });
            }
        })
    });
</script>
