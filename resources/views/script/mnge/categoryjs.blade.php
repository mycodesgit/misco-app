<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        $('#adCategory').submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: categoryCreateRoute,
                type: "POST",
                data: formData,
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        console.log(response);
                        $(document).trigger('categoryAdded', [response.data]);
                        $('#adCategory')[0].reset();
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

        $(document).on('categoryAdded', function(event, category) {
            if (category && category.id) {
                // Append the new option and select it automatically
                var newOption = new Option(category.ticketcatname, category.id, true, true);

                // Using ID selector
                $('#categoryDropdown').append(newOption).trigger('change');

                // OR if targeting by name attribute across multiple dropdowns:
                // $('select[name="cat_id"]').append(newOption);
            }
        });

        var dataTable = $('#categoryTable').DataTable({
            "ajax": {
                "url": categoryReadRoute,
                "type": "GET",
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'ticketcatname'},
                {data: 'cattype'},
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
                            var buttons = '<button type="button" class="btn btn-sm btn-success btn-categoryedit mr-1 text-light" data-id="' + row.id + '" data-categoryname="' + row.ticketcatname + '" data-cattype="' + row.cattype + '" data-status="' + row.status + '" data-toggle="tooltip" data-placement="top" title="Edit Category.">';
                            buttons += '<i class="ti ti-pencil"></i> </button>' +'&nbsp;';
                            buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger category-delete" data-toggle="tooltip" data-placement="top" title="Delete Category."><i class="ti ti-trash"></i> </button>';
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
        $(document).on('categoryAdded', function() {
            dataTable.ajax.reload();
        });

        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    $(document).on('click', '.btn-categoryedit', function() {
        var id = $(this).data('id');
        var categoryName = $(this).data('categoryname');
        var categoryType = $(this).data('cattype');
        var isStatus = $(this).data('status');

        if (typeof categoryType === 'string') {
            try {
                categoryType = JSON.parse(categoryType); // Handles '["Requester","IT Support"]'
            } catch (e) {
                categoryType = categoryType.split(','); // Handles "Requester,IT Support"
            }
        }

        $('#editCategoryId').val(id);
        $('#editCategoryName').val(categoryName);
        $('#editCategoryType').val(categoryType);
        $('#editIsStatus').val(isStatus);

        $('#editCategoryType').val(categoryType).trigger('change');

        $('#editCategoryModal').modal('show');
    });

    $('#editCategoryForm').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: categoryUpdateRoute,
            type: "POST",
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $('#editCategoryModal').modal('hide');
                    $(document).trigger('categoryAdded');
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
                    url: categoryDeleteRoute.replace(':id', id),
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
