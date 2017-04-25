var User = {
    index: function () {
        $(document).ready(function () {
            $('.remove').on('click', function () {
                var userId = $(this).attr('rel');
                if (!userId) {
                    bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh trình duyệt và thử lại!');
                    return false;
                }

                bootbox.confirm('<b>Bạn có chắc chắn muốn xóa người dùng này không ???</b>', function (e) {
                    if (e) {
                        $.ajax({
                            type: 'POST',
                            cache: false,
                            dataType: 'json',
                            url: baseurl + '/backend/user/delete',
                            data: {
                                userId: userId
                            },
                            success: function (rs) {
                                if (rs.st == 1) {
                                    bootbox.alert(rs.ms, function () {
                                        window.location.href = window.location.href;
                                    })
                                } else {
                                    bootbox.alert(rs.ms, function () {
                                        return false;
                                    });
                                }
                            }
                        })
                    }
                });

            })
        });
        User.add();
    },
    add: function () {
        $(document).ready(function () {
            $('#city').on('change', function () {
                var cityId = $(this).val();
                if (!cityId) {
                    bootbox.alert('Xảy ra lỗi, vui lòng refresh lại trình duyệt!');
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: baseurl + '/backend/district/get-list',
                    data: {
                        cityId: cityId
                    },
                    success: function (rs) {
                        if (rs.st == 1) {
                            var html = '<option value="0"> Vui lòng chọn Quận / Huyện</option>';
                            $.each(rs.data, function (e, v) {
                                html += '<option value = "' + v.dist_id + '">' + v.dist_name + '</option>';
                            })
                            $('#district').html(html);
                        } else {
                            bootbox.alert(rs.ms);
                            return false;
                        }
                    }

                })
            })
        })
    },
}