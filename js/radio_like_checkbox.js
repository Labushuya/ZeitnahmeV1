$(".chb").change(function() {
    var checked = $(this).is(':checked');
    $(".chb").prop('checked', false);
    if(checked) {
        $(this).prop('checked', true);
    }
});