(function () {
    if (typeof set_login == 'undefined' || set_login == '') {
        return false;
    }
    $('#loginform input[name="username"]').val(set_login);
    $('#loginform input[name="password"]').focus();
})();