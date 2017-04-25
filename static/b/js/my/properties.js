var Properties = {
    index: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                var id = $(this).attr('rel');
                console.log(id);
                if (!id) {
                    bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh lại trình duyệt và thử lại!');
                    return false;
                }
                bootbox.confirm('Bạn có chắc chắn muốn xóa nhu cầu này không ????', function (e) {
                    if (e) {
                        $.ajax({
                            type: 'POST',
                            cache: false,
                            dataType: 'json',
                            url: baseurl + '/backend/properties/delete',
                            data: {
                                id: id
                            },
                            success: function (rs) {
                                if (rs.st == 1) {
                                    bootbox.alert(rs.ms, function () {
                                        window.location = window.location.href;
                                    });
                                } else {
                                    bootbox.alert(rs.ms);
                                }
                            }
                        });
                    }
                })
            });
        });
    },
    add: function () {
        $(document).ready(function () {
            var parentId = $('select.parent_id').val();
            if (parentId == 0) {
                $('.select-icon').show();
                $('.select-prop').show();
            } else {
                $('.select-icon').hide();
                $('.select-prop').hide();
            }
            $('select.parent_id').on('change', function () {
                var parentId = $(this).val();
                if (parentId == 0) {
                    $('.select-icon').show();
                    $('.select-prop').show();
                } else {
                    $('.select-icon').hide();
                    $('.select-prop').hide();
                }
            })
        })
    }
}