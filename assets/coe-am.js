(function ($) {
	// Hide the edit options on page load
	$(".edit-metadata-basic, .edit-metadata-advanced").hide();

	// Confirm our deletions
	$(".coe-am-delete").on("click", function (e) {
		e.preventDefault();
		var msg = "";
		if (typeof coe_am_saved_metadata.existing_taxonomies !== "undefined") {
			msg = coe_am_saved_metadata.confirm_delete;
		}
		var submit_delete_warning = $(
			'<div class="cptui-submit-delete-dialog">' + msg + "</div>"
		)
			.appendTo("#poststuff")
			.dialog({
				// classes: {
				// 	"ui-dialog": "ui-corner-all",
				// },
				modal: true,
				autoOpen: true,
				buttons: [
					{
						text: "OK",
						click: function () {
							var form = $(e.target).closest("form");
							$(e.target).unbind("click").click();
						},
					},
					{
						text: "Cancel",
						click: function () {
							$(this).dialog("close");
						},
					},
				],
			});
	});
})(jQuery);
