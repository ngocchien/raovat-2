var General = {
    index: function () {
        $(document).ready(function () {
            $('.remove').on('click', function () {
                var id = $(this).attr('rel');
                if (id) {
                    bootbox.confirm('Bạn có chắc chắn muốn xóa General này không???', function (e) {
                        if (e) {
                            $.ajax({
                                type: 'POST',
                                url: baseurl + '/backend/general-be/delete',
                                dataType: 'json',
                                cache: false,
                                data: {
                                    id: id
                                },
                                success: function (rs) {
                                    if (rs.st == 1) {
                                        bootbox.alert(rs.ms, function () {
                                            window.location.href = window.location.href;
                                        });
                                    } else {
                                        bootbox.alert(rs.ms);
                                        return false;
                                    }
                                }
                            });
                        }
                    })
                }
            })
        })
    },
    add: function () {
//        $(document).ready(function () {
////            $('.wysihtml5').wysihtml5({
////                "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
////                "emphasis": true, //Italics, bold, etc. Default true
////                "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
////                "color": true //Button to change color of font  
////            });
//        })
    },
}