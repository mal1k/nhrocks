$('#drag-and-drop-zone').dmUploader
({
    // url: '/demos/dnd/upload.php',
    // dataType: 'json',
    // allowedTypes: 'image/*',
    // extFilter: 'jpg;png;gif',
    onInit: function(){
        $.danidemo.addLog('#demo-debug', 'default', 'Plugin initialized correctly');
    },
    onBeforeUpload: function(id){
        $.danidemo.addLog('#demo-debug', 'default', 'Starting the upload of #' + id);

        $.danidemo.updateFileStatus(id, 'default', 'Uploading...');
    },
    onNewFile: function(id, file){
        $.danidemo.addFile('#demo-files', id, file);
    },
    onComplete: function(){
        $.danidemo.addLog('#demo-debug', 'default', 'All pending tranfers completed');
    },
    onUploadProgress: function(id, percent){
        var percentStr = percent + '%';

        $.danidemo.updateFileProgress(id, percentStr);
    },
    onUploadSuccess: function(id, data){
        $.danidemo.addLog('#demo-debug', 'success', 'Upload of file #' + id + ' completed');

        $.danidemo.addLog('#demo-debug', 'info', 'Server Response for file #' + id + ': ' + JSON.stringify(data));

        $.danidemo.updateFileStatus(id, 'success', 'Upload Complete');

        $.danidemo.updateFileProgress(id, '100%');
    },
    onUploadError: function(id, message){
        $.danidemo.updateFileStatus(id, 'error', message);

        $.danidemo.addLog('#demo-debug', 'error', 'Failed to Upload file #' + id + ': ' + message);
    },
    onFileTypeError: function(file){
        $.danidemo.addLog('#demo-debug', 'error', 'File \'' + file.name + '\' cannot be added: must be an image');
    },
    onFileSizeError: function(file){
        $.danidemo.addLog('#demo-debug', 'error', 'File \'' + file.name + '\' cannot be added: size excess limit');
    },
    /*onFileExtError: function(file){
      $.danidemo.addLog('#demo-debug', 'error', 'File \'' + file.name + '\' has a Not Allowed Extension');
    },*/
    onFallbackMode: function(message){
        $.danidemo.addLog('#demo-debug', 'info', 'Browser not supported(do something else here!): ' + message);
    }
});