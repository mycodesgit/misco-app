<script>
    $(document).ready(function () {
        var ticketStatusPreviewRoute = "{{ route('ticketstatus-report.preview') }}";

        $('#ticketStatusForm').on('submit', function (event) {
            event.preventDefault();

            var month = $('#tsFilterMonth').val();
            var year = $('#tsFilterYear').val();
            var offId = $('#tsFilterOffice').val();
            var status = $('#tsFilterStatus').val();

            if (!month || !year) {
                toastr.warning('Please select a month and year.');
                return;
            }

            var params = $.param({
                month: month,
                year: year,
                off_id: offId || '',
                status: status || ''
            });

            var pdfUrl = ticketStatusPreviewRoute + '?' + params;

            $('#tsNoReportIcon').hide();
            $('#tsPdfContainer').show();
            $('#tsPdfIframe').attr('src', pdfUrl);

            $('#tsOpenPdfBtn').prop('disabled', false).data('url', pdfUrl);
        });

        $('#tsOpenPdfBtn').on('click', function () {
            var url = $(this).data('url');
            if (url) {
                window.open(url, '_blank');
            }
        });
    });
</script>
