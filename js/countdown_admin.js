(function($) {

    $(document).ready(function() {
        updateFields();

        $(document).ajaxStop(function(){
            //updateFields();
        });
    });

    $(document).ajaxComplete(function() {
        console.log('ajax complete');

        $('.cd_date_class').datetimepicker();
        $('.color-picker').wpColorPicker();

        //updateFields();
    });

    function updateFields(){
        $('.cd_date_class').each(function(){
            $(this).datetimepicker();
        });
        $('.color-picker').each(function(){
            $(this).wpColorPicker();
        });
    }

})( jQuery );