function checkOfflineOnline() {
    var onlineValue = $('input[name=online]').val();

    if (onlineValue == 1) {
        $("span#blogOnline").trigger("click");
    } else {
        $("span#blogOffline").trigger("click");
    }
}

$(document).ready(function () {
    checkOfflineOnline();



    $("span#blogOffline").on("click", function () {
        $("span#blogOnline").children('i').removeClass('text-success');
        $(this).children('i').addClass('text-danger');
        $('input[name=online]').val(0);
    });

    $("span#blogOnline").on("click", function () {
        $("#blogOffline").children('i').removeClass('text-danger');
        $(this).children('i').addClass('text-success');
        $('input[name=online]').val(1);
    });

    $('#categories').on('shown.bs.modal', function() {});

    /**
     * Init timeshift
     */
    $("#timeOnline, #timeOffline").timeshift({
        hourClock: 24
    });

    /**
     * Init dateshift
     */
    $(".dateOnline, .dateOffline").dateshift({
        preappelement: '<i class="far fa-calendar-alt"></i>',
        preapp: 'app',
        nextButtonText: '<i class="far fa-caret-square-right"></i>',
        previousButtonText: '<i class="far fa-caret-square-left"></i>',
        dateFormat: 'dd-mm-yyyy'
    });

});

$(document).on('change', ':file', function () {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);

    $('input[name=fileUploadFileName]').val(label);
});