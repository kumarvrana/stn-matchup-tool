var __popup_clicked_td, __popup_data_item;

var editing = false;
var id;
var working = false;

var xhr;
var last_object, last_text, last_td, last_id;
var check_all = 0;
var checked_id = [];
var working_ajax = false;

// select duplicate row from popup

function selectDuplicate(obj) {

	var row = $(obj).data('row');

	$('.select-duplicate').each(function() {
		$(this).removeClass('select-duplicate');
	});

	$(obj).addClass('select-duplicate');

}

// replace selected duplicate with original

function replaceDuplicate() {

	var selectedRow = $('#pop-results').find('.select-duplicate');
	var disid = $(selectedRow).data('row');
	$(".setdis"+disid+" td").css("display", "");
	$(selectedRow).addClass('replaced-duplicate');
	deleteDuplicates($(selectedRow).data('row'), $(selectedRow).clone());

}

// delete duplicates

function deleteDuplicates(safe_id, selected_row) {

	var url = site_url_with_comp_matchup;

	var table_row = $(__popup_clicked_td).parent();

	if (working) {
		xhr.abort();
	}

	working = true;

	xhr = $.ajax({
	    url : url,
	    method : "get",
	    async : true,
	    data : {
	        task : 'deleteDuplicates',
	        safe : safe_id,
	        item : __popup_data_item
	    },
	    success : function(o) {

		    working = false;
		    popup("hide");
			$(table_row).after(selected_row);
		    $(table_row).remove();

		    if (o == 0) {
			    alert("There is an error while updating the duplicates.");
		    }
	    }
	});

}

// all popup events declared below
function popup(e, c) {

	switch (e) {

	case 'show':
		$("#popup").show();
		$("#bg-cover").show();
	break;

	case 'hide':
		$("#popup").hide();
		$("#bg-cover").hide();
	break;

	case 'empty':
		$("#pop-results").html('');
	break;

	case 'load':
		$("#pop-results").html(c);
		popup("show");
	break;

	}

}

$(function() {
	// check all
	$(document).on("click", "#check", function() {

		console.log($(this).is(":checked"));

		if ($(this).is(":checked")) {
			$(".check").each(function() {
				$(this).prop("checked", true);
				$(this).parent().parent().addClass('highlight-row');
			});
		} else {
			$(".check").each(function() {
				$(this).prop("checked", false);
				$(this).parent().parent().removeClass('highlight-row');
			});
		}
 
	});

	// show select box to change option

	$(document)
	        .on(
	                "click",
	                ".change-status",
	                function() {
		                check_all = $("#check").is(":checked") ? 1 : 0;
		                if (working_ajax) {
			                alert("Please wait...");
			                return;
		                }
					checked_id = [];
					$('#view_data_form').find('input[type="checkbox"]:checked').each(function (){
						var ids = $(this).parent().data('id');
						checked_id.push(ids);
		
					});   
					
		                if (editing) {
			                $(last_object).html(
			                        "<span class='change-status' data-id='"
			                                + last_id + "'>" + last_text
			                                + "</span>");
			                editing = false;
		                }

		                last_object = $(this).parent();
		                var id = $(this).data("id");
		                last_id = id;
		                var old_status = $(this).text();
		                old_status = old_status.trim()
		                last_text = old_status;

		                var Y_selected = "";
		                var N_selected = "";
		                var AI_selected = "";

		                if (old_status === "Y") {
			                Y_selected = "SELECTED";
		                }
		                if (old_status === "N") {
			                N_selected = "SELECTED";
		                }
		                if (old_status === "AI") {
			                AI_selected = "SELECTED";
		                }

		                // alert(N_selected);
		                options = '<div class="matchup-import-editor"><div class="matchup-impe-container">'
		                options += "<select data-id='" + id
		                        + "' id='changeStatus'>";
		                options += '<option value="Y" ' + Y_selected
		                        + ' >Yes</option>';
		                options += '<option value="N" ' + N_selected
		                        + ' >No</option>';
		                options += '<option value="AI" ' + AI_selected
		                        + '>AI</option>';
		                options += '</select>';
		                options += '</div><div class="save-cancel-status">';
		                options += '<button class="btn orange" id="matchup-save-import-status">Save</button><button class="btn cancel" id="matchup-cancel-import-status">Cancel</button>';
		                options += '</div></div>'

		                $(this).parent().html(options);
		                editing = true;
		                /*
						 * var lastedit = $(this).html(options); editing =
						 * false;
						 */

		                $('.import').each(function() {

			                $(this).addClass('highlight-column');

		                });

	                });

	// change status

	$(document).on(
	        "click",
	        "#matchup-save-import-status",
	        function() {

		        var changeStatus = $("#changeStatus");

		        var val = changeStatus.val();
		        var id = changeStatus.data("id");
				//var id = (checked_id != '') ? checked_id : iid;
		        working_ajax = true;

		        var url = site_url_with_comp_matchup;

		        $.ajax({
		            url : url,
		            method : "get",
		            async : true,
		            data : {
		                task : 'changeImportStatus',
		                'id' : id,
		                'status' : val,
		                'check' : check_all,
						'multi_ids' : checked_id
		            },
		            success : function(status) {
			            last_text = val;
			            // console.log(status);
			            if (check_all == 1) {
							$('#import_status').each(function() {
								$(".change-status").html("<span class='change-status' data-id='"+ last_id+ "'>"+ last_text+ "</span>");
					            $(last_object).html("<span class='change-status' data-id='"+ last_id+ "'>"+ last_text+ "</span>");
					            $(".change-status").addClass('highlight-column');
				           });
			            } else {
							if(status == 2){
								$(".check").each(function(){
								
								if ($(this).is(":checked")) {
									var ctd = $(this).parent();
									$(ctd).parent().find('.change-status').html("<span class='change-status' data-id='"+ last_id+ "'>"+ last_text+ "</span>");
									$(last_object).html("<span class='change-status' data-id='"+ last_id+ "'>"+ last_text+ "</span>");
									$(".change-status").addClass('highlight-column');
								}

							});

							}else{
								$(last_object).html("<span class='change-status' data-id='"+ last_id + "'>" + last_text+ "</span>");
								$(last_object).addClass('highlight-column');
								$(last_object).parent().find(".check").prop("checked", true);
							}
						}
			            // $(last_object).parent().parent().addClass('highlight-row');
			            working_ajax = false;
			         }
		        });

		        // $(this).parent().html("4");

	        });

	$(document).on(
	        "click",
	        "#matchup-cancel-import-status",
	        function() {
		        // $(this).remove();
		        $(last_object).html(
		                "<span class='change-status' data-id='" + last_id
		                        + "'>" + last_text + "</span>");
		        $(last_object).addClass('highlight-column');
		        // $(this).parent().html("4");

	        });

	// load duplicates and show popup

	$(document).on("click", ".show-duplicates", function() {

		__popup_clicked_td = $(this).parent();

		var item = $(this).data('item');
		var ID = $(this).data('id');

		__popup_data_item = item;

		if (working) {
			xhr.abort();
		}

		working = true;

		var url = site_url_with_comp_matchup;

		xhr = $.ajax({
		    url : url,
		    method : "get",
		    async : true,
		    data : {
		        task : 'loadDuplicates',
		        posno : item,
		        safe : ID
		    },
		    success : function(o) {

			    working = false;
			    popup("load", o);

		    }
		});

	});

	// filter script

	$(".typeit").on("keyup change", function() {

		var url = site_url_with_comp_matchup;
		// var column = $(this).attr('id');

		if (working) {
			xhr.abort();
		}

		working = true;

		xhr = $.ajax({

		    url : url,
		    method : "get",
		    async : true,
		    data : $("#matchup-filter").serialize(),
		    success : function(results) {
			    $("#table-results").html(results);
			    working = false;
		    }
		});

	});

	// change order by colm
	var global_order = "asc";

	$(".sort-column").on(
	        "click",
	        function() {

		        working = true;
		        var sort_colm = $(this);
		        var id = $(this).attr('id');
		        var order = $(this).data('order');

		        $("#order").val(order);
		        $("#order-by").val(id);

		        var url = site_url_with_comp_matchup;

		        xhr = $.ajax({
		            url : url,
		            method : "get",
		            data : $("#matchup-filter").serialize(), // {task:
		            // 'sortResults',
		            // sort_by: id,
		            // order:
		            // global_order},
		            success : function(o) {

			            /*
						 * if (global_order == "asc") { global_order = "desc"; }
						 * else { global_order = "asc"; }
						 */

			            $("#table-results").html(o);
			            working = false;

			            $(".sort-column").each(
			                    function(e) {
				                    $(this).removeClass('asc-results')
				                            .removeClass('desc-results');
			                    });

			            if (order == 'asc') {
				            order_new = 'desc';
				            $("#" + id).removeClass('desc-results');
				            $("#" + id).addClass('asc-results');
			            } else {
				            order_new = 'asc';
				            $("#" + id).removeClass('asc-results');
				            $("#" + id).addClass('desc-results');
			            }

			            sort_colm.data('order', order_new);
		            }
		        });
	        });

	$(document).on('change', '.check', function() {
		if (this.checked) {
			$(this).parent().parent().addClass('highlight-row');
		} else {
			$(this).parent().parent().removeClass('highlight-row');
		}
	});

});

$(function () {
	"use strict";
	var importBtn = $('img[name=import]').parent();
	importBtn.attr('href', '#');
	importBtn.prop('href', '#');
	
	importBtn.on('click', function (e){
		e.preventDefault();
		STN.lib.ModalMask.show();
		$('#loading').removeClass('stn-hide');
		$.getJSON(
			STN.live_site + '/administrator/components/com_stn_matchup/ajax/import.php',
			function (data) {
				$('#loading').addClass('stn-hide');
				$('#import-results .importcount').text(data.processedSkus.length);
				$('#import-results .existcount').text(data.alreadyImported.length);
				$('#import-results .unimportedcount').text(data.intended - data.importedCount);
				if (data.errors.length > 0) {
					$('#import-errors').text(data.errors.join('<br>'));
				} else {
					$('#import-errors').text('No errors reported.');
				}
				$('#import-results').removeClass('stn-hide');
			}
		);
	})
});




