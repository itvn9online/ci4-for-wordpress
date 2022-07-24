(function () {
    //console.log(arr_trans_label);
    console.log('%c Chạy vòng lặp thay thế text cho label', 'color: green;');

    // 
    for (var x in arr_trans_label) {
        $('#for_vue label[for="data_lang_' + x + '"]').html(arr_trans_label[x]);
        $('#data_lang_' + x).attr({
            placeholder: arr_trans_label[x]
        });
    }
})();
