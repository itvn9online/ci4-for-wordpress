(function () {
    //console.log(arr_trans_label);
    console.log('%c Chạy vòng lặp thay thế text cho label', 'color: green;');

    // 
    for (var x in arr_trans_label) {
        $('#for_vue label[for="data_' + x + '"]').html(arr_trans_label[x]).addClass('bold');
        $('#data_' + x).attr({
            placeholder: arr_trans_label[x]
        });
    }
})();
