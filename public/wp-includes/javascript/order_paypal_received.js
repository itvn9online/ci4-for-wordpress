/**
 * Get Client ID:
 * https://developer.paypal.com/dashboard/applications/sandbox
 *
 * Script Configuration:
 * https://developer.paypal.com/sdk/js/configuration/
 *
 * https://developer.paypal.com/sdk/js/v1/reference/#createorder
 * https://stackoverflow.com/questions/56414640/paypal-checkout-javascript-with-smart-payment-buttons-create-order-problem
 */

function WGR_load_paypal_buttons(input) {
	if (typeof max_i != "number") {
		max_i = 99;
	} else if (max_i < 0) {
		console.log("max_i", max_i);
		return false;
	}

	//
	if (typeof paypal == "undefined") {
		setTimeout(() => {
			WGR_load_paypal_buttons(input, max_i - 1);
		}, 100);
		return false;
	}

	//
	if (typeof input.currency_code == "undefined") {
		input.currency_code = "USD";
	}
	console.log("input", input);

	//
	paypal
		.Buttons({
			style: {
				// disableMaxWidth: true,
				label: "paypal",
			},
			// https://developer.paypal.com/docs/api/orders/v2/#orders_create
			createOrder(data, actions) {
				console.log(data);

				//
				let amount_breakdown = {
					item_total: {
						currency_code: input.currency_code,
						value: current_order_data.order_subtotal + "",
					},
					shipping: {
						currency_code: input.currency_code,
						value: current_order_data.shipping_fee + "",
					},
					discount: {
						currency_code: input.currency_code,
						value: current_order_data.order_discount + "",
					},
				};

				//
				let purchase_units_items = current_order_data.order_item;

				//
				let purchase_units_description =
					"Order number #" +
					current_order_data.order_id +
					" form " +
					document.domain;

				//
				return actions.order.create({
					purchase_units: [
						{
							reference_id: input.reference_id,
							description: purchase_units_description,
							// custom_id: "",
							// soft_descriptor: "",
							amount: {
								currency_code: input.currency_code,
								value: input.value,
								breakdown: amount_breakdown,
							},
							items: purchase_units_items,
							// shipping: current_order_data.shipping,
						},
					],
				});
			},
			onApprove(data, actions) {
				if (1 < 2) {
					console.log("data", data);
					for (let x in data) {
						console.log(x, data[x]);
					}
				}

				//
				return actions.order.capture().then(function (details) {
					if (1 < 2) {
						console.log("details", details);
						console.log(
							"Transaction completed by:",
							details.payer.name.given_name
						);
						for (let x in details) {
							console.log(x, details[x]);
						}
					}

					//
					let order_complete = false;
					if (typeof details.purchase_units == "undefined") {
						order_complete = "details.purchase_units undefined";
					} else if (details.purchase_units.length < 1) {
						order_complete = "details.purchase_units length";
					} else if (
						typeof details.purchase_units[0].reference_id == "undefined"
					) {
						order_complete = "details.purchase_units reference_id undefined";
					} else if (
						details.purchase_units[0].reference_id !=
						current_order_data.reference_id
					) {
						order_complete = "details.purchase_units reference_id mismatch";
					} else if (typeof details.status == "undefined") {
						order_complete = "details.status undefined";
					} else if (details.status != "COMPLETED") {
						order_complete = "details.status NOT COMPLETED";
					} else {
						order_complete = true;
					}
					console.log("order_complete", order_complete);

					//
					if (order_complete !== true) {
						console.log(
							"%c" + "order NOT complete:" + order_complete,
							"color: red"
						);
						return false;
					}

					// Call your server to save the transaction
					return jQuery.ajax({
						type: "POST",
						url: "actions/capture_paypal_order",
						dataType: "json",
						//crossDomain: true,
						data: {
							order_id: current_order_data.order_id,
							reference_id: details.purchase_units[0].reference_id,
							approve_data: JSON.stringify(data),
							order_capture: JSON.stringify(details),
						},
						timeout: 33 * 1000,
						error: function (jqXHR, textStatus, errorThrown) {
							jQueryAjaxError(
								jqXHR,
								textStatus,
								errorThrown,
								new Error().stack
							);
						},
						success: function (data) {
							console.log(data);

							//
							if (
								typeof data.result_update != "undefined" &&
								data.result_update * 1 > 0
							) {
								window.location.reload();
							}
						},
					});
				});
			},
			onCancel(data) {
				// Show a cancel page, or return to cart
				console.log("onCancel", data);
			},
			onError(err) {
				// For example, redirect to a specific error page
				// window.location.href = "/your-error-page-here";
				console.log("onError", err);
			},
		})
		.render("#paypal-button-container");
}

//
$(document).ready(function () {
	WGR_load_paypal_buttons({
		reference_id: current_order_data.reference_id,
		value: current_order_data.order_amount,
	});
});
