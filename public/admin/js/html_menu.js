function action_before_submit_html_menu() {
    // thêm class css cố định cho phần menu
    $('#Resolution_ifr').contents().find('ul').addClass('cf');
    $('#Resolution_ifr').contents().find('ul ul').addClass('sub-menu');

    //
    return action_before_submit_post();
}
