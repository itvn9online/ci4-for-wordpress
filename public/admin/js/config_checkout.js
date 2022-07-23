$(document).ready(function () {
    /*
     * nạp danh sách ngân hàng
     * Danh sách ngân hàng được tải định kỳ tại đây: https://api.vietqr.io/v2/banks
     * https://www.vietqr.io/danh-sach-api/api-danh-sach-ma-ngan-hang
     */
    jQuery.ajax({
        type: 'GET',
        url: 'libraries/banks-vietqr.json',
        dataType: 'json',
        //crossDomain: true,
        timeout: 33 * 1000,
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            if (typeof jqXHR.responseText != 'undefined') {
                console.log(jqXHR.responseText);
            }
            console.log(errorThrown);
            console.log(textStatus);
            if (textStatus === 'timeout') {
                //
            }
        },
        success: function (data) {
            console.log(data);

            //
            if (typeof data.data != 'undefined') {
                var str = '';
                var a = data.data;

                for (var i = 0; i < a.length; i++) {
                    str += '<option value="' + a[i].bin + '" data-logo="' + a[i].logo + '" data-swift_code="' + a[i].swift_code + '" data-name="' + a[i].name + '" data-short_name="' + a[i].short_name + '" data-code="' + a[i].code + '">' + a[i].shortName + ' (' + a[i].code + ') - ' + a[i].name + '</option>';
                }
                $('#data_bank_bin_code').append(str);
                $('#data_bank_bin_code').change(function () {
                    $('#data_bank_logo').val($('option:selected', this).attr('data-logo') || '').trigger('blur');
                    $('#data_bank_swift_code').val($('option:selected', this).attr('data-swift_code') || '').trigger('blur');
                    $('#data_bank_name').val($('option:selected', this).attr('data-name') || '').trigger('blur');
                    $('#data_bank_short_name').val($('option:selected', this).attr('data-short_name') || '').trigger('blur');
                    $('#data_bank_code').val($('option:selected', this).attr('data-code') || '').trigger('blur');
                });

                //
                var select_bank = $('#data_bank_bin_code').attr('data-select') || '';
                if (select_bank != '') {
                    $('#data_bank_bin_code').val(select_bank).trigger('change');
                    //WGR_set_prop_for_select('#data_bank_bin_code');
                }

                //
                if (!$('#data_bank_bin_code').hasClass('has-select2')) {
                    $('#data_bank_bin_code').select2();
                    $('#data_bank_bin_code').addClass('has-select2');
                }
            }
        }
    });
});
