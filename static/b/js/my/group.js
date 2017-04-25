var Group = {
    index: function () {
        $(document).ready(function () {
            $('.remove').on('click', function () {
                var groupId = $(this).attr('rel');
                if (!groupId) {
                    bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh trình duyệt và thử lại!');
                    return false;
                }

                bootbox.confirm('<b>Bạn có chắc chắn muốn xóa người dùng này không ???</b>', function (e) {
                    if (e) {
                        $.ajax({
                            type: 'POST',
                            cache: false,
                            dataType: 'json',
                            url: baseurl + '/backend/group/delete',
                            data: {
                                groupId: groupId
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
    },
}