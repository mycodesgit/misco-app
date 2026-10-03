<script>
    $(document).ready(function () {

    // Type of Support changed
    $('#support_type').on('change', function () {
        
        let supportType = $(this).val();

        let category = $('#category');
        let subcategory = $('#subcategory');

        // Get the off_id of the selected role
    let offId = $(this).find(':selected').data('off-id');

    // Store off_id in hidden input
    $('#support_type_hidden').val(offId);

        // Reset category
        category.empty()
            .append('<option value="" selected disabled>Loading categories...</option>')
            .prop('disabled', true);

        // Reset subcategory
        subcategory.empty()
            .append('<option value="" selected disabled>Select Subcategory</option>')
            .prop('disabled', true);

        // Destroy Select2 temporarily
        category.trigger('change');
        subcategory.trigger('change');

        if (!supportType) {
            return;
        }

        let url = "{{ route('tickets.categories', ['supportType' => '__supportType__']) }}";
        url = url.replace(
            '__supportType__',
            encodeURIComponent(supportType)
        );
        $.ajax({
            url: url,
            type: 'GET',

            success: function (data) {

                category.empty()
                    .append('<option value="" selected disabled>Select Category</option>');

                if (data.length > 0) {

                    $.each(data, function (index, item) {

                        category.append(
                            $('<option>', {
                                value: item.id,
                                text: item.ticketcatname
                            })
                        );

                    });

                    category.prop('disabled', false);

                } else {

                    category.append(
                        '<option value="" disabled>No categories available</option>'
                    );

                }

                category.trigger('change');
            },

            error: function (xhr) {

                console.error(xhr);

                category.empty()
                    .append('<option value="" selected disabled>Error loading categories</option>')
                    .prop('disabled', true);

                category.trigger('change');
            }
        });
    });


    // Category changed
    $('#category').on('change', function () {

        let categoryId = $(this).val();

        let subcategory = $('#subcategory');

        subcategory.empty()
            .append('<option value="" selected disabled>Loading subcategories...</option>')
            .prop('disabled', true);

        subcategory.trigger('change');

        if (!categoryId) {
            return;
        }

        let url = "{{ route('tickets.subcategories', ['category' => '__category__']) }}";
        url = url.replace(
            '__category__',
            categoryId
        );

        $.ajax({
            url: url,
            type: 'GET',

            success: function (data) {

                subcategory.empty()
                    .append('<option value="" selected disabled>Select Subcategory</option>');

                if (data.length > 0) {

                    $.each(data, function (index, item) {

                        subcategory.append(
                            $('<option>', {
                                value: item.id,
                                text: item.ticketsubcatname
                            })
                        );

                    });

                    subcategory.prop('disabled', false);

                } else {

                    subcategory.append(
                        '<option value="" disabled>No subcategories available</option>'
                    );
                }

                subcategory.trigger('change');
            },

            error: function (xhr) {

                console.error(xhr);

                subcategory.empty()
                    .append('<option value="" selected disabled>Error loading subcategories</option>')
                    .prop('disabled', true);

                subcategory.trigger('change');
            }
        });
    });

});

</script>