const singleLabel = document.getElementById("label_singular");
const pluralLabel = document.getElementById("label_plural");
const taxName = document.getElementById("tax_name");
const queryVar = document.getElementById("query_var");
const restBase = document.getElementById("rest_base");
const rewriteSlug = document.getElementById("rewrite_slug");
const selectTax = document.getElementById("select_tax");
const btnEditTax = document.getElementById("coe_am_edit");
const btnDelete = document.getElementById("coe_am_delete");
const settings = document.querySelectorAll(".metadata-settings");

(function ($) {
	$(document).ready(function () {
		console.log("Ready!");
	});

	// $("#coe_am_trigger_modal").on("click tap", function (e) {
	// 	console.log("delete click");
	// 	e.preventDefault();
	// 	let heading = "",
	// 		msg = "";

	// 	if (typeof coe_am_scripts.existing_taxonomies !== "undefined") {
	// 		heading = coe_am_scripts.confirm_delete.heading;
	// 		msg = coe_am_scripts.confirm_delete.msg;
	// 		const modal = $("#coe_am_modal");

	// 		modal.find("h5").text(heading);
	// 		modal.find(".modal-body").text(msg);
	// 		modal.modal(
	// 			{
	// 				backdrop: "static",
	// 				keyboard: false,
	// 			},
	// 			"show"
	// 		);

	// 		modal
	// 			.find(".modal-submit")
	// 			.unbind("click")
	// 			.click(function () {
	// 				console.log("modal submit clicked");
	// 			});
	// 	}
	// });
})(jQuery);

if (singleLabel) {
	singleLabel.addEventListener("change", function () {
		let val = this.value
			.toLowerCase()
			.replace(/['"]+/g, "")
			.replace(/\s/g, "_");
		console.log(val);

		if (1 > taxName.value.length) {
			taxName.value = val;
			console.log("tax_name set to " + val);
		}
	});
}

if (pluralLabel) {
	pluralLabel.addEventListener("change", function () {
		// Change the value of the `plural_name` field to lowercase and replace spaces with underscores.
		let val = this.value
			.toLowerCase()
			.replace(/['"]+/g, "")
			.replace(/\s/g, "_");
		console.log(val);

		// Set `rest_base` value
		if (1 > restBase.value.length) {
			restBase.value = val;
			console.log("rest_base set to " + val);
		}

		// Set `rewrite_slug` value
		if (1 > rewriteSlug.value.length) {
			rewriteSlug.value = val;
			console.log("rewrite_slug set to " + val);
		}
	});
}
