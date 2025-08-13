/**
 * Chức năng tạo thông báo đẩy cho admin.
 * Yêu cầu quyền gửi thông báo lên trình duyệt.
 */

function fetch_admin_notifications() {
	// gọi tới ajax để lấy thông báo từ server
	$.ajax({
		url: "sadmin/acjaxs/fetch_admin_notifications",
		method: "POST",
		data: {
			action: "fetch_admin_notifications",
		},
		dataType: "json",
		// Thiết lập timeout để tránh treo
		timeout: 33000, // 33 giây
		// Xử lý phản hồi từ server
		success: function (response) {
			if (response && response.notifications) {
				response.notifications.forEach(function (notification) {
					// Hiển thị thông báo từ server
					new Notification(notification.title, {
						body: notification.content,
						icon: "https://" + window.location.hostname + "/favicon.png",
						// chỉ định URL khi nhấp vào thông báo
						data: {
							url: notification.url || "https://" + window.location.hostname,
						},
					});
				});

				// hẹn giờ lấy thông báo mới sau 30 giây
				setTimeout(fetch_admin_notifications, 30000);
			}
		},
		// Xử lý lỗi nếu không thể lấy thông báo
		error: function () {
			console.error("Không thể lấy thông báo từ server.");
		},
	});
}

jQuery(document).ready(function ($) {
	// Kiểm tra xem trình duyệt có hỗ trợ thông báo không
	if (!("Notification" in window)) {
		console.warn("Trình duyệt của bạn không hỗ trợ thông báo.");
		return;
	}

	// Yêu cầu quyền gửi thông báo
	Notification.requestPermission().then(function (permission) {
		if (permission === "granted") {
			// Nếu đã có quyền, gọi hàm để lấy thông báo
			fetch_admin_notifications();
		} else {
			console.warn("Quyền gửi thông báo bị từ chối.");
		}
	});
});
