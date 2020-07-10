CKEDITOR.editorConfig = function ( config ) {
    config.toolbar = [
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo'] },
        { name: 'links', items: ['Link', 'Unlink'] },
        { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
        '/',
        { name: 'paragraph', items: ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'] },
        { name: 'styles', items: ['Format', 'Font', 'FontSize', 'TextColor','BGColor'] },
        '/',
        { name: 'tools', items: ['Maximize'] },
        { name: 'document', items: ['Source'] }
    ];
    config.resize_enabled = true;
    config.autoParagraph = true;
    config.extraPlugins = 'image2,uploadimage,colorbutton,font,justify';
    config.allowedContent = true;
    CKEDITOR.dtd.$removeEmpty.i = false;
    CKEDITOR.dtd.$removeEmpty.em = false;

    // Upload Image
    config.filebrowserUploadUrl = '/ckeditor.php';
};
