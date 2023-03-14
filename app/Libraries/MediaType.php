<?php

namespace App\Libraries;

//
class MediaType extends PostType
{
    // các định dạng ảnh được phép upload -> tách riêng vì ảnh còn có chế độ resize với lại optimize
    const IMAGE_MIME_TYPE = [
        'image/jpeg',
        'image/jpg',
        'image/png',
    ];

    // các định dạng khác
    const ALLOW_MIME_TYPE = [
        //'video/jpeg',
        //'audio/jpg',
        'application/octet-stream',
        //'text/png',
    ];

    //
    public function __construct()
    {
        parent::__construct();
    }
}
