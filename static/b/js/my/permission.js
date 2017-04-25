var Permission = {
    index: function () {
        $(document).ready(function () {

        });
    },
    grant: function () {
        $(document).ready(function () {
            $('.actionName').on('click', function () {
                var     $this = $(this),
                        isChecked = $this.is(':checked'),
                        resource = $this.val(),
                        setAll = $('.set-all').val();
                loading = $(this).parents('.panel-body').prev('header').children('span.pull-right');
                if (resource && isChecked === true) {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/permission/add/',
                        data: {
                            currentPart: currentPart,
                            intUserRole: intUserRole,
                            resource: resource
                        },
                        cache: false,
                        dataType: 'json',
                        beforeSend: function () {
                            loading.show();
                        },
                        success: function (rs) {
                            if (rs.st == 1) {
                                setTimeout(function () {
                                    loading.hide();
                                }, '500');
                            } else {
                                setTimeout(function () {
                                    loading.hide();
                                }, '500');

                                bootbox.alert(rs.ms);
                            }
                        }
                    });
                }
                if (resource && isChecked === false) {
                    $.ajax({
                        type: "POST",
                        url: baseurl + '/backend/permission/delete/',
                        cache: false,
                        dataType: 'json',
                        beforeSend: function () {
                            loading.show();
                        },
                        data: {
                            resource: resource,
                            currentPart: currentPart,
                            intUserRole: intUserRole
                        },
                        success: function (rs) {
                            setTimeout(function () {
                                loading.hide();
                            }, '500');
                            if (rs.st == -1) {
                                bootbox.alert(rs.ms);
                            }
                        }
                    });
                }
            });
        });
    }
};