
jQuery(document).ready(function() {

    /*
        Fullscreen background
    */
    $.backstretch([
										"{{ URL::asset('images/backgrounds/1.jpg') }}",
										"{{ URL::asset('images/backgrounds/2.jpg') }}",
	              		"{{ URL::asset('images/backgrounds/3.jpg') }}"
	             ], {duration: 3000, fade: 750});

    /*
        Form validation
    */
    // $('.login-form input[type="text"], .login-form input[type="password"], .login-form textarea').on('focus', function() {
    // 	$(this).removeClass('input-error');
    // });
    //
    // $('.login-form').on('submit', function(e) {
    //
    // 	$(this).find('input[type="text"], input[type="password"], textarea').each(function(){
    // 		if( $(this).val() == "" ) {
    // 			e.preventDefault();
    // 			$(this).addClass('input-error');
    // 		}
    // 		else {
    // 			$(this).removeClass('input-error');
    // 		}
    // 	});
    //
    // });


});
