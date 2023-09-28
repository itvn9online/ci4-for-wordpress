function BrowseServer(startupPath, functionData) {
    //var config = {};
    //config.width = 500;
    var finder = new CKFinder();
    finder.basePath = '../';
    finder.startupPath = startupPath;
    finder.selectActionFunction = SetFileField;
    finder.selectActionData = functionData;
    finder.selectThumbnailActionFunction = ShowThumbnails;

    finder.popup();
}

function SetFileField(fileUrl, data) {
    document.getElementById(data["selectActionData"]).value = fileUrl;
}

function ShowThumbnails(fileUrl, data) {
    // this = CKFinderAPI
    var sFileName = this.getSelectedFile().name;
    document.getElementById('thumbnails').innerHTML +=
        '<div class="thumb">'
        + '<img src="' + fileUrl + '" />'
        + '<div class="caption">'
        + '<a href="' + data["fileUrl"] + '" target="_blank">' + sFileName + '</a> (' + data["fileSize"] + 'KB)'
        + '</div>'
        + '</div>';

    document.getElementById('preview').style.display = "";
    return false;
}