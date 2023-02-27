/** Check if Blog is on- or offline and activate right switch */
function checkOfflineOnline() {
    let onlineValue = $('input[name=online]').val();
    if (onlineValue == 1) {
        $("span#blogOnline").trigger("click");
    } else {
        $("span#blogOffline").trigger("click");
    }
}

/** Load all javascript/jQuery stuff on page ready */
$(document).ready(function () {
    /** Initialise offline/online check */
    checkOfflineOnline();

    /** Set blog offline when clicking on button */
    $("span#blogOffline").on("click", function () {
        $("span#blogOnline").children('i').removeClass('text-success');
        $(this).children('i').addClass('text-danger');
        $('input[name=online]').val(0);
    });

    /** Set blog online when clicking on button */
    $("span#blogOnline").on("click", function () {
        $("#blogOffline").children('i').removeClass('text-danger');
        $(this).children('i').addClass('text-success');
        $('input[name=online]').val(1);
    });

    /** Init timeshift */
    $("#timeOnline, #timeOffline").timeshift({
        hourClock: 24
    });

    /** Init dateshift */
    $(".dateOnline, .dateOffline").dateshift({
        preappelement: '<i class="far fa-calendar-alt"></i>',
        preapp: 'app',
        nextButtonText: '<i class="far fa-caret-square-right"></i>',
        previousButtonText: '<i class="far fa-caret-square-left"></i>',
        dateFormat: 'dd-mm-yyyy'
    });




    $( "form#uploadfileform" ).submit(function( event ) {
        event.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            method:"POST",
            url: "/uploadfilesajax",
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            beforeSend:function(){
                $('button[type="submit"]').attr('disabled','disabled');
            },
            success: function(data){

                $('button[type="submit"]').removeAttr('disabled');

                $('#alertBox').html(data).fadeIn();
            }

        });
    });

});

/** Init file upload layout */
$(document).on('change', ':file', function () {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);

    $('input[name=fileUploadFileName]').val(label);
});

