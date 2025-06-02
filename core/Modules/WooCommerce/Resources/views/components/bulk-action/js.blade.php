<script>
(function($) {
    $(document).ready(function() {
        $(document).on('click', '#bulk_delete_btn', function(e) {
            e.preventDefault();

            let bulkOption = $('#bulk_option').val();
            let allCheckbox = $('.bulk-checkbox:checked');
            let allIds = [];

            allCheckbox.each(function(index, value) {
                allIds.push($(this).val());
            });

            if (allIds !== '' && bulkOption === 'import') {
                $(this).text('{{ __('Importing...') }}');
                $.ajax({
                    'type': "POST",
                    'url': "{{ $route }}",
                    'data': {
                        _token: "{{ csrf_token() }}",
                        ids: allIds,
                    },
                    success: function(data) {
                        toastr.success(data.msg);

                        setTimeout(() => {
                            location.reload()
                        }, 10000);
                    },
                    error: function (data)
                    {
                        if (data.status == 550)
                            {
                                let errorResponse = data.responseJSON;
                                $.each(errorResponse, function (index, value) {
                                    toastr.error(value);
                                });
                            }
                    }
                });
            }
        });

        $('.all-checkbox').on('change', function(e) {
            e.preventDefault();
            let value = $('.all-checkbox').is(':checked');
            let allChek = $(this).parent().parent().parent().parent().parent().find('.bulk-checkbox');
            //have write code here fr
            if (value == true) {
                allChek.prop('checked', true);
            } else {
                allChek.prop('checked', false);
            }
        });
    });
})(jQuery);
</script>
