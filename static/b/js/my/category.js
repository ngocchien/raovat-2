var Category = {
    index: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                var categoryId = $(this).attr('rel');
                if (!categoryId) {
                    bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh lại trình duyệt và thử lại!');
                    return false;
                }
                bootbox.confirm('Bạn có chắc chắn muốn xóa danh mục này không ????', function (e) {
                    if (e) {
                        $.ajax({
                            type: 'POST',
                            cache: false,
                            dataType: 'json',
                            url: baseurl + '/backend/category/delete',
                            data: {
                                categoryId: categoryId
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
            console.log(parentId);
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
    },
    edit: function () {
        $(document).ready(function () {
            var parentId = $('input.parent').val();
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