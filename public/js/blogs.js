$(document).ready(function () {
    checkOfflineOnline();

    function checkOfflineOnline() {
        var onlineValue = $('input[name=online]').val();

        if (onlineValue == 1) {
            $("span#blogOnline").trigger("click");
        } else {
            $("span#blogOffline").trigger("click");
        }
    }

    $("span#blogOffline").on("click", function () {
        $("span#blogOnline").removeClass('blogOnlineActive');
        $(this).addClass('blogOfflineActive');
        $('input[name=online]').val(0);
    });

    $("span#blogOnline").on("click", function () {
        $("span#blogOffline").removeClass('blogOfflineActive');
        $(this).addClass('blogOnlineActive');
        $('input[name=online]').val(1);
    });

    $('#categories').on('shown.bs.modal', function() {});




    $("input[name=dateOnline]").datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        autoclose: true,
        todayBtn: true,
        startDate: "2013-02-14 10:00",
        minuteStep: 1
    });
    $("input[name=dateOffline]").datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        autoclose: true,
        todayBtn: true,
        startDate: "2013-02-14 10:00",
        minuteStep: 1
    });
});

$(document).on('change', ':file', function () {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);

    $('input[name=fileUploadFileName]').val(label);
});