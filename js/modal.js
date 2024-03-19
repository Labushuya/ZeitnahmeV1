// LOAD AS SOON AS DOCUMENT IS FULLY LOADED
$(document).ready(function() {	
	// MODAL MY EVENT TIMEBUDDIES
	$(function() {
		$("#dialog_mt").dialog({
			width: 640,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_mt").on("click", function() {
			$("#dialog_mt").dialog("open");
		});
	});

	// MODAL MY EVENT TIMEBUDDY
	$(function() {
		$("#dialog_mz").dialog({
			width: 640,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_mz").on("click", function() {
			$("#dialog_mz").dialog("open");
		});
	});
	
	// MODAL MY EVENT STAMPCONTROL
	$(function() {
		$("#dialog_sc").dialog({
			width: 640,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_sc").on("click", function() {
			$("#dialog_sc").dialog("open");
		});
	});
	
	// MODAL MY EVENT TIMECONTROL
	$(function() {
		$("#dialog_tc").dialog({
			width: 640,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_tc").on("click", function() {
			$("#dialog_tc").dialog("open");
		});
	});
	
	// MODAL MY EVENT BOARDINGCONTROL
	$(function() {
		$("#dialog_bc").dialog({
			width: 640,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_bc").on("click", function() {
			$("#dialog_bc").dialog("open");
		});
	});

	// MODAL MY EVENT ROUNDS
	$(function() {
		$("#dialog_rd").dialog({
			width: 640,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_rd").on("click", function() {
			$("#dialog_rd").dialog("open");
		});
	});
	
	// MODAL MY EVENT TIME RESULTS
	$(function() {
		$("#dialog_res").dialog({
			width: 1024,
			height: 450,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_res").on("click", function() {
			$("#dialog_res").dialog("open");
		});
	});

	// MODAL UPLOAD EXCEL PICTURE
	$(function() {
		$("#dialog_mt_pic").dialog({
			width: 640,
			height: 275,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_mt_pic").on("click", function() {
			$("#dialog_mt_pic").dialog("open");
		});
	});
	
	$(function() {
		$("#dialog_mt_pic_dummy").dialog({
			width: 640,
			height: 350,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_mt_pic_dummy").on("click", function() {
			$("#dialog_mt_pic_dummy").dialog("open");
		});
	});

	// MODAL TUTORIAL: ADD MZ
	$(function() {
		$("#tut_mz_anlegen").dialog({
			width: 640,
			height: 625,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_tut_mz_anlegen").on("click", function() {
			$("#tut_mz_anlegen").dialog("open");
		});
	});
	
	// MODAL TUTORIAL: CALCULATION MAKE EVENT
	$(function() {
		$("#tut_art_der_berechnung").dialog({
			width: 640,
			height: 625,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_tut_art_der_berechnung").on("click", function() {
			$("#tut_art_der_berechnung").dialog("open");
		});
	});
	
	// MODAL HINT: KARENZZEIT
	$(function() {
		$("#help_karenzzeit").dialog({
			width: 640,
			height: 305,
			autoOpen: false,
			show: {
				effect: "drop",
				duration: 1000
			},
			hide: {
				effect: "drop",
				duration: 1000
			}
		});
	 
		$("#opener_karenzzeit_hilfe").on("click", function() {
			$("#help_karenzzeit").dialog("open");
		});
	});
});