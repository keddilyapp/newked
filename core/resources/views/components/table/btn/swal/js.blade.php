<script>
(function ($) {
    "use strict"
    $(document).ready(function () {
        $(document).on('click', '.swal-delete', function () {
            Swal.fire({
                title: "{{ __('Do you want to delete this item?') }}",
                text: '{{__("You would not be able to revert this item!")}}',
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: '{{__('Delete')}}',
                confirmButtonColor: '#dd3333',
                cancelButtonText: "{{__('Cancel')}}",
            }).then((result) => {
                if (result.isConfirmed) {
                    let route = $(this).data('route');

                    $.post(route, {_token: '{{ csrf_token() }}'}).then(function (data) {
                        if (data.success) {
                            Swal.fire('Deleted!', '', 'success');
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    }).catch(function(error) {
                        Swal.fire('Error!', 'An error occurred while deleting the item.', 'error');
                    });
                }
            });
        });
    });
})(jQuery)
</script>
