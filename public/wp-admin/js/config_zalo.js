//
jQuery(document).ready(function () {
  //console.log(zalo_app_id);
  replace_url_config_app("YOUR_ZALO_APP_ID", zalo_app_id);

  // các dữ liệu không khuyến khích sửa
  jQuery("#data_zalooa_access_token, #data_zalooa_refresh_token")
    .addClass("graycolor")
    .attr({
      readonly: "readonly",
      title:
        "Dữ liệu này không khuyến khích thay đổi thủ công! Nếu bạn vẫn muốn tiếp tục, hãy bấm đúp chuột để cập nhật dữ liệu này...",
    })
    .click(function () {
      WGR_alert(jQuery(this).attr("title"), "warning");
    })
    .dblclick(function () {
      jQuery(this).removeAttr("readonly");
    });

  // các dữ liệu không cho sửa
  jQuery("#data_zalooa_expires_token").attr({
    disabled: "disabled",
    readonly: "readonly",
  });

  //
  jQuery("#data_zalooa_webhook")
    .attr({
      readonly: "readonly",
      ondblclick: "click2Copy(this);",
    })
    .val(web_link + "zalos/webhook");

  //
  let a = jQuery("#data_zalooa_expires_token").val() || "";
  if (a != "") {
    a *= 1000;
    if (!isNaN(a)) {
      let tzoffset = new Date().getTimezoneOffset() * 60000; // offset in milliseconds
      a = new Date(a - tzoffset).toISOString().split(".")[0].replace("T", " ");
      jQuery("#data_zalooa_expires_token").after(" " + a);
    }
  }

  // thay {BOT_TOKEN} trong href hướng dẫn lấy chat_id
  function replace_zalo_bot_token_href(zalo_bot_token) {
    jQuery(".text-zalo_chat_id a").each(function () {
      let a = jQuery(this);
      let origin = a.attr("data-origin-href");
      if (typeof origin == "undefined") {
        origin = a.attr("href") || "";
        a.attr("data-origin-href", origin);
      }
      // không có {BOT_TOKEN} thì bỏ qua
      if (origin.includes("{BOT_TOKEN}") != true) {
        return;
      }
      if (zalo_bot_token == "") {
        a.attr("href", "#");
      } else {
        a.attr("href", origin.split("{BOT_TOKEN}").join(zalo_bot_token));
      }
    });
  }

  jQuery("#data_zalo_bot_token").on("input change", function () {
    replace_zalo_bot_token_href(jQuery(this).val() || "");
  });
  replace_zalo_bot_token_href(jQuery("#data_zalo_bot_token").val() || "");
});
