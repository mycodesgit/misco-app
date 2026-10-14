<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right"
    };
    $(document).ready(function() {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        // Upload CSV (school year + semester picked first, rows default to Available)
        $('#uploadVouchers').submit(function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            var $btn = $('#uploadVouchersBtn').prop('disabled', true);

            $.ajax({
                url: wifiUploadRoute,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        $(document).trigger('wifiChanged');
                        $('#uploadVouchers')[0].reset();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    var errorMessage = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred';
                    toastr.error(errorMessage);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        var dataTable = $('#wifiTable').DataTable({
            "ajax": {
                "url": wifiReadRoute,
                "type": "GET",
                "data": function(d) {
                    d.schlyear = $('#filterSchlyear').val();
                    d.semester = $('#filterSemester').val();
                    d.status = $('#filterStatus').val();
                },
                "dataSrc": function(json) {
                    var rows = json.data || [];
                    var available = rows.filter(function(r) { return parseInt(r.status) === 1; }).length;
                    $('#wifi-available-count').text(available);
                    $('#wifi-used-count').text(rows.length - available);
                    return rows;
                }
            },
            destroy: true,
            info: true,
            responsive: true,
            lengthChange: true,
            searching: true,
            paging: true,
            "columns": [
                {data: 'voucher_code'},
                {data: 'schlyear'},
                {data: 'semester'},
                {
                    data: 'status',
                    render: function(data) {
                        if (parseInt(data) === 1) {
                            return '<span class="badge bg-success-subtle text-success">Available</span>';
                        }
                        return '<span class="badge bg-secondary-subtle text-secondary">Used</span>';
                    },
                },
                {
                    data: 'id',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            var toggleLabel = parseInt(row.status) === 1 ? 'Mark as Used.' : 'Mark as Available.';
                            var toggleIcon = parseInt(row.status) === 1 ? 'ti-circle-check' : 'ti-rotate';
                            var buttons = '<button type="button" class="btn btn-sm btn-success wifi-toggle mr-1 text-light" data-id="' + row.id + '" data-status="' + (parseInt(row.status) === 1 ? 0 : 1) + '" data-toggle="tooltip" data-placement="top" title="' + toggleLabel + '">';
                            buttons += '<i class="ti ' + toggleIcon + '"></i> </button>' + '&nbsp;';
                            buttons += '<button type="button" value="' + data + '" class="btn btn-sm btn-danger wifi-delete" data-toggle="tooltip" data-placement="top" title="Delete Voucher."><i class="ti ti-trash"></i> </button>';

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

        $('#filterSchlyear, #filterSemester, #filterStatus').on('change', function() {
            dataTable.ajax.reload();
        });

        $(document).on('wifiChanged', function() {
            dataTable.ajax.reload();
        });
        dataTable.on('draw', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });

    // Toggle Available / Used
    $(document).on('click', '.wifi-toggle', function() {
        var id = $(this).data('id');
        var status = $(this).data('status');

        $.ajax({
            url: wifiStatusRoute,
            type: "POST",
            data: { id: id, status: status },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                    $(document).trigger('wifiChanged');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                var errorMessage = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred';
                toastr.error(errorMessage);
            }
        });
    });

    // Delete voucher (SweetAlert confirmation)
    $(document).on('click', '.wifi-delete', function() {
        var id = $(this).val();
        var url = wifiDeleteRoute.replace(':id', id);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to recover this voucher code!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.message,
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $(document).trigger('wifiChanged');
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred';
                        toastr.error(errorMessage);
                    }
                });
            }
        });
    });
</script>
