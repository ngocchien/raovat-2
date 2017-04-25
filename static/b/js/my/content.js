var Content = {
    index: function () {
        $(document).ready(function () {
            $('.remove').click(function () {
                var cont_id = $(this).attr('rel');
                if (!cont_id) {
                    bootbox.alert('Xảy ra lỗi trong quá trình xử lý! Vui lòng refresh lại trình duyệt và thử lại!');
                    return false;
                }
                bootbox.confirm('Bạn có chắc chắn muốn xóa tin rao vặt này không???', function (e) {
                    if (e) {
                        $.ajax({
                            type: 'POST',
                            cache: false,
                            dataType: 'json',
                            url: baseurl + '/backend/content/delete',
                            data: {
                                cont_id: cont_id
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

            $('.up-vip').on('click', function () {
                $('input[name=cont_id]').val($(this).attr('rel'));
                $('#ModalUpVip').modal('show');
            });

            $('button[name=deal-vip]').on('click', function () {
                var cont_id = $('input[name=cont_id]').val();
                var num_date = $('input[name=numdate]').val();
                var type_vip = $('input[name=typevip]:checked').val();
                if (!num_date || num_date < 1) {
                    bootbox.alert('<b class="color-red">Bạn nhập số ngày chưa hợp lệ</b>');
                    return false;
                }
                $.ajax({
                    type: 'POST',
                    url: baseurl + '/backend/content/upvip',
                    dataType: 'json',
                    cache: false,
                    data: {
                        cont_id: cont_id,
                        type_vip: type_vip,
                        num_date: num_date
                    },
                    success: function (rs) {
                        $('#ModalUpVip').modal('hide');
                        if (rs.st == 1) {

                            bootbox.alert(rs.ms, function () {
                                window.location.href = window.location.href;
                            });
                        } else {
                            bootbox.alert(rs.ms);
                        }
                    }
                });
            })

            Content.add();
        });
    },
    add: function () {
        $(document).ready(function () {
            $('.wysihtml5').wysihtml5();
        })
    },
}