$(document).ready(function() {
	$('.rid_1').on('change',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_1_1').html(html);
					$('.rid_pos_1_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_1_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_1_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_1_5').html('<option value="">1. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_1_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_1_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_1_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_1_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_1_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$('.rid_pos_1_1').on('change',function(){
		var ridID = $('.rid_1').val();
		var rid_pos_1_1ID = $(this).val();
		if(rid_pos_1_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_1_2').html(html);
					$('.rid_pos_1_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_1_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_1_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_1_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_1_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_1_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_1_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$('.rid_pos_1_2').on('change',function(){
		var ridID = $('.rid_1').val();
		var rid_pos_1_2ID = $(this).val();
		if(rid_pos_1_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_1_3').html(html);
					$('.rid_pos_1_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_1_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_1_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_1_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_1_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$('.rid_pos_1_3').on('change',function(){
		var ridID = $('.rid_1').val();
		var rid_pos_1_3ID = $(this).val();
		if(rid_pos_1_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_1_4').html(html);
					$('.rid_pos_1_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_1_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_1_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$('.rid_pos_1_4').on('change',function(){
		var ridID = $('.rid_1').val();
		var rid_pos_1_4ID = $(this).val();
		if(rid_pos_1_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_1_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_1_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 2
	$(document).on('change','.rid_2',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_2_1').html(html);
					$('.rid_pos_2_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_2_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_2_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_2_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_2_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_2_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_2_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_2_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_2_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_2_1',function(){
		var ridID = $('.rid_2').val();
		var rid_pos_2_1ID = $(this).val();
		if(rid_pos_2_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_2_2').html(html);
					$('.rid_pos_2_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_2_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_2_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_2_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_2_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_2_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_2_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_2_2',function(){
		var ridID = $('.rid_2').val();
		var rid_pos_2_2ID = $(this).val();
		if(rid_pos_2_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_2_3').html(html);
					$('.rid_pos_2_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_2_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_2_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_2_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_2_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_2_3',function(){
		var ridID = $('.rid_2').val();
		var rid_pos_2_3ID = $(this).val();
		if(rid_pos_2_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_2_4').html(html);
					$('.rid_pos_2_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_2_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_2_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_2_4',function(){
		var ridID = $('.rid_2').val();
		var rid_pos_2_4ID = $(this).val();
		if(rid_pos_2_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_2_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_2_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 3
	$(document).on('change','.rid_3',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_3_1').html(html);
					$('.rid_pos_3_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_3_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_3_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_3_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_3_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_3_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_3_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_3_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_3_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_3_1',function(){
		var ridID = $('.rid_3').val();
		var rid_pos_3_1ID = $(this).val();
		if(rid_pos_3_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_3_2').html(html);
					$('.rid_pos_3_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_3_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_3_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_3_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_3_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_3_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_3_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_3_2',function(){
		var ridID = $('.rid_3').val();
		var rid_pos_3_2ID = $(this).val();
		if(rid_pos_3_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_3_3').html(html);
					$('.rid_pos_3_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_3_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_3_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_3_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_3_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_3_3',function(){
		var ridID = $('.rid_3').val();
		var rid_pos_3_3ID = $(this).val();
		if(rid_pos_3_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_3_4').html(html);
					$('.rid_pos_3_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_3_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_3_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_3_4',function(){
		var ridID = $('.rid_3').val();
		var rid_pos_3_4ID = $(this).val();
		if(rid_pos_3_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_3_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_3_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 4
	$(document).on('change','.rid_4',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_4_1').html(html);
					$('.rid_pos_4_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_4_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_4_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_4_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_4_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_4_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_4_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_4_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_4_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_4_1',function(){
		var ridID = $('.rid_4').val();
		var rid_pos_4_1ID = $(this).val();
		if(rid_pos_4_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_4_2').html(html);
					$('.rid_pos_4_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_4_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_4_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_4_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_4_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_4_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_4_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_4_2',function(){
		var ridID = $('.rid_4').val();
		var rid_pos_4_2ID = $(this).val();
		if(rid_pos_4_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_4_3').html(html);
					$('.rid_pos_4_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_4_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_4_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_4_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_4_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_4_3',function(){
		var ridID = $('.rid_4').val();
		var rid_pos_4_3ID = $(this).val();
		if(rid_pos_4_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_4_4').html(html);
					$('.rid_pos_4_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_4_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_4_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_4_4',function(){
		var ridID = $('.rid_4').val();
		var rid_pos_4_4ID = $(this).val();
		if(rid_pos_4_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_4_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_4_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 5
	$(document).on('change','.rid_5',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_5_1').html(html);
					$('.rid_pos_5_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_5_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_5_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_5_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_5_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_5_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_5_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_5_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_5_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_5_1',function(){
		var ridID = $('.rid_5').val();
		var rid_pos_5_1ID = $(this).val();
		if(rid_pos_5_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_5_2').html(html);
					$('.rid_pos_5_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_5_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_5_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_5_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_5_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_5_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_5_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_5_2',function(){
		var ridID = $('.rid_5').val();
		var rid_pos_5_2ID = $(this).val();
		if(rid_pos_5_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_5_3').html(html);
					$('.rid_pos_5_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_5_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_5_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_5_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_5_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_5_3',function(){
		var ridID = $('.rid_5').val();
		var rid_pos_5_3ID = $(this).val();
		if(rid_pos_5_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_5_4').html(html);
					$('.rid_pos_5_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_5_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_5_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_5_4',function(){
		var ridID = $('.rid_5').val();
		var rid_pos_5_4ID = $(this).val();
		if(rid_pos_5_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_5_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_5_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 6
	$(document).on('change','.rid_6',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_6_1').html(html);
					$('.rid_pos_6_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_6_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_6_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_6_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_6_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_6_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_6_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_6_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_6_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_6_1',function(){
		var ridID = $('.rid_6').val();
		var rid_pos_6_1ID = $(this).val();
		if(rid_pos_6_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_6_2').html(html);
					$('.rid_pos_6_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_6_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_6_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_6_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_6_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_6_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_6_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_6_2',function(){
		var ridID = $('.rid_6').val();
		var rid_pos_6_2ID = $(this).val();
		if(rid_pos_6_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_6_3').html(html);
					$('.rid_pos_6_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_6_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_6_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_6_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_6_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_6_3',function(){
		var ridID = $('.rid_6').val();
		var rid_pos_6_3ID = $(this).val();
		if(rid_pos_6_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_6_4').html(html);
					$('.rid_pos_6_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_6_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_6_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_6_4',function(){
		var ridID = $('.rid_6').val();
		var rid_pos_6_4ID = $(this).val();
		if(rid_pos_6_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_6_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_6_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 7
	$(document).on('change','.rid_7',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_7_1').html(html);
					$('.rid_pos_7_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_7_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_7_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_7_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_7_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_7_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_7_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_7_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_7_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_7_1',function(){
		var ridID = $('.rid_7').val();
		var rid_pos_7_1ID = $(this).val();
		if(rid_pos_7_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_7_2').html(html);
					$('.rid_pos_7_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_7_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_7_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_7_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_7_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_7_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_7_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_7_2',function(){
		var ridID = $('.rid_7').val();
		var rid_pos_7_2ID = $(this).val();
		if(rid_pos_7_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_7_3').html(html);
					$('.rid_pos_7_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_7_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_7_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_7_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_7_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_7_3',function(){
		var ridID = $('.rid_7').val();
		var rid_pos_7_3ID = $(this).val();
		if(rid_pos_7_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_7_4').html(html);
					$('.rid_pos_7_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_7_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_7_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_7_4',function(){
		var ridID = $('.rid_7').val();
		var rid_pos_7_4ID = $(this).val();
		if(rid_pos_7_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_7_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_7_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 8
	$(document).on('change','.rid_8',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_8_1').html(html);
					$('.rid_pos_8_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_8_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_8_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_8_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_8_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_8_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_8_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_8_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_8_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_8_1',function(){
		var ridID = $('.rid_8').val();
		var rid_pos_8_1ID = $(this).val();
		if(rid_pos_8_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_8_2').html(html);
					$('.rid_pos_8_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_8_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_8_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_8_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_8_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_8_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_8_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_8_2',function(){
		var ridID = $('.rid_8').val();
		var rid_pos_8_2ID = $(this).val();
		if(rid_pos_8_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_8_3').html(html);
					$('.rid_pos_8_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_8_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_8_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_8_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_8_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_8_3',function(){
		var ridID = $('.rid_8').val();
		var rid_pos_8_3ID = $(this).val();
		if(rid_pos_8_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_8_4').html(html);
					$('.rid_pos_8_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_8_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_8_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_8_4',function(){
		var ridID = $('.rid_8').val();
		var rid_pos_8_4ID = $(this).val();
		if(rid_pos_8_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_8_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_8_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 9
	$(document).on('change','.rid_9',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_9_1').html(html);
					$('.rid_pos_9_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_9_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_9_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_9_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_9_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_9_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_9_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_9_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_9_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_9_1',function(){
		var ridID = $('.rid_9').val();
		var rid_pos_9_1ID = $(this).val();
		if(rid_pos_9_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_9_2').html(html);
					$('.rid_pos_9_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_9_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_9_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_9_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_9_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_9_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_9_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_9_2',function(){
		var ridID = $('.rid_9').val();
		var rid_pos_9_2ID = $(this).val();
		if(rid_pos_9_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_9_3').html(html);
					$('.rid_pos_9_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_9_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_9_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_9_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_9_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_9_3',function(){
		var ridID = $('.rid_9').val();
		var rid_pos_9_3ID = $(this).val();
		if(rid_pos_9_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_9_4').html(html);
					$('.rid_pos_9_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_9_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_9_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_9_4',function(){
		var ridID = $('.rid_9').val();
		var rid_pos_9_4ID = $(this).val();
		if(rid_pos_9_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_9_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_9_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 10
	$(document).on('change','.rid_10',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_10_1').html(html);
					$('.rid_pos_10_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_10_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_10_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_10_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_10_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_10_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_10_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_10_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_10_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_10_1',function(){
		var ridID = $('.rid_10').val();
		var rid_pos_10_1ID = $(this).val();
		if(rid_pos_10_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_10_2').html(html);
					$('.rid_pos_10_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_10_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_10_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_10_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_10_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_10_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_10_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_10_2',function(){
		var ridID = $('.rid_10').val();
		var rid_pos_10_2ID = $(this).val();
		if(rid_pos_10_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_10_3').html(html);
					$('.rid_pos_10_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_10_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_10_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_10_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_10_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_10_3',function(){
		var ridID = $('.rid_10').val();
		var rid_pos_10_3ID = $(this).val();
		if(rid_pos_10_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_10_4').html(html);
					$('.rid_pos_10_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_10_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_10_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_10_4',function(){
		var ridID = $('.rid_10').val();
		var rid_pos_10_4ID = $(this).val();
		if(rid_pos_10_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_10_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_10_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 11
	$(document).on('change','.rid_11',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_11_1').html(html);
					$('.rid_pos_11_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_11_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_11_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_11_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_11_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_11_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_11_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_11_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_11_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_11_1',function(){
		var ridID = $('.rid_11').val();
		var rid_pos_11_1ID = $(this).val();
		if(rid_pos_11_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_11_2').html(html);
					$('.rid_pos_11_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_11_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_11_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_11_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_11_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_11_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_11_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_11_2',function(){
		var ridID = $('.rid_11').val();
		var rid_pos_11_2ID = $(this).val();
		if(rid_pos_11_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_11_3').html(html);
					$('.rid_pos_11_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_11_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_11_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_11_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_11_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_11_3',function(){
		var ridID = $('.rid_11').val();
		var rid_pos_11_3ID = $(this).val();
		if(rid_pos_11_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_11_4').html(html);
					$('.rid_pos_11_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_11_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_11_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_11_4',function(){
		var ridID = $('.rid_11').val();
		var rid_pos_11_4ID = $(this).val();
		if(rid_pos_11_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_11_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_11_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 12
	$(document).on('change','.rid_12',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_12_1').html(html);
					$('.rid_pos_12_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_12_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_12_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_12_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_12_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_12_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_12_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_12_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_12_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_12_1',function(){
		var ridID = $('.rid_12').val();
		var rid_pos_12_1ID = $(this).val();
		if(rid_pos_12_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_12_2').html(html);
					$('.rid_pos_12_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_12_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_12_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_12_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_12_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_12_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_12_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_12_2',function(){
		var ridID = $('.rid_12').val();
		var rid_pos_12_2ID = $(this).val();
		if(rid_pos_12_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_12_3').html(html);
					$('.rid_pos_12_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_12_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_12_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_12_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_12_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_12_3',function(){
		var ridID = $('.rid_12').val();
		var rid_pos_12_3ID = $(this).val();
		if(rid_pos_12_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_12_4').html(html);
					$('.rid_pos_12_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_12_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_12_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_12_4',function(){
		var ridID = $('.rid_12').val();
		var rid_pos_12_4ID = $(this).val();
		if(rid_pos_12_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_12_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_12_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 13
	$(document).on('change','.rid_13',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_13_1').html(html);
					$('.rid_pos_13_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_13_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_13_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_13_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_13_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_13_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_13_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_13_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_13_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_13_1',function(){
		var ridID = $('.rid_13').val();
		var rid_pos_13_1ID = $(this).val();
		if(rid_pos_13_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_13_2').html(html);
					$('.rid_pos_13_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_13_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_13_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_13_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_13_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_13_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_13_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_13_2',function(){
		var ridID = $('.rid_13').val();
		var rid_pos_13_2ID = $(this).val();
		if(rid_pos_13_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_13_3').html(html);
					$('.rid_pos_13_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_13_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_13_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_13_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_13_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_13_3',function(){
		var ridID = $('.rid_13').val();
		var rid_pos_13_3ID = $(this).val();
		if(rid_pos_13_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_13_4').html(html);
					$('.rid_pos_13_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_13_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_13_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_13_4',function(){
		var ridID = $('.rid_13').val();
		var rid_pos_13_4ID = $(this).val();
		if(rid_pos_13_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_13_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_13_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 14
	$(document).on('change','.rid_14',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_14_1').html(html);
					$('.rid_pos_14_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_14_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_14_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_14_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_14_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_14_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_14_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_14_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_14_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_14_1',function(){
		var ridID = $('.rid_14').val();
		var rid_pos_14_1ID = $(this).val();
		if(rid_pos_14_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_14_2').html(html);
					$('.rid_pos_14_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_14_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_14_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_14_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_14_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_14_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_14_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_14_2',function(){
		var ridID = $('.rid_14').val();
		var rid_pos_14_2ID = $(this).val();
		if(rid_pos_14_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_14_3').html(html);
					$('.rid_pos_14_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_14_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_14_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_14_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_14_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_14_3',function(){
		var ridID = $('.rid_14').val();
		var rid_pos_14_3ID = $(this).val();
		if(rid_pos_14_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_14_4').html(html);
					$('.rid_pos_14_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_14_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_14_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_14_4',function(){
		var ridID = $('.rid_14').val();
		var rid_pos_14_4ID = $(this).val();
		if(rid_pos_14_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_14_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_14_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
		
	// FIELDSET 15
	$(document).on('change','.rid_15',function(){
		var ridID = $(this).val();
		if(ridID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_15_1').html(html);
					$('.rid_pos_15_2').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_15_3').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_15_4').html('<option value="">1. Pos. wählen</option>');
					$('.rid_pos_15_5').html('<option value="">1. Pos. wählen</option>');
				}
			});
		}else{
			$('.rid_pos_15_1').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_15_2').html('<option value="">Prüfung wählen</option>'); 
			$('.rid_pos_15_3').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_15_4').html('<option value="">Prüfung wählen</option>');
			$('.rid_pos_15_5').html('<option value="">Prüfung wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_15_1',function(){
		var ridID = $('.rid_15').val();
		var rid_pos_15_1ID = $(this).val();
		if(rid_pos_15_1ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_15_2').html(html);
					$('.rid_pos_15_3').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_15_4').html('<option value="">2. Pos. wählen</option>');
					$('.rid_pos_15_5').html('<option value="">2. Pos. wählen</option>'); 
				}
			}); 
		}else{
			$('.rid_pos_15_2').html('<option value="">1. Pos. wählen</option>'); 
			$('.rid_pos_15_3').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_15_4').html('<option value="">1. Pos. wählen</option>');
			$('.rid_pos_15_5').html('<option value="">1. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_15_2',function(){
		var ridID = $('.rid_15').val();
		var rid_pos_15_2ID = $(this).val();
		if(rid_pos_15_2ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_15_3').html(html);
					$('.rid_pos_15_4').html('<option value="">3. Pos. wählen</option>');
					$('.rid_pos_15_5').html('<option value="">3. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_15_3').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_15_4').html('<option value="">2. Pos. wählen</option>');
			$('.rid_pos_15_5').html('<option value="">2. Pos. wählen</option>');
		}
	});

	$(document).on('change','.rid_pos_15_3',function(){
		var ridID = $('.rid_15').val();
		var rid_pos_15_3ID = $(this).val();
		if(rid_pos_15_3ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_15_4').html(html);
					$('.rid_pos_15_5').html('<option value="">4. Pos. wählen</option>');
				}
			}); 
		}else{
			$('.rid_pos_15_4').html('<option value="">3. Pos. wählen</option>');
			$('.rid_pos_15_5').html('<option value="">3. Pos. wählen</option>'); 
		}
	});

	$(document).on('change','.rid_pos_15_4',function(){
		var ridID = $('.rid_15').val();
		var rid_pos_15_4ID = $(this).val();
		if(rid_pos_15_4ID){
			$.ajax({
				type: 'POST',
				url: 'addmz_live_dep_select.php',
				data: 'rid='+ridID,
				success: function(html){
					$('.rid_pos_15_5').html(html);
				}
			}); 
		}else{
			$('.rid_pos_15_5').html('<option value="">4. Pos. wählen</option>'); 
		}
	});
});