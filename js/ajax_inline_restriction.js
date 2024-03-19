$(document).ready(function(e) {
	// ROUNDS
	// PREVENT LINE BREAK
    $(".edit_rd").keydown(function(e) {
        if(e.keyCode == 13 && e.shiftKey) {
			e.preventDefault();
			// alert('Enter + shift pressed');
        } else if(e.keyCode == 13) {
			e.preventDefault();
        } 
    });
    
	// PREVENT PASTE
    $(".edit_rd").on('paste',function(e) {
        e.preventDefault();
    });
    
	// ALLOW SPECIAL CHARS AND PREVENT ALPHANUMERICAL
    $(".edit_rd").keydown(function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
        // Allow: Ctrl/cmd+A
        (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: Ctrl/cmd+C
        (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: Ctrl/cmd+X
        (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        // DON'T DO ANYTHING
			return;
        }
        // ENSURE NUMBER AND PREVENT KEYDOWN
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
			e.preventDefault();
        }
    });
    
	// MAX INPUT LENGHT 2 DIGITS
    $(".edit_rd").keydown(function(e) {
        return $.inArray(e.which, [8, 46, 37, 39]) > -1 || $(this).text().length < 2;
	});
		

	// TIMEBUDDIES
	// PREVENT LINE BREAK
    $(".edit_mz").keydown(function(e) {
        if(e.keyCode == 13 && e.shiftKey) {
			e.preventDefault();
			// alert('Enter + shift pressed');
        } else if(e.keyCode == 13) {
			e.preventDefault();
        } 
    });
    
	// PREVENT PASTE
    $(".edit_mz").on('paste',function(e) {
        e.preventDefault();
    });
    
	// ALLOW SPECIAL CHARS
    $(".edit_mz").keydown(function(e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
        // Allow: Ctrl/cmd+A
        (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: Ctrl/cmd+C
        (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: Ctrl/cmd+X
        (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        // DON'T DO ANYTHING
			return;
        }
    });
});